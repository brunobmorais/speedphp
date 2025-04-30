<?php

namespace App\Api\Controllers;

use App\Api\Lib\RequestClass;

use App\Daos\PessoaDao;
use App\Daos\PessoaFisicaDao;
use App\Daos\PessoaJuridicaDao;
use App\Libs\FuncoesLib;
use App\Models\EnderecoModel;
use App\Models\PessoaFisicaModel;
use App\Models\PessoajuridicaModel;
use App\Models\PessoaModel;


class PessoaApiController
{

    public function getPessoafisica(RequestClass $request, $args)
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

    public function getPessoa(RequestClass $request, $args)
    {
        $func = new FuncoesLib();
        $pessoaFisicaDao = new PessoaFisicaDao();
        $pessoaJuridicaDao = new PessoaJuridicaDao();


        $cpfcnpj = $func->removeCaracteres($args['cpfcnpj'] ?? "");

        if (empty($cpfcnpj))
        {
            return [
                "error" => true,
                "message" => "Parâmetro inválido",
                "data" => []
            ];
        }

        if (strlen($cpfcnpj)>11)
            $usuarioResult = $pessoaJuridicaDao->buscarPessoaCNPJ($cpfcnpj);
        else
            $usuarioResult = $pessoaFisicaDao->buscarPessoaCPF($cpfcnpj);

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


    /**
     * busca funcionario por nome ou cpf
     *
     * @param RequestClass $request
     * @param array $args
     * @return array
     */

    public function postBuscarFuncionario(RequestClass $request){
        
        
        $json = $request->getJsonParams();
        if (!empty($json['NOME'])) {
            $result = (new GpFuncionarioDao)->buscarTodos($json['NOME']); 
            $arrayResult = [];
           
            if ($result > 0) {
                foreach($result as $funcionario){
                    array_push($arrayResult, ['id' => $funcionario->CODPESSOA, 'text' => $funcionario->NOME.' - '.$funcionario->CPF]);
                }

                $retorno['error'] = false;
                $retorno['data'] = $arrayResult;

            } else {
                $retorno['error'] = true;
            }
        } else {
            $retorno['error'] = true;
        }

        return $retorno;

    }

    public function postPessoa(RequestClass $request, $args = [])
    {
        $objJson = $request->getJsonParams();

        $pessoaModel = new PessoaModel($objJson);
        $enderecoModel = new EnderecoModel($objJson);

        if ($objJson["TIPOPESSOA"] == "F") {
            $pessoaFisicaModel = new PessoaFisicaModel($objJson);
            $pessoaFisicaModel->setCPF((new FuncoesLib())->removeCaracteres($pessoaFisicaModel->getCPF()));
            $pessoaFisicaModel->setDATANASCIMENTO((new FuncoesLib())->formatDataBanco($pessoaFisicaModel->getDATANASCIMENTO()));
        } else {
            $pessoaJuridicaModel = new PessoaJuridicaModel($objJson);
            $pessoaJuridicaModel->setNOMEFANTASIA((new FuncoesLib())->textoPrimeiraLetraMaiusculoCadaPalavra($pessoaJuridicaModel->getNOMEFANTASIA()));
            $pessoaJuridicaModel->setCNPJ((new FuncoesLib())->removeCaracteres($pessoaJuridicaModel->getCNPJ()));
        }

        $pessoaModel->setNOME((new FuncoesLib())->textoPrimeiraLetraMaiusculoCadaPalavra($pessoaModel->getNOME()));

        if ($objJson["ACTION"] == "update") {
            if ($objJson["TIPOPESSOA"] == "F")
                $result = (new PessoaDao())->atualizarPessoaFisica($enderecoModel, $pessoaModel, $pessoaFisicaModel);
            else
                $result = (new PessoaDao())->atualizarPessoaJuridica($enderecoModel, $pessoaModel, $pessoaJuridicaModel);
        } else {
            if ($objJson["TIPOPESSOA"] == "F")
                $result = (new PessoaDao())->inserirPessoaFisica($enderecoModel, $pessoaModel, $pessoaFisicaModel);
            else
                $result = (new PessoaDao())->inserirPessoaJuridica($enderecoModel, $pessoaModel, $pessoaJuridicaModel);
        }

        return $result;
    }


}
