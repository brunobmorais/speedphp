<?php
namespace App\Libs;

use App\Models\UsuarioModel;

/**
 * Created by PhpStorm.
 * User: Bruno Morais
 * Email: brunomoraisti@gmail.com
 * Date: 13/06/2023
 * Time: 15:17
 */
class SessionLib
{
    protected const NOME_SESSAO = "SESSION-APP";
    private static bool $started = false;

    public function __construct()
    {
    }

    public static function start(): void
    {
        if (self::$started) {
            return; // Já foi iniciada nesta requisição
        }

        if (session_status() === PHP_SESSION_NONE) {
            // ✅ CONFIGURAÇÕES DE SEGURANÇA
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_samesite', 'Lax');

            // Se usar HTTPS, descomente:
            // ini_set('session.cookie_secure', '1');

            // Tempo de vida: 30 minutos
            ini_set('session.gc_maxlifetime', '1800');
            ini_set('session.cookie_lifetime', '1800');

            session_name(self::NOME_SESSAO);
            session_start();

            self::$started = true;

            // ✅ Regenerar ID periodicamente (previne fixação)
            if (!isset($_SESSION['_session_created'])) {
                $_SESSION['_session_created'] = time();
            } elseif (time() - $_SESSION['_session_created'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['_session_created'] = time();
            }
        }
    }

    // ✅ REMOVER - Não fechar sessão durante a requisição
    // private static function end(): void
    // {
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //         ob_end_flush();
    //     }
    // }

    public static function apagaSessao(): void
    {
        self::start();

        // ✅ Limpar cookie de sessão também
        if (isset($_COOKIE[self::NOME_SESSAO])) {
            setcookie(
                self::NOME_SESSAO,
                '',
                time() - 3600,
                '/',
                '',
                false, // secure
                true   // httponly
            );
        }

        session_unset();
        session_destroy();

        self::$started = false;
    }

    public static function setValue(string $name, $value): void
    {
        self::start();
        $_SESSION[$name] = $value;
        // ✅ NÃO FECHAR - deixar aberta
    }

    public static function getValue($name)
    {
        self::start();
        return $_SESSION[$name] ?? null;
        // ✅ NÃO FECHAR - deixar aberta
    }

    public static function apagaCampo($nomeCampo): void
    {
        self::start();
        unset($_SESSION[$nomeCampo]);
        // ✅ NÃO FECHAR - deixar aberta
    }

    public static function setDataSession(array $dados): void
    {
        self::apagaSessao();
        self::start(); // ✅ Reiniciar após destruir

        foreach ($dados as $key => $value) {
            $_SESSION[$key] = $value; // ✅ Acesso direto já que já iniciamos
        }
    }

    public static function getDataSession(array $keys = []): array
    {
        self::start();

        // Se $keys for vazio, busca todos os dados de sessão
        $keys = empty($keys) ? array_keys($_SESSION) : $keys;

        $dados = [];
        foreach ($keys as $key) {
            $dados[$key] = $_SESSION[$key] ?? null; // ✅ Acesso direto
        }

        return $dados;
    }

    /**
     * ✅ NOVO: Verificar se sessão existe e é válida
     */
    public static function isValid(): bool
    {
        self::start();
        return isset($_SESSION['_session_created']);
    }

    /**
     * ✅ NOVO: Obter ID da sessão (para debug)
     */
    public static function getId(): string
    {
        self::start();
        return session_id();
    }

    /**
     * ✅ NOVO: Regenerar ID de sessão (após login)
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['_session_created'] = time();
    }
}