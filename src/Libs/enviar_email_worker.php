#!/usr/bin/env php
<?php
// enviar_email_worker.php - Script para ser executado em background

// Iniciar log
use PHPMailer\PHPMailer\PHPMailer;

// Iniciar log
$logDir = dirname(__DIR__, 2) . '/logs/';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . 'email_worker.log';

$logMessage = date('Y-m-d H:i:s') . " - Worker de email iniciado\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

// Verificar se foi passado um argumento
if (!isset($argv[1])) {
    $errorMsg = date('Y-m-d H:i:s') . " - Erro: Dados do e-mail não fornecidos\n";
    file_put_contents($logFile, $errorMsg, FILE_APPEND);
    exit("Dados do e-mail não fornecidos");
}

try {
    // Obter e desserializar os dados do e-mail
    $emailData = unserialize(base64_decode($argv[1]));

    // Verificar se os dados estão corretos
    if (!is_array($emailData) || empty($emailData['subject']) || empty($emailData['body']) || empty($emailData['address'])) {
        $errorMsg = date('Y-m-d H:i:s') . " - Erro: Dados de e-mail inválidos\n";
        file_put_contents(dirname(__DIR__, 2) . '/logs/email_worker.log', $errorMsg, FILE_APPEND);
        exit("Dados de e-mail inválidos");
    }

    // Log de processamento
    $logMsg = date('Y-m-d H:i:s') . " - Processando email para: " . implode(", ", $emailData['address']) . "\n";
    file_put_contents(dirname(__DIR__, 2) . '/logs/email_worker.log', $logMsg, FILE_APPEND);

    // Incluir autoloader e arquivos de configuração necessários
    $autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
    $configPath = dirname(__DIR__, 2) . '/config/config.php';

    // Verificar se os arquivos existem
    if (!file_exists($autoloadPath)) {
        throw new Exception("Autoloader não encontrado: $autoloadPath");
    }
    if (!file_exists($configPath)) {
        throw new Exception("Arquivo de configuração não encontrado: $configPath");
    }

    require_once $autoloadPath;
    require_once $configPath;

    // Extrair dados do e-mail
    $subject = $emailData['subject'];
    $body = $emailData['body'];
    $address = $emailData['address'];
    $copy = $emailData['copy'] ?? [];
    $reply = $emailData['reply'] ?? null;
    $file = $emailData['file'] ?? [];


    $phpmailer = new PHPMailer(true); // true permite exceções

    //CONFIGURAÇÕES DE ENVIO
    $phpmailer->isSMTP();
    $phpmailer->SMTPDebug = 0;
    $phpmailer->Host = CONFIG_EMAIL['host'];
    $phpmailer->Port = CONFIG_EMAIL['port'];
    $phpmailer->SMTPSecure = CONFIG_EMAIL['smtpSecure'];
    $phpmailer->SMTPAuth = CONFIG_EMAIL['smtpAuth'];
    $phpmailer->Username = CONFIG_EMAIL['userName'];
    $phpmailer->Password = CONFIG_EMAIL['password'];
    $phpmailer->CharSet = "UTF-8";

    // Aumentar timeout para servidores lentos
    $phpmailer->Timeout = 60;

    //ASSUNTO EMAIL
    $phpmailer->Subject = $subject . " | " . date("d/m/Y H:i:s");

    //DESTINATÁRIO
    $phpmailer->setFrom(CONFIG_EMAIL['from'], CONFIG_SITE['name']);
    $phpmailer->addReplyTo($reply ?? CONFIG_EMAIL['reply'], "");

    //EMAIL A SER ENVIADO
    foreach ($address as $item) {
        $phpmailer->addAddress($item);
    }

    foreach ($copy as $item) {
        $phpmailer->addBCC($item);
    }

    // Tratamento melhorado para anexos
    if (!empty($file)) {
        if (isset($file['tmp_name']) && file_exists($file['tmp_name'])) {
            $phpmailer->AddAttachment($file['tmp_name'], $file['name']);
        } else if (is_array($file)) {
            // Suporte para múltiplos arquivos
            foreach ($file as $attachment) {
                if (isset($attachment['tmp_name']) && file_exists($attachment['tmp_name'])) {
                    $phpmailer->AddAttachment($attachment['tmp_name'], $attachment['name']);
                }
            }
        }
    }

    //CONTEUDO
    $phpmailer->isHTML(true);
    $phpmailer->Body = $body;

    $result = $phpmailer->send();

    if (!$result) {
        $errorMsg = date('Y-m-d H:i:s') . " - Erro ao enviar e-mail: " . $phpmailer->ErrorInfo . "\n";
        file_put_contents(dirname(__DIR__, 2) . '/logs/email_worker.log', $errorMsg, FILE_APPEND);
    } else {
        $successMsg = date('Y-m-d H:i:s') . " - E-mail enviado com sucesso para: " . implode(", ", $address) . "\n";
        // file_put_contents(dirname(__DIR__, 2) . '/logs/email_worker.log', $successMsg, FILE_APPEND);
    }

} catch (Exception $e) {
    $errorMsg = date('Y-m-d H:i:s') . " - Exceção ao enviar e-mail: " . $e->getMessage() . "\n";
    file_put_contents(dirname(__DIR__, 2) . '/logs/email_worker.log', $errorMsg, FILE_APPEND);
}
?>