#!/usr/bin/env php
<?php
// enviar_whatsapp_worker.php - Script para envio de WhatsApp em background

use App\Libs\Whatsapp\WhatsappFactory;

// Iniciar log
$logDir = dirname(__DIR__, 3) . '/logs/';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . 'whatsapp_worker.log';

$logMessage = date('Y-m-d H:i:s') . " - Worker de WhatsApp iniciado\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

// Verificar se foi passado um argumento
if (!isset($argv[1])) {
    $errorMsg = date('Y-m-d H:i:s') . " - Erro: Dados das mensagens não fornecidos\n";
    file_put_contents($logFile, $errorMsg, FILE_APPEND);
    exit("Dados das mensagens não fornecidos");
}

try {
    // Obter e desserializar os dados
    $data = unserialize(base64_decode($argv[1]));

    // Verificar se os dados estão corretos
    if (!is_array($data) || empty($data['messages'])) {
        $errorMsg = date('Y-m-d H:i:s') . " - Erro: Dados de mensagens inválidos\n";
        file_put_contents($logFile, $errorMsg, FILE_APPEND);
        exit("Dados de mensagens inválidos");
    }

    $messages = $data['messages'];
    $delayBetween = $data['delay_between'] ?? 2;
    $total = count($messages);

    $logMsg = date('Y-m-d H:i:s') . " - Processando lote de {$total} mensagens (delay: {$delayBetween}s)\n";
    file_put_contents($logFile, $logMsg, FILE_APPEND);

    // Incluir autoloader e configuração
    $autoloadPath = dirname(__DIR__, 3) . '/vendor/autoload.php';
    $configPath = dirname(__DIR__, 3) . '/config/config.php';

    if (!file_exists($autoloadPath)) {
        throw new Exception("Autoloader não encontrado: $autoloadPath");
    }
    if (!file_exists($configPath)) {
        throw new Exception("Arquivo de configuração não encontrado: $configPath");
    }

    require_once $autoloadPath;
    require_once $configPath;

    // Instanciar provedor via factory
    $provider = WhatsappFactory::create();

    $enviados = 0;
    $falhas = 0;

    foreach ($messages as $index => $msg) {
        $phone = $msg['phone'] ?? 'desconhecido';
        $type = $msg['type'] ?? 'text';

        try {
            $result = match ($type) {
                'image' => $provider->sendImage(
                    $msg['phone'],
                    $msg['image'],
                    $msg['caption'] ?? '',
                    $msg['linkUrl'] ?? ''
                ),

                default => $provider->sendText(
                    $msg['phone'],
                    $msg['message']
                ),
            };

            if ($result['success']) {
                $enviados++;
                $logMsg = date('Y-m-d H:i:s') . " - [" . ($index + 1) . "/{$total}] Enviado para {$phone} (messageId: {$result['messageId']})\n";
            } else {
                $falhas++;
                $logMsg = date('Y-m-d H:i:s') . " - [" . ($index + 1) . "/{$total}] Falha para {$phone}: {$result['error']}\n";
            }

            file_put_contents($logFile, $logMsg, FILE_APPEND);

        } catch (Exception $e) {
            $falhas++;
            $logMsg = date('Y-m-d H:i:s') . " - [" . ($index + 1) . "/{$total}] Exceção para {$phone}: {$e->getMessage()}\n";
            file_put_contents($logFile, $logMsg, FILE_APPEND);
        }

        // Delay entre mensagens (exceto na última)
        if ($index < $total - 1 && $delayBetween > 0) {
            sleep($delayBetween);
        }
    }

    // Resumo final
    $resumo = date('Y-m-d H:i:s') . " - Lote finalizado: {$enviados} enviados, {$falhas} falhas de {$total} total\n";
    $resumo .= str_repeat('-', 60) . "\n";
    file_put_contents($logFile, $resumo, FILE_APPEND);

} catch (Exception $e) {
    $errorMsg = date('Y-m-d H:i:s') . " - Exceção no worker: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $errorMsg, FILE_APPEND);
}
?>
