<?php


namespace App\Api;

use App\Api\Controllers\SistemaRouter;
use App\Api\Lib\MiddlewareClass;
use App\Api\Lib\Middlewares\BearerAuthMiddleware;
use App\Api\Lib\Middlewares\MaintenanceMiddleware;
use App\Api\Lib\Middlewares\OriginMiddleware;
use App\Api\Lib\ResponseClass;
use App\Api\Lib\RouterClass;
use App\Api\Routers\DefaultRouter;
use App\Api\Routers\MercadoPagoRouter;
use App\Api\Routers\PessoaRouter;
use App\Api\Routers\TelegramRouter;

class Api
{

    public static function run(){

        // SETA CLASSES DO MIDDLEWARE
        MiddlewareClass::setMap([
            "maintenance" => MaintenanceMiddleware::class,
            "bearer-auth" => BearerAuthMiddleware::class,
            "origin" => OriginMiddleware::class
        ]);

        // SETA MIDDEWARES PADRAO
        MiddlewareClass::setDefault([
            "maintenance"
        ]);

        $router = new RouterClass(CONFIG_URL."/api");

        $router->get('', [
            function () {
                return new ResponseClass(200, [
                    'error' => false,
                    'message' => "API ".CONFIG_SITE['name']
                ]);
            }
        ]);

        // SETA ROTAS
        DefaultRouter::start($router);
        PessoaRouter::start($router);
        SistemaRouter::start($router);
        MercadoPagoRouter::start($router);
        TelegramRouter::start($router);
        //ExternoRouter::start($router);

        $router->run()->sendResponse();

    }

}