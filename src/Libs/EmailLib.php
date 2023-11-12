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
    public static function sendEmailPHPMailer(string $subject, string $body, array $address, array $file, array $copy):bool
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
        $phpmailer->Subject = $subject;

        //DESTINATÁRIO
        $phpmailer->setFrom(CONFIG_EMAIL['from'], CONFIG_SITE['name']);
        $phpmailer->addReplyTo(CONFIG_EMAIL['reply'], CONFIG_SITE['name']);

        //EMAIL A SER ENVIADO
        foreach ($address as $item)
            $phpmailer->addAddress($item);     // Add destino

        if (!empty($copy))
            $phpmailer->addBCC($copy, CONFIG_SITE['name']);

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
    public static function sendEmail(string $subject, string $body, array $address, array $copy = []):bool
    {
        $headers = [];
        $headers[] = "MIME-Version: 1.1";
        $headers[] = "Content-type: text/html; charset=UTF-8";
        $headers[] = "From: ".CONFIG_SITE['name']." <".CONFIG_EMAIL['from'].">";
        $headers[] = "Return-Path: ".CONFIG_SITE['name']." <".CONFIG_EMAIL['reply'].">"; // return-path
        $headers[] = "Reply-To: ".CONFIG_SITE['name']." <".CONFIG_EMAIL['reply'].">"; // Endereço (devidamente validado) que o seu usuário informou no contato

        foreach ($copy as $item) {
            $headers[] = "Bcc: ".$item."\r\n";
        }

        foreach ($address as $item)
            $headers[] = "Cc: ".$item."\r\n";

        $subject = $subject." | ".date("d/m/Y H:i:s");

        $status = mail("", $subject, $body, implode("\r\n", $headers));
        if ($status) {
            return true;
        } else {
            return false;
        }

    }

}