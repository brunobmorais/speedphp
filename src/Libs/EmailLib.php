<?php
namespace App\Libs;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class EmailClass
 *
 * Responsável por enviar email de acordo com os parametros
 *
 * @author Bruno Morais <brunomoraisti@gmail.com>
 * @version 1.0
 * @package App\Lib
 * @dataProvider 26/01/2021
 */
class EmailLib
{
    /**
     * FUNÇÃO PARA ENVIAR EMAL PELA BIBLIOTECA PHPMAILER
     *
     * @param $subject
     * @param $body
     * @param $address
     * @param string $nameAddress
     * @param array $file;
     * @param bool $copy;
     * @return bool
     */
    public static function sendEmailPHPMailer(string $subject, string $body, array $address, array $copy = [], string $reply = null, array $file = []):bool
    {
        $phpmailer = new PHPMailer();

        //CONFIGURAÇÕES DE ENVIO
        $phpmailer->isSMTP();
        $phpmailer->SMTPDebug = 0;
        $phpmailer->Host = CONFIG_EMAIL['host'];// Specify main and backup SMTP servers
        $phpmailer->Port = CONFIG_EMAIL['port'];// TCP port to connect to
        $phpmailer->SMTPSecure = CONFIG_EMAIL['smtpSecure'];
        $phpmailer->SMTPAuth = CONFIG_EMAIL['smtpAuth'];
        $phpmailer->Username = CONFIG_EMAIL['userName'];                // SMTP username
        $phpmailer->Password = CONFIG_EMAIL['password'];// SMTP password
        $phpmailer->CharSet = "UTF-8";

        //ASSUNTO EMAIL
        $phpmailer->Subject = $subject." | " . date("d/m/Y H:i:s");
;
        //DESTINATÁRIO
        $phpmailer->setFrom(CONFIG_EMAIL['from'], CONFIG_SITE['name']);
        $phpmailer->addReplyTo($reply??CONFIG_EMAIL['reply'], "");

        //EMAIL A SER ENVIADO
        foreach ($address as $item)
            $phpmailer->addAddress($item);     // Add destino

        foreach ($copy as $item)
            $phpmailer->addBCC($item);

        if (!empty($file)) {
            $phpmailer->AddAttachment($file['tmp_name'], $file['name']);
        }

        //CONTEUDO
        $phpmailer->isHTML(true);
        $phpmailer->Body = $body;

        if (!$phpmailer->send()) {
            //echo "Erro ao enviar email: " . $phpmailer->ErrorInfo;
            //exit;
            return false;
        } else {
            //echo "Email enviado!\n";
            return true;
        }
    }

    /**
     * FUNÇÃO ENVIAR EMAIL
     *
     * @param string $subject
     * @param string $body
     * @param string $address
     * @param bool $copy
     * @return bool
     */
    public static function sendEmail(string $subject, string $body, array $address, array $copy = [], string $reply = null, $file = [] ): bool
    {
        if (empty($address)) {
            return false;
        }

        $to = implode(",", $address);

        $boundary = uniqid();
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: multipart/mixed; boundary={$boundary}";
        $headers[] = "From: " . CONFIG_SITE['name'] . " <" . CONFIG_EMAIL['from'] . ">";
        $headers[] = "Reply-To: " . $reply??CONFIG_EMAIL['reply'];
        $headers[] = "Return-Path: " . $reply??CONFIG_EMAIL['reply'];

        if (!empty($copy)) {
            $headers[] = "Bcc: " . implode(",", $copy);
        }

        $subject .= " | " . date("d/m/Y H:i:s");

        // attachment
        $msg = "";
        $msg .= "--".$boundary."\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $msg .= chunk_split(base64_encode($body));

        if (!empty($file)) {
            foreach ($file as $item) {
                $tmp_name = $item['tmp_name']; // Temp file path
                $name = $item['name']; // Original file name
                $size = $item['size']; // File size
                $type = $item['type']; // File type
                $error = $item['error'];

                $messageFile = "";
                $messageFile .= "--".$boundary."\r\n";
                $attachment = chunk_split(base64_encode(file_get_contents($tmp_name)));
                $messageFile .= "Content-Type: $type; name={$name}\r\n";
                $messageFile .= "Content-Transfer-Encoding: base64\r\n";
                $messageFile .= "Content-Disposition: attachment; filename={$name}\r\n\r\n";
                $messageFile .= $attachment . "\r\n";
                $messageFile .= "--".$boundary."--";
                $msg .= $messageFile;
            }
        }

        // Enviar e-mail (corrigindo para usar a variável $to)
        $status = mail($to, $subject, $msg, implode("\r\n", $headers));

        return $status;
    }


    /**
     * Função para enviar e-mail em background usando PHPMailer
     *
     * @param array $emailData Dados do e-mail (destinatário, assunto, corpo, etc)
     * @return bool Retorna verdadeiro se o processo de background foi iniciado
     */
    public static function sendEmailPHPMailerBackground(string $subject, string $body, array $address, array $copy = [], string $reply = null, array $file = [],  ):bool
    {
        // Criar array com os dados do e-mail
        $emailData = [
            'subject' => $subject,
            'body' => $body,
            'address' => $address,
            'copy' => $copy,
            'reply' => $reply,
            'file' => $file
        ];

        // Serializar os dados do e-mail para passar para o processo em background
        $serializedData = base64_encode(serialize($emailData));

        // Caminho para o script worker
        $scriptPath = dirname(__DIR__, 2) . '/src/Libs/enviar_email_worker.php';

        // Verificar sistema operacional para usar o comando correto
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows - usar start /B para executar em background
            pclose(popen('start /B php "' . $scriptPath . '" "' . $serializedData . '" > NUL 2>&1', 'r'));
        } elseif (strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN') {
            // macOS - usar metodo alternativo para processos em background
            exec('nohup php "' . $scriptPath . '" "' . $serializedData . '" > /dev/null 2>&1 &');
        } else {
            // Linux/Unix - usar nohup para executar em background
            exec('nohup php "' . $scriptPath . '" "' . $serializedData . '" > /dev/null 2>&1 &');
        }

        return true;
    }
}