<?php

namespace App\Libs\Whatsapp;


use App\Libs\HttpLib;

class ZapiProvider implements WhatsappProviderInterface
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
        $config = CONFIG_KEY_API['zapi'];
        $this->instanceId = $config['instance_id'];
        $this->token = $config['token'];
        $this->clientToken = $config['client_token'];
        $this->phoneNumber = $config['phonenumber'];
        $this->admin_group = $config['admin_group'];
        $this->webhookSecret = $config['webhook_secret'];
        $this->baseUrl = "https://api.z-api.io/instances";
        $this->maximumDelayMessage = 60;
    }

    public function getProviderName(): string
    {
        return 'zapi';
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function validateWebhookToken(string $token): bool
    {
        return $token === $this->webhookSecret;
    }

    /**
     * Monta a URL base da instância
     */
    private function getUrl(string $endpoint): string
    {
        return "{$this->baseUrl}/{$this->instanceId}/token/{$this->token}/{$endpoint}";
    }

    /**
     * Headers padrão para requisições
     */
    private function getHeaders(): array
    {
        return [
            'Content-Type: application/json',
            'Client-Token: ' . $this->clientToken
        ];
    }

    /**
     * Executa requisição POST para a Z-API
     * @return array ['success' => bool, 'messageId' => string|null, 'providerMessageId' => string|null, 'error' => string|null]
     */
    private function request(string $endpoint, array $data): array
    {
        $url = $this->getUrl($endpoint);
        $response = HttpLib::post($url, $data, $this->getHeaders());

        $body = json_decode($response['body'], true);
        $httpCode = $response['info']['http_code'] ?? 0;

        if ($httpCode === 200 && !empty($body['messageId'])) {
            return [
                'success' => true,
                'messageId' => $body['messageId'],
                'providerMessageId' => $body['zaapId'] ?? null,
                'zaapId' => $body['zaapId'] ?? null,
                'error' => null
            ];
        }

        return [
            'success' => false,
            'messageId' => null,
            'providerMessageId' => null,
            'zaapId' => null,
            'error' => $body['message'] ?? $body['error'] ?? "Erro HTTP {$httpCode}"
        ];
    }

    /**
     * Envia mensagem de texto
     * @param string $phone Número com DDI (ex: 5511999999999)
     * @param string $message Texto da mensagem
     * @param int $delayMessage Delay em segundos (1-$this->maximumDelayMessage) antes do envio
     */
    public function sendText(string $phone, string $message, int $delayMessage = 3): array
    {

        $data = [
            'phone' => $phone,
            'message' => $message
        ];

        if ($delayMessage > 0) {
            $data['delayMessage'] = rand(min($delayMessage, $this->maximumDelayMessage), $this->maximumDelayMessage);
        }

        return $this->request('send-text', $data);
    }

    /**
     * Envia mensagem com imagem
     * @param string $phone Número com DDI
     * @param string $imageUrl URL da imagem ou base64
     * @param string $message Mensagem do texto
     * @param string $linkUrl Endpoint interno de redirecionamento
     * @param int $delayMessage Delay em segundos
     */
    public function sendImage(string $phone, string $imageUrl, string $message, string $linkUrl = '', int $delayMessage = 0): array
    {



        $data = [
            "phone" => $phone,
            "message" => $message,
            "image" => $imageUrl,
            "linkUrl" => $linkUrl,
            "title" => "Via Esporte",
            "linkDescription" => "A mais moderna Plataforma para Inscrições em Eventos Esportivos"
        ];

        if ($delayMessage > 0) {
            $data['delayMessage'] = rand(min($delayMessage, $this->maximumDelayMessage), $this->maximumDelayMessage);
        }

        return $this->request('send-link', $data);
    }


    public function sendButton(string $phone,  string $message, array $buttonActions = []): array
    {

        $data = [
            "phone" => $phone,
            "message" => $message,
            'buttonActions' => $buttonActions
        ];


        return $this->request('send-button-actions', $data);
    }


    /**
     * Envia múltiplas mensagens com delay entre cada uma.
     * Executa de forma sequencial para não sobrecarregar a API nem o servidor.
     *
     * @param array $messages Array de mensagens, cada uma com:
     *   - 'phone' => string
     *   - 'message' => string
     *   - 'type' => string (text|image|document)
     *   - 'image' => string (se type=image)
     *   - 'caption' => string (opcional)
     *   - 'linkUrl' => string (se type=image, opcional)
     * @param int $delayBetween Delay em segundos entre cada envio (padrão 2s)
     * @return array ['total' => int, 'enviados' => int, 'falhas' => int, 'resultados' => array]
     */
    public function sendBatch(array $messages, int $delayBetween = 2): array
    {
        $resultados = [];
        $enviados = 0;
        $falhas = 0;

        foreach ($messages as $index => $msg) {
            $result = match ($msg['type'] ?? 'text') {
                'image' => $this->sendImage(
                    $msg['phone'],
                    $msg['image'],
                    $msg['caption'] ?? '',
                    $msg['linkUrl'] ?? ''
                ),
                default => $this->sendText(
                    $msg['phone'],
                    $msg['message']
                ),
            };

            $result['phone'] = $msg['phone'];
            $resultados[] = $result;

            if ($result['success']) {
                $enviados++;
            } else {
                $falhas++;
            }

            // Delay entre mensagens (exceto na última)
            if ($index < count($messages) - 1 && $delayBetween > 0) {
                sleep($delayBetween);
            }
        }

        return [
            'total' => count($messages),
            'enviados' => $enviados,
            'falhas' => $falhas,
            'resultados' => $resultados
        ];
    }
}
