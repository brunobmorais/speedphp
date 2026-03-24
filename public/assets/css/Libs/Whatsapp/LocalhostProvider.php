<?php

namespace App\Libs\Whatsapp;

use App\Libs\EmailLib;

/**
 * Provider fake para ambiente de desenvolvimento.
 * Intercepta mensagens WhatsApp e envia para o Mailpit via email.
 */
class LocalhostProvider implements WhatsappProviderInterface
{



    private string $instanceId;
    private string $token;
    private string $clientToken;
    private string $phoneNumber;
    private string $webhookSecret;
    private string $admin_group;
    private string $baseUrl;
    private int $maximumDelayMessage;


    public function __construct()
    {
        $config = CONFIG_KEY_API['localhost_wpp'];
        $this->instanceId = $config['instance_id'];
        $this->token = $config['token'];
        $this->clientToken = $config['client_token'];
        $this->phoneNumber = $config['phonenumber'];
        $this->admin_group = $config['admin_group'];
        $this->webhookSecret = $config['webhook_secret'];
        $this->maximumDelayMessage = 60;
    }

    public function getProviderName(): string
    {
        return 'localhost_wpp';
    }

    public function getPhoneNumber(): string
    {
        return 'LOCALHOST_WPP';
    }

    public function validateWebhookToken(string $token): bool
    {
        return true;
    }

    public function sendText(string $phone, string $message, int $delayMessage = 3): array
    {
        $this->sendToMailpit('TEXT', $phone, $message);
        return $this->fakeSuccess();
    }

    public function sendImage(string $phone, string $imageUrl, string $message, string $linkUrl = '', int $delayMessage = 0): array
    {
        $body = $message;
        if ($imageUrl) $body .= "<br><br><strong>Imagem:</strong> <a href='{$imageUrl}'>{$imageUrl}</a>";
        if ($linkUrl) $body .= "<br><strong>Link:</strong> <a href='{$linkUrl}'>{$linkUrl}</a>";
        $this->sendToMailpit('IMAGE', $phone, $body);
        return $this->fakeSuccess();
    }

    public function sendButton(string $phone, string $message, array $buttonActions = []): array
    {
        $body = $message . "<br><br><strong>Botões:</strong><pre>" . json_encode($buttonActions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        $this->sendToMailpit('BUTTON', $phone, $body);
        return $this->fakeSuccess();
    }

    public function sendBatch(array $messages, int $delayBetween = 2): array
    {
        $resultados = [];
        foreach ($messages as $msg) {
            $type = $msg['type'] ?? 'text';
            $content = $msg['message'] ?? $msg['caption'] ?? '';
            $this->sendToMailpit('BATCH/' . strtoupper($type), $msg['phone'], $content);
            $resultados[] = array_merge($this->fakeSuccess(), ['phone' => $msg['phone']]);
        }

        return [
            'total' => count($messages),
            'enviados' => count($messages),
            'falhas' => 0,
            'resultados' => $resultados
        ];
    }

    private function fakeSuccess(): array
    {
        return [
            'success' => true,
            'messageId' => 'fake-' . uniqid(),
            'providerMessageId' => null,
            'error' => null
        ];
    }

    private function sendToMailpit(string $type, string $phone, string $content): void
    {
        $subject = "[WhatsApp DEV] [{$type}] Para: {$phone}";
        $body = "
            <div style='font-family:sans-serif;max-width:500px;margin:auto;padding:20px;'>
                <h2 style='color:#25D366;'>WhatsApp LOCALHOST</h2>
                <table style='width:100%;border-collapse:collapse;'>
                    <tr><td style='padding:8px;font-weight:bold;'>Tipo:</td><td style='padding:8px;'>{$type}</td></tr>
                    <tr><td style='padding:8px;font-weight:bold;'>Telefone:</td><td style='padding:8px;'>{$phone}</td></tr>
                    <tr><td style='padding:8px;font-weight:bold;'>Horário:</td><td style='padding:8px;'>" . date('d/m/Y H:i:s') . "</td></tr>
                </table>
                <hr style='margin:16px 0;'>
                <div style='padding:12px;background:#f0f0f0;border-radius:8px;'>{$content}</div>
            </div>";

        EmailLib::sendEmailPHPMailer($subject, $body, [CONFIG_EMAIL['from']]);
    }
}
