<?php

namespace App\Api\Routers;


use App\Api\Controllers\PessoaApiController;
use App\Api\Controllers\PrevisaoDoTempoApiController;
use App\Api\Controllers\ProtocoloApiController;
use App\Api\Controllers\UsuarioApiController;
use App\Api\Lib\ResponseClass;
use App\Api\Lib\RouterClass;

class PessoaRouter
{

    public static function start(RouterClass $router): RouterClass
    {
        $router->get('/pessoa/pessoafisica/{cpf}', [
            "middlewares" => [
                "origin",
                "bearer-auth"
            ],
            function ($request, $cpf) {
                $args['cpf'] = $cpf;
                return new ResponseClass(200, (new PessoaApiController())->getPessoafisica($request, $args));
            }
        ]);

        $router->get('/pessoa/{cpfcnpj}', [
            "middlewares" => [
                "origin",
                "bearer-auth"
            ],
            function ($request, $cpfcnpj) {
                $args['cpfcnpj'] = $cpfcnpj;
                return new ResponseClass(200, (new PessoaApiController())->getPessoa($request, $args));
            }
        ]);

        $router->post('/pessoa/', [
            "middlewares" => [
                "origin",
                "bearer-auth"
            ],
            function ($request) {
                return new ResponseClass(200, (new PessoaApiController())->postPessoa($request));
            }
        ]);

        $router->post('/funcionario', [
            function ($request) {
                return new ResponseClass(200, (new PessoaApiController())->postBuscarFuncionario($request));
            }
        ]);


        return $router;
    }
}
