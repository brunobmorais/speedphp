<?php

use App\Controllers\ErroController;
use App\Core\AppCore;
use Spatie\Ignition\Ignition;

require dirname(__DIR__, 1) . '/vendor/autoload.php';
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
    if (CONFIG_DISPLAY_ERROR_DETAILS) {
        throw new ErrorException($e->getMessage(), $e->getCode(), 1, $e->getFile() ?? "", $e->getLine(), $e->getPrevious());
    } else {
        (new ErroController())->database();
    }
}