importScripts("https://www.gstatic.com/firebasejs/6.4.2/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/6.4.2/firebase-messaging.js");

firebase.initializeApp({
  'messagingSenderId': '1098109170346'
})

const messaging = firebase.messaging();

/*messaging.setBackgroundMessageHandler(function(payload) {
  const notification = JSON.parse(payload.data.notification);

  return self.registration.showNotification(notification.title, notification);
});*/

messaging.setBackgroundMessageHandler(function(payload) {
  const data = JSON.parse(payload.data.notification);

  //console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
  var notificationTitle = data.title;
  var notificationOptions = {
    body: data.body,
    icon: 'https://toaqui.app/assets/img/logonavegador.png',
    click_action: data.click_action
  };

  return self.registration.showNotification(notificationTitle,
      notificationOptions);
});
