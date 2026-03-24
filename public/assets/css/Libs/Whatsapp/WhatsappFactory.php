<?php

namespace App\Libs\Whatsapp;

use App\Libs\Exception\WhatsappException;

abstract class WhatsappFactory
{
    private static ?WhatsappProviderInterface $instance = null;

    /**
     * Retorna a instância do provedor de WhatsApp configurado.
     * Reutiliza a instância dentro da mesma requisição/processo.
     *
     * @param bool $fresh Forçar nova instância (ignora cache)
     * @return WhatsappProviderInterface
     * @throws WhatsappException Se o provedor configurado não existe
     */
    public static function create(string $provider = CONFIG_KEY_API['whatsapp_provider'], bool $fresh = false): WhatsappProviderInterface
    {
        if (!$fresh && self::$instance !== null) {
            return self::$instance;
        }

        $providerName = CONFIG_KEY_API['whatsapp_provider'];

        self::$instance = match ($providerName) {
            'zapi' => new ZapiProvider(),
            'localhost_wpp' => new LocalhostProvider,
            default => throw new WhatsappException("Provedor WhatsApp '{$providerName}' não configurado", 500),
        };

        return self::$instance;
    }


    public static function sendBatchBackground(array $messages, int $delayBetween = 2): bool
    {
        $data = [
            'messages' => $messages,
            'delay_between' => $delayBetween
        ];

        $serializedData = base64_encode(serialize($data));
        $scriptPath = __DIR__ . '/enviar_whatsapp_worker.php';

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen('start /B php "' . $scriptPath . '" "' . $serializedData . '" > NUL 2>&1', 'r'));
        } else {
            \exec('nohup php "' . $scriptPath . '" "' . $serializedData . '" > /dev/null 2>&1 &');
        }

        return true;
    }
}
