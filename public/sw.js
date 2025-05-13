/*
Copyright 2015, 2019, 2020, 2021 Google LLC. All Rights Reserved.
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at
http://www.apache.org/licenses/LICENSE-2.0
Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

const OFFLINE_VERSION = "1.0.11";
const CACHE_NAME = `offline-v${OFFLINE_VERSION}`;
const OFFLINE_URL = "offline.html";

// Instalação do Service Worker
self.addEventListener("install", (event) => {
    event.waitUntil(
        (async () => {
            const cache = await caches.open(CACHE_NAME);
            await cache.add(new Request(OFFLINE_URL, { cache: "reload" }));
        })()
    );
    self.skipWaiting();
});

// Ativação do Service Worker
self.addEventListener("activate", (event) => {
    console.log("✅ Service Worker ativado!");

    event.waitUntil(
        (async () => {
            if ("navigationPreload" in self.registration) {
                await self.registration.navigationPreload.enable();
            }

            const cacheKeys = await caches.keys();
            await Promise.all(
                cacheKeys.map((key) => {
                    if (key !== CACHE_NAME) {
                        console.log(`🗑️ Removendo cache antigo: ${key}`);
                        return caches.delete(key);
                    }
                })
            );
        })()
    );

    self.clients.claim();
});

// Interceptação de requisições
self.addEventListener("fetch", (event) => {
    if (event.request.mode === "navigate") {
        event.respondWith(
            (async () => {
                try {
                    const preloadResponse = await event.preloadResponse;
                    if (preloadResponse) {
                        return preloadResponse;
                    }
                    return await fetch(event.request);
                } catch (error) {
                    console.warn("⚠️ Erro ao buscar recurso. Retornando página offline.", error);
                    const cache = await caches.open(CACHE_NAME);
                    return await cache.match(OFFLINE_URL);
                }
            })()
        );
    }
});

// Push Notification
self.addEventListener("push", (event) => {
    console.log("📨 Push recebido!");

    if (!(self.Notification && self.Notification.permission === "granted")) {
        console.warn("🔕 Notificações não permitidas.");
        return;
    }

    let data = {};
    try {
        data = event.data?.json() || {};
    } catch (err) {
        console.error("❌ Erro ao processar JSON da notificação:", err);
    }

    const notification = {
        title: data.notification?.title || "Nova Notificação",
        body: data.notification?.body || "Você recebeu uma nova mensagem.",
        icon: data.data?.icon || "/assets/img/icon/android-icon-512x512.png",
        badge: data.data?.badge || "/assets/img/icon/android-icon-36x36.png",
        image: data.data?.image || "",
        tag: data.tag || crypto.randomUUID(),
        data: {
            url: data.data?.click_action || self.location.origin + "/",
        },
        requireInteraction: true,
    };

    event.waitUntil(
        self.registration.showNotification(notification.title, notification)
    );
});

// Clique na notificação
self.addEventListener("notificationclick", (event) => {
    console.log("🖱️ Notificação clicada!");

    event.notification.close();

    event.waitUntil(
        (async () => {
            const allClients = await clients.matchAll({
                type: "window",
                includeUncontrolled: true,
            });

            for (const client of allClients) {
                if (client.url === event.notification.data.url && "focus" in client) {
                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(event.notification.data.url);
            }
        })()
    );
});

// Mensagens para o Service Worker
self.addEventListener("message", (event) => {
    console.log("📩 Mensagem recebida no Service Worker:", event.data);
});