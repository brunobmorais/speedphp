<?php

namespace App\Api\Controllers;

use App\Api\Lib\RequestClass;
use App\Daos\EstoqueDao;
use App\Daos\LogDao;
use App\Daos\MunicipioDao;
use App\Daos\PessoaDao;
use App\Daos\RecuperaSenhaDao;
use App\Libs\EmailLib;
use App\Libs\FuncoesLib;
use App\Libs\JwtLib;
use App\Libs\SessionLib;
use App\Libs\TemplateEmailLib;
use App\Models\PessoaModel;
use App\Models\UsuarioCidadeModel;
use App\Models\UsuarioModel;

class UsuarioApiController
{

    public function login(RequestClass $request)
    {
        $sessao = new SessionLib();
        $func = new FuncoesLib();
        $pessoaModel = new PessoaModel();
        $usuarioDao = new PessoaDao();
        $jwtTokenClass = new JwtLib();

        $cpf = $func->removeCaracteres($request->getPostVars()['cpf'] ?? "");
        $senha = $request->getPostVars()['senha'] ?? "";

        $pessoaModel->setCPF($cpf)->setSENHA($senha);
        $usuarioResult = $usuarioDao->buscarFuncionarioCpfCnpj($cpf);
        $retorno = array();

        if (!empty($usuarioResult)) {

            if ($func->verify_password_hash($pessoaModel->getSenha(), $usuarioResult->getSenha())) {

                if ($usuarioResult->getSITUACAO() == "0") {

                    $retorno['error'] = true;
                    $retorno['msg'] = "Usuário inativo, entre em contato com o suporte!";
                    return $retorno;
                }

                $data['id'] = $usuarioResult->getCODUSUARIO();
                $token = $jwtTokenClass->encode(1440, $data);

                $redireciona = !empty($sessao->getValue('REDIRECIONA')) ? $sessao->getValue('REDIRECIONA') : '/';
                $sessao->setDataSession($usuarioResult);

                $retorno['codusuario'] = $usuarioResult->getCODUSUARIO();
                $retorno['error'] = false;
                $retorno['token'] = $token;
                $retorno['msg'] = "";
                $retorno['redireciona'] = $redireciona;

            } else {
                $retorno['error'] = true;
                $retorno['msg'] = "Login ou senha incorreto!";
            }
        } else {
            $retorno['error'] = true;
            $retorno['msg'] = "Login ou senha incorreto!";
        }

        return $retorno;
    }

    public function recuperasenha(RequestClass $request)
    {

        $func = new FuncoesLib();

        $pessoaDao = new PessoaDao();

        $cpf = $request->getJsonParams()['cpf'] ?? "";

        $cpf = $func->removeCaracteres($cpf);

        $pessoaModel = $pessoaDao->buscarFuncionarioCpfCnpj($cpf);

        if (!empty($pessoaModel)) {

            $token = (new JwtLib())->encode(60, ["id" => $pessoaModel->getCODUSUARIO()]);

            $msge = (new TemplateEmailLib)->template1(
                "Recuperação de senha",
                "Solicitação de recuperação de senha de acesso",
                "Você quer criar uma nova senha, certo?",
                CONFIG_SITE['url'] . "/usuario/novasenha/" . $token,
                "Sim, criar nova senha");


            if (EmailLib::sendEmail("Recuperacao de Senha", $msge, array($pessoaModel->getEMAIL()))) {
                $retorno['error'] = false;
                $retorno['msg'] = "Se tiver cadastrado, você receberá um link para redefinir a senha1!";

            } else {
                $retorno['error'] = true;
                $retorno['msg'] = "Erro ao enviar email de recuperação";
            }

        } else {
            $retorno['error'] = false;
            $retorno['msg'] = "Se tiver cadastrado, você receberá um link para redefinir a senha2!";
        }

        return $retorno;

    }


}
