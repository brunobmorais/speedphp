<?php
namespace App\Libs;

/**
 * User: Bruno Morais
 * Email: brunomoraisti@gmail.com
 * Date: 13/06/2023
 * Time: 15:17
 *
 * DANGER - ERRO
 * WARNING - ATENÇÃO
 * SUCCESS - EXECUTADO COM SUCESSO
 * INFO - INFORMAR
 *
 */
class AlertLib
{
    protected const NOME_SESSAO= "ALERTLIB";

    public function __construct()
    {
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(self::NOME_SESSAO);
            session_start();
        }
    }

    private function endSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    private function setSessionAlert(string $alert): void
    {
        $this->startSession();
        $_SESSION['ALERTLIB_MSG'] = $alert;
        $this->endSession();
    }

    private function clearSessionAlert(): void
    {
        $this->startSession();
        unset($_SESSION['ALERTLIB_MSG'], $_SESSION['ALERTLIB_SITUACAO']);
        $this->endSession();
    }

    public function showAlert(string $type, string $message, string $redirect): void
    {
        $title = match ($type) {
            'danger' => 'Ops!',
            'warning' => 'Atenção!',
            'success' => 'Legal!',
            'info' => 'Informação!',
            default => 'Notificação'
        };

        $alert = "<script>window.onload = function () {iziToast.{$type}({title: '{$title}', message: '{$message}', position: 'bottomRight'});}</script>";

        $this->setSessionAlert($alert);
        header("Location: $redirect");
        exit;
    }

    public function danger(string $message, string $redirect): void
    {
        $this->showAlert('error', $message, $redirect);
    }

    public function warning(string $message, string $redirect): void
    {
        $this->showAlert('warning', $message, $redirect);
    }

    public function success(string $message, string $redirect): void
    {
        $this->showAlert('success', $message, $redirect);
    }

    public function info(string $message, string $redirect): void
    {
        $this->showAlert('info', $message, $redirect);
    }

    public function checkAlert(): ?string
    {
        $this->startSession();
        $alert = $_SESSION['ALERTLIB_MSG'] ?? null;
        $this->endSession();
        $this->clearSessionAlert();
        return $alert;
    }
}
