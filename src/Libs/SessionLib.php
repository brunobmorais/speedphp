<?php
namespace App\Libs;

class SessionLib
{
    protected const NOME_SESSAO = "SESSION-APP";
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            // ✅ CONFIGURAR PARÂMETROS DO COOKIE
            session_set_cookie_params([
                'lifetime' => 1800, // 30 minutos
                'path' => '/',
                'domain' => '', // Domínio atual
                'secure' => false, // Mude para true se usar HTTPS
                'httponly' => true,
                'samesite' => 'Lax' // Ou 'None' se cross-origin
            ]);

            session_name(self::NOME_SESSAO);
            session_start();

            self::$started = true;

            if (!isset($_SESSION['_session_created'])) {
                $_SESSION['_session_created'] = time();
            } elseif (time() - $_SESSION['_session_created'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['_session_created'] = time();
            }
        } else {
            self::$started = true;
        }
    }

    public static function apagaSessao(): void
    {
        self::start();

        if (isset($_COOKIE[self::NOME_SESSAO])) {
            $params = session_get_cookie_params();
            setcookie(
                self::NOME_SESSAO,
                '',
                time() - 3600,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
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
    }

    public static function getValue($name)
    {
        self::start();
        return $_SESSION[$name] ?? null;
    }

    public static function apagaCampo($nomeCampo): void
    {
        self::start();
        unset($_SESSION[$nomeCampo]);
    }

    public static function setDataSession(array $dados): void
    {
        self::start();

        // ✅ PRESERVAR CSRF
        $csrf = $_SESSION['CSRF'] ?? null;
        $csrfTime = $_SESSION['CSRF_TIME'] ?? null;

        // Limpar dados antigos (exceto metadados)
        foreach (array_keys($_SESSION) as $key) {
            if (!str_starts_with($key, '_session_')) {
                unset($_SESSION[$key]);
            }
        }

        // Adicionar novos dados
        foreach ($dados as $key => $value) {
            $_SESSION[$key] = $value;
        }

        // ✅ RESTAURAR CSRF
        if ($csrf !== null) {
            $_SESSION['CSRF'] = $csrf;
        }
        if ($csrfTime !== null) {
            $_SESSION['CSRF_TIME'] = $csrfTime;
        }
    }

    public static function getDataSession(array $keys = []): array
    {
        self::start();
        $keys = empty($keys) ? array_keys($_SESSION) : $keys;

        $dados = [];
        foreach ($keys as $key) {
            $dados[$key] = $_SESSION[$key] ?? null;
        }

        return $dados;
    }

    public static function isValid(): bool
    {
        self::start();
        return isset($_SESSION['_session_created']);
    }

    public static function getId(): string
    {
        self::start();
        return session_id();
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['_session_created'] = time();
    }

    public static function debug(): array
    {
        self::start();
        return [
            'session_id' => session_id(),
            'session_name' => session_name(),
            'session_status' => session_status(),
            'started_flag' => self::$started,
            'cookie_params' => session_get_cookie_params(),
            'data' => $_SESSION ?? [],
            'cookie_exists' => isset($_COOKIE[self::NOME_SESSAO])
        ];
    }

    public static function isExpired(): bool
    {
        self::start();

        if (!isset($_SESSION['_session_created'])) {
            return true;
        }

        $tempoDecorrido = time() - $_SESSION['_session_created'];
        return $tempoDecorrido > 1800;
    }

    public static function generateCsrfToken(): string
    {
        self::start();

        if (!isset($_SESSION['CSRF']) || !isset($_SESSION['CSRF_TIME'])) {
            $_SESSION['CSRF'] = bin2hex(random_bytes(32));
            $_SESSION['CSRF_TIME'] = time();
        } elseif (time() - $_SESSION['CSRF_TIME'] > 3600) {
            $_SESSION['CSRF'] = bin2hex(random_bytes(32));
            $_SESSION['CSRF_TIME'] = time();
        }

        return $_SESSION['CSRF'];
    }

    public static function validateCsrfToken(?string $token): array
    {
        self::start();

        if (self::isExpired()) {
            return [
                'valid' => false,
                'error' => 'SESSION_EXPIRED',
                'message' => 'Sessão expirada. Por favor, recarregue a página.'
            ];
        }

        $csrfSession = $_SESSION['CSRF'] ?? null;

        if (empty($csrfSession)) {
            return [
                'valid' => false,
                'error' => 'CSRF_NOT_FOUND',
                'message' => 'Token CSRF não encontrado. Por favor, recarregue a página.'
            ];
        }

        if (empty($token)) {
            return [
                'valid' => false,
                'error' => 'CSRF_MISSING',
                'message' => 'Token CSRF não fornecido.'
            ];
        }

        if (!hash_equals($csrfSession, $token)) {
            return [
                'valid' => false,
                'error' => 'CSRF_INVALID',
                'message' => 'Token CSRF inválido.'
            ];
        }

        return [
            'valid' => true,
            'error' => null,
            'message' => 'Token válido'
        ];
    }
}