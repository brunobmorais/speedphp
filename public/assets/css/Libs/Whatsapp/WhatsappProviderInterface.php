<?php

namespace App\Libs\Whatsapp;

/**
 * Contrato para provedores de WhatsApp.
 * Qualquer provedor (Z-API, API OFICIAL) deve implementar esta interface.
 *
 * Retorno padrão dos métodos de envio:
 * ['success' => bool, 'messageId' => ?string, 'providerMessageId' => ?string, 'error' => ?string]
 */
interface WhatsappProviderInterface
{




    public function __construct();

    /**
     * Envia mensagem de texto.
     *
     * @param string $phone Número com DDI (ex: 5511999999999)
     * @param string $message Texto da mensagem
     * @param int $delayMessage Delay em segundos antes do envio
     * @return array{success: bool, messageId: ?string, providerMessageId: ?string, error: ?string}
     */
    public function sendText(string $phone, string $message, int $delayMessage = 3): array;

    /**
     * Envia mensagem com imagem.
     *
     * @param string $phone Número com DDI
     * @param string $imageUrl URL da imagem ou base64
     * @param string $message Legenda/texto da mensagem
     * @param string $linkUrl URL de redirecionamento
     * @param int $delayMessage Delay em segundos
     * @return array{success: bool, messageId: ?string, providerMessageId: ?string, error: ?string}
     */
    public function sendImage(string $phone, string $imageUrl, string $message, string $linkUrl = '', int $delayMessage = 0): array;

    public function sendButton(string $phone,  string $message, array $buttonActions = []): array;

    /**
     * Envia múltiplas mensagens com delay entre elas (bloqueante).
     *
     * @param array $messages Array de mensagens com keys: phone, message, type, image, caption, linkUrl
     * @param int $delayBetween Delay em segundos entre cada envio
     * @return array{total: int, enviados: int, falhas: int, resultados: array}
     */
    public function sendBatch(array $messages, int $delayBetween = 2): array;

    /**
     * Retorna o nome identificador do provedor (ex: 'zapi', 'twilio', 'evolution').
     */
    public function getProviderName(): string;

    /**
     * Retorna o número de telefone configurado para este provedor.
     */
    public function getPhoneNumber(): string;

    /**
     * Valida o token/secret de um webhook recebido.
     *
     * @param string $token Token recebido no webhook
     * @return bool true se válido
     */
    public function validateWebhookToken(string $token): bool;
}
