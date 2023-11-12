<?php
namespace App\Api\Routers;


use App\Api\Controllers\PrevisaoDoTempoApiController;
use App\Api\Controllers\UsuarioApiController;
use App\Api\Lib\ResponseClass;
use App\Api\Lib\RouterClass;

class DefaultRouter
{

    public static function start(RouterClass $router): RouterClass{

        // FAZER LOGIN
        $router->post('/login', [
            function ($request) {
                return new ResponseClass(200, (new UsuarioApiController())->login($request));
            }
        ]);

        // RECUPERAR SENHA
        $router->post('/recuperasenha', [
            function ($request) {
                return new ResponseClass(200, (new UsuarioApiController())->recuperasenha($request));
            }
        ]);


        $router->post('/buscarmunicipio', [
            function ($request) {
                return new ResponseClass(200, (new UsuarioApiController())->buscarmunicipio($request));
            }
        ]);

        $router->post('/buscarestoque', [
            "middlewares" => [
                "origin",
                "bearer-auth"
            ],
            function ($request) {
                return new ResponseClass(200, (new UsuarioApiController())->buscarEstoqueId($request));
            }
        ]);

        return $router;
    }
}