<?php

namespace App\Api\Routers;


use App\Api\Controllers\MercadoPagoApiController;
use App\Api\Lib\ResponseClass;
use App\Api\Lib\RouterClass;

class MercadoPagoRouter
{

    public static function start(RouterClass $router): RouterClass
    {
        $router->setGroup('/mercadopago');

        $router->post('/checkout', [
            "middlewares" => [
                "origin",
                "bearer-auth"
            ],
            function ($request) {
                return new ResponseClass(200, (new MercadoPagoApiController())->checkout($request));
            }
        ]);

        $router->post('/estornarpagamento', [
            "middlewares" => [
                "origin",
                "bearer-auth"
            ],
            function ($request) {
                return new ResponseClass(200, (new MercadoPagoApiController())->estornarPagamento($request));
            }
        ]);

        $router->post('/cancelarpagamento', [
            "middlewares" => [
                "origin",
                "bearer-auth"
            ],
            function ($request) {
                return new ResponseClass(200, (new MercadoPagoApiController())->cancelarPagamento($request));
            }
        ]);

        $router->post('/consultarpagamento', [
            "middlewares" => [
                "origin",
                "bearer-auth"
            ],
            function ($request) {
                return new ResponseClass(200, (new MercadoPagoApiController())->consultarPagamento($request));
            }
        ]);

        $router->get('/cancelarinscricoesvencidas', [
            function ($request) {
                return new ResponseClass(200, (new MercadoPagoApiController())->cancelarInscricoesVencidas($request));
            }
        ]);

        $router->post('/webhook', [
            function ($request) {
                return new ResponseClass(200, (new MercadoPagoApiController())->webhook($request));
            }
        ]);

        return $router;
    }
}
