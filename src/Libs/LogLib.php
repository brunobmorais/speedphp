<?php

namespace App\Libs;

class LogLib
{
    private static string $logDir = '';

    private static function getLogDir(): string
    {
        if (!self::$logDir) {
            self::$logDir = dirname(__DIR__, 2) . '/logs/';
        }
        return self::$logDir;
    }

    public static function error(string $message, ?\Throwable $e = null, string $channel = 'app'): void
    {
        $context = [];
        if ($e) {
            $context['exception'] = get_class($e);
            $context['file']      = $e->getFile();
            $context['line']      = $e->getLine();
            $context['trace']     = substr($e->getTraceAsString(), 0, 800);
        }
        self::escrever('ERROR', $message, $context, $channel);
    }

    public static function warning(string $message, array $context = [], string $channel = 'app'): void
    {
        self::escrever('WARNING', $message, $context, $channel);
    }

    public static function info(string $message, array $context = [], string $channel = 'app'): void
    {
        self::escrever('INFO', $message, $context, $channel);
    }

    private static function escrever(string $nivel, string $message, array $context, string $channel): void
    {
        try {
            $dir  = self::getLogDir();
            $file = $dir . $channel . '_' . date('Y-m-d') . '.log';

            $user = '';
            if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['CODPESSOA'])) {
                $user = ' | User: ' . $_SESSION['CODPESSOA'];
            }

            $method  = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
            $url     = $_SERVER['REQUEST_URI'] ?? '';
            $ip      = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
            $agent   = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $ctx     = empty($context) ? '' : ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $entrada = '[' . date('Y-m-d H:i:s') . '] [' . $nivel . '] ' . $message . $ctx . ' | ' . $method . ' ' . $url . ' | IP: ' . $ip . ' | UA: ' . $agent . $user . PHP_EOL;

            file_put_contents($file, $entrada, FILE_APPEND | LOCK_EX);
            self::rotacionar($channel);
        } catch (\Throwable) {
            // silencioso — logging nunca deve quebrar a aplicação
        }
    }

    private static function rotacionar(string $channel): void
    {
        // Roda no máximo uma vez por hora por canal (flag de arquivo)
        $flagFile = self::getLogDir() . '.rotation_' . $channel;
        if (file_exists($flagFile) && (time() - filemtime($flagFile)) < 3600) {
            return;
        }
        touch($flagFile);

        $limite = strtotime('-3 days');
        foreach (glob(self::getLogDir() . $channel . '_*.log') as $arquivo) {
            if (filemtime($arquivo) < $limite) {
                @unlink($arquivo);
            }
        }
    }
}
