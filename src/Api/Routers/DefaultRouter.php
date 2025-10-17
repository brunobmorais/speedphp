<?php

namespace App\Api\Routers;


use App\Api\Lib\ResponseClass;
use App\Api\Lib\RouterClass;
use App\Api\Lib\UsuarioApiController;

class DefaultRouter
{

    public static function start(RouterClass $router): RouterClass
    {

        // FAZER LOGIN
        $router->post('/usuario/login', [
            function ($request) {
                return new ResponseClass(200, (new UsuarioApiController())->login($request));
            }
        ]);

        // FAZER LOGIN
        $router->post('/usuario/cadastrar', [
            function ($request) {
                return new ResponseClass(200, (new UsuarioApiController())->cadastrar($request));
            }
        ]);

        // RECUPERAR SENHA
        $router->post('/usuario/recuperasenha', [
            function ($request) {
                return new ResponseClass(200, (new UsuarioApiController())->recuperasenha($request));
            }
        ]);


        $router->post('/buscarmunicipio', [
            function ($request) {
                return new ResponseClass(200, (new UsuarioApiController())->buscarmunicipio($request));
            }
        ]);



        return $router;
    }
}
