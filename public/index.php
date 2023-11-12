<?php
require dirname(__DIR__,1) . '/vendor/autoload.php';

use App\Controllers\ErroController;
use App\Core\AppCore;

if (CONFIG_MAINTENANCE){
    (new ErroController())->manutencao();
    die();
}

if (CONFIG_DISPLAY_ERROR_DETAILS){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

$app = new AppCore();






