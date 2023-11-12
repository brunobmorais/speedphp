<?php
namespace App\Api\Routers;


use App\Api\Controllers\FazendaoDataBaseApiController;
use App\Api\Controllers\PrevisaoDoTempoApiController;
use App\Api\Lib\ResponseClass;
use App\Api\Lib\RouterClass;

class ExternoRouter
{

    public static function start(RouterClass $router): RouterClass{


        return $router;
    }
}