<?php

use App\Controllers\ErroController;
use App\Core\AppCore;
use App\Libs\EmailLib;
use App\Libs\LogLib;
use App\Libs\TemplateEmailLib;
use Spatie\Ignition\Ignition;

require dirname(__DIR__, 1) . '/vendor/autoload.php';

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!(error_reporting() & $errno)) return false;
    LogLib::warning("PHP Error [$errno]: $errstr", ['file' => $errfile, 'line' => $errline]);
    return true;
});

register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        LogLib::error("PHP Fatal Error: {$error['message']}", null, 'app');
    }
});

try {
    if (CONFIG_MAINTENANCE) {
        (new ErroController())->manutencao();
        die();
    }

    if (CONFIG_DISPLAY_ERROR_DETAILS) {
        Ignition::make()
            ->setEditor("phpstorm")
            ->shouldDisplayException(CONFIG_DISPLAY_ERROR_DETAILS)
            ->register();

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    $app = new AppCore();
} catch (Exception|Error|PDOException $e) {
    LogLib::error($e->getMessage(), $e);
    if (CONFIG_DISPLAY_ERROR_DETAILS) {
        throw new ErrorException($e->getMessage(), $e->getCode(), 1, $e->getFile() ?? "", $e->getLine(), $e->getPrevious());
    } else {
        (new ErroController())->database();
    }
}