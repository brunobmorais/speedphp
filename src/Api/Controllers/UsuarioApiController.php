<?php

namespace App\Api\Controllers;

use App\Api\Lib\RequestClass;
use App\Daos\CidadeDao;
use App\Daos\EstoqueDao;
use App\Daos\LogDao;
use App\Daos\MunicipioDao;
use App\Daos\RecuperaSenhaDao;
use App\Daos\SICodigoValidadorDao;
use App\Daos\UsuarioDao;
use App\Libs\EmailLib;
use App\Libs\FuncoesLib;
use App\Libs\JwtLib;
use App\Libs\LoginLimit;
use App\Libs\SessionLib;
use App\Libs\TemplateEmailLib;
use App\Models\UsuarioCidadeModel;
use App\Models\UsuarioModel;

class UsuarioApiController
{

    public function login(RequestClass $request)
    {
        $func = new FuncoesLib();
        $usuarioModel = new UsuarioModel();
        $usuarioDao = new UsuarioDao();
        $jwtTokenClass = new JwtLib();

        $cpf = $func->removeCaracteres($request->getJsonParams()['cpf'] ?? "");
        $senha = $request->getJsonParams()['senha'] ?? "";
        $csrf = $request->getJsonParams()['csrf'] ?? "";
        $csrfSession = SessionLib::getValue("CSRF");

        if ($csrf!=$csrfSession || empty($csrfSession)) {
            $retorno['error'] = true;
            $retorno['message'] = "Token inválido";
            return $retorno;
        }

        // VERIFICA TENTATIVAS DE LOGIN
        $result = (new LoginLimit())->check();
        if (!$result) {
            $retorno['error'] = true;
            $retorno['message'] = "Você excedeu o número de tentativas, aguarde 20 segundos e tente novamente";
            return $retorno;
        }

        $usuarioModel->setCPF($cpf)->setSENHA($senha);
        $usuarioResult = $usuarioDao->buscarCpf($cpf);
        $retorno = array();

        if (!empty($usuarioResult)) {

            if ($func->verify_password_hash($usuarioModel->getSenha(), $usuarioResult->getSenha())) {

                if ($usuarioResult->getSITUACAO() == "0") {

                    $retorno['error'] = true;
                    $retorno['message'] = "Usuário inativo, entre em contato com o suporte!";
                    return $retorno;
                }

                $data['id'] = $usuarioResult->getCODUSUARIO();
                $token = $jwtTokenClass->encode(43200, $data); // 30 dias de validade

                $redireciona = !empty(SessionLib::getValue('REDIRECIONA')) ? SessionLib::getValue('REDIRECIONA') : '/';
                SessionLib::setDataSession($usuarioResult);

                (new LogDao())->salvaLog("LOGIN: ENTROU NO SISTEMA");

                $retorno['codusuario'] = $usuarioResult->getCODUSUARIO();
                $retorno['error'] = false;
                $retorno['token'] = $token;
                $retorno['message'] = "";
                $retorno['redireciona'] = $redireciona;

            } else {
                $result = (new LoginLimit())->check();
                if (!$result)
                    $message = "Você excedeu o número de tentativas, aguarde 20 segundos e tente novamente";
                else
                    $message = "Login ou senha incorreto!";

                $retorno['error'] = true;
                $retorno['message'] = $message;
            }
        } else {
            $retorno['error'] = true;
            $retorno['message'] = "Login ou senha incorreto!";
        }

        return $retorno;
    }

    public function recuperasenha(RequestClass $request)
    {

        $func = new FuncoesLib();

        $usuarioDao = new UsuarioDao();

        $cpf = $request->getJsonParams()['cpf'] ?? "";
        $cpf = $func->removeCaracteres($cpf);

        $usuarioModel = $usuarioDao->buscarCpf($cpf);

        if (!empty($usuarioModel)) {

            $minutos = 60;
            $token = (new JwtLib())->encode($minutos, ["id" => $usuarioModel->getCODUSUARIO()]);
            $vencimento = date('Y-m-d H:i', strtotime("+{$minutos} minutes"));

            (new SICodigoValidadorDao())->insert("CODUSUARIO, TOKEN, VALIDADE, SITUACAO", array($usuarioModel->getCODUSUARIO(), $token, $vencimento, 1));

            $msge = (new TemplateEmailLib)->template1(
                "Recuperação de senha",
                "Solicitação de recuperação de senha de acesso",
                "Você quer criar uma nova senha, certo?",
                CONFIG_SITE['url'] . "/usuario/novasenha/" . $token,
                "Sim, criar nova senha");


            if (EmailLib::sendEmail("Recuperacao de Senha", $msge, array($usuarioModel->getEMAIL()))) {
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

        SessionLib::setValue("CODPESSOA",$usuarioModel->getCODUSUARIO());
        (new LogDao())->salvaLog("LOGIN: ESQUECI SENHA" );
        SessionLib::apagaSessao();
        return $retorno;

    }

    public function buscarmunicipio(RequestClass $request)
    {

        $json = $request->getJsonParams();

        if (!empty($json['uf'])) {
            $municipioDao = new CidadeDao();

            $where = "WHERE UF = '" . $json['uf'] . "' ORDER BY NOME";
            $result = $municipioDao->select("*", $where);
            if ($result > 0) {
                $retorno['error'] = false;
                $retorno['data'] = $result;

            } else {
                $retorno['error'] = true;
            }
        } else {
            $retorno['error'] = true;
        }

        return $retorno;

    }

}
