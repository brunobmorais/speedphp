<?php

namespace App\Api\Controllers;

use App\Api\Lib\RequestClass;
use App\Daos\EstoqueDao;
use App\Daos\MunicipioDao;
use App\Daos\PessoaFisicaDao;
use App\Daos\RecuperaSenhaDao;
use App\Libs\FuncoesLib;
use App\Models\PessoaFisicaModel;
use App\Models\UsuarioCidadeModel;

class PessoaApiController
{

    public function pessoafisica(RequestClass $request, $args)
    {
        $func = new FuncoesLib();
        $pessoaFisicaModel = new PessoaFisicaModel();
        $pessoaFisicaDao = new PessoaFisicaDao();

        $cpf = $func->removeCaracteres($args['cpf'] ?? "");

        if (empty($cpf))
        {
            return [
                "error" => true,
                "message" => "Parâmetro inválido",
                "data" => []
            ];
        }

        $pessoaFisicaModel->setCPF($cpf);
        $usuarioResult = $pessoaFisicaDao->buscarPessoaCPF($cpf);

        if (empty($usuarioResult)) {
            return [
                "error" => false,
                "message" => "Não encontrado",
                "data" => []
            ];
        }


        return [
            "error" => false,
            "message" => "Encontrado. :)",
            "data" => $usuarioResult[0]
        ];
    }
}
