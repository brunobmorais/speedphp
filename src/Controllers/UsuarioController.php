<?php

namespace App\Controllers;


use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Core\PageCore;
use App\Core\Template\DefaultTemplate;
use App\Daos\PessoaDao;
use App\Libs\AlertLib;
use App\Libs\CookieLib;
use App\Libs\FuncoesLib;
use App\Libs\JwtLib;
use App\Libs\SessionLib;
use App\Models\PessoaModel;

class UsuarioController extends ControllerCore implements ControllerInterface
{
    /*
    * chama a view index.php   /
    */
    public function index($args = null)
    {
        $this->redirect("/");

    }

    /**
     * @param $args
     * @return \Error|\Exception|null
     */
    public function meusdados($args = null)
    {
        try {

            $this->isLogged();

            $data["TITLE"] = "Meus dados";
            $data["TITLEIMAGE"] = "mdi mdi-account-outline";
            $data["TITLEBREADCRUMB"] = "<li class='breadcrumb-item-custom '><a href='/'>Inicio</a></li><i class='mdi mdi-chevron-right mx-1' aria-hidden='true'></i></li><li class='breadcrumb-item-custom '><a href='./'>Meus Dados</a></li>";

            return $this->render(
                "Logged",
                'usuario/meusdados',
                $data
            );

        } catch (\Error $e) {
            return $e;
        }

    }

    public function meusDadosAction($args = null)
    {
        $this->isLogged();
        $this->validateRequestMethod("POST");

        $sessaoClass = new SessionLib();
        $alertaClass = new AlertLib();
        $pessoaDao = new PessoaDao();
        $pessoaModel = new PessoaModel();
        $funcoesClass = new FuncoesLib();

        $pessoaModel->fromMapToModel($_POST);

        // ATUALIZA PESSOA
        $atributos = "NOME, EMAIL, CPF";
        $parametros = array($pessoaModel->getNOME(), $pessoaModel->getEMAIL(), $pessoaModel->getCPF());
        $result = $pessoaDao->update($atributos, $parametros, "CODPESSOA={$pessoaModel->getCODPESSOA()}");

        if ($result) {
            $resultUsuarioModel = $pessoaDao->buscarFuncionarioModelId($_POST['CODUSUARIO']);
            $sessaoClass->setDataSession($resultUsuarioModel);

            $alertaClass->success("Atualização realizada com sucesso!", "/usuario/meusdados");
        } else {
            $alertaClass->danger("Erro ao atualizar informações", "/usuario/meusdados");
        }
    }

    public function logoff()
    {
        try {
            $cookie = new CookieLib();
            SessionLib::apagaSessao();
            $cookie->deleteValue("TOKEN_USER");
            $this->redirect("/usuario/login");
        } catch (\Error $e) {
            return $e;
        }
    }

    public function login($args = null)
    {
        try {
            $data['TITLE'] = "Login";
            return $this->render(
                "Default",
                'usuario/login',$data);
        } catch (\Error $e) {
            return $e;
        }

    }

    public function esquecisenha($args = null)
    {
        try {
            $head['TITLE'] = "Esqueci senha";
            return $this->render(
                "Default",
                'usuario/esquecisenha', $head);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function novasenha($args = null)
    {
        try {
            //PEGANDO PARAMETRO GET
            if (empty($args[0])) {
                $this->redirect("/usuario/login");
            } else {
                $data['TOKEN'] = $args[0];
                $dataToken = (new JwtLib())->decode($data["TOKEN"]);

                if ($dataToken) {
                    $pessoaDao = new PessoaDao();
                    $data["PESSOA"] = $pessoaDao->buscarTokenPessoa($data['TOKEN']);
                    if (!empty($data["PESSOA"])) {
                        $data['TITLE'] = "Nova senha";
                        // CARREGA VIEW
                        return $this->render(
            "Default",
                'usuario/novasenha', $data);
                    } else {
                        (new AlertLib())->warning("Token não encontrado", "/usuario/login");
                    }
                } else {
                    (new AlertLib())->warning("Token exipirou ou é inválido", "/usuario/login");
                }
            }
        } catch (\Error $e) {
            return $e;
        }
    }

    public function addnovasenha()
    {
        $this->validateRequestMethod("POST");

        $func = new FuncoesLib();
        $pessoaModel = new PessoaModel();
        $pessoaDao = new PessoaDao();
        $alerta = new AlertLib();

        $pessoaModel->setId($_POST["id"]);
        $pessoaModel->setIdFuncionario($_POST["idfuncionario"]);
        $pessoaModel->setToken($_POST["token"]);
        $pessoaModel->setSenha($func->create_password_hash($_POST['cdsenha']));

        $dados = $pessoaDao->buscarIdToken($pessoaModel);

        if ($dados == false) {
            $alerta->warning('Sua solicitação expirou! Tente novamente.', '/usuario/esquecisenha');
        } else {
            if ($pessoaDao->updateSenha($pessoaModel) == false) {
                $alerta->warning('Aconteceu um erro, tente mais tarde', '/usuario/login');
            } else {
                $alerta->success("Alteração realizada!", '/usuario/login');
            }
        }

    }

    public function alterarsenha()
    {
        $this->isLogged();
        $this->validateRequestMethod("POST");

        $func = new FuncoesLib();
        $usuarioModel = new PessoaModel();
        $usuarioDao = new PessoaDao();
        $alerta = new AlertLib();


        $usuarioModel->setSENHA($_POST['senhaatual'])
            ->setCODPESSOA($_POST["codpessoa"])->setCODUSUARIO($_POST['codusuario']);

        $resultUsuarioModel = $usuarioDao->buscarFuncionarioModelId($_POST["codusuario"]);

        if (!empty($resultUsuarioModel)) {
            if ($func->verify_password_hash($usuarioModel->getSENHA(), $resultUsuarioModel->getSENHA())) {
                $usuarioModel->setSENHA($func->create_password_hash($_POST['novasenha']));
                if ($usuarioDao->updateSenha($usuarioModel) == false) {
                    $alerta->warning('Aconteceu um erro, tente mais tarde', '/usuario/meusdados');
                } else {
                    $alerta->success("Alteração realizada!", '/usuario/meusdados');
                }
            } else {
                $alerta->danger("Senha atual não confere!", '/usuario/meusdados');
            }
        } else {
            $alerta->danger("Erro ao buscar usuário", '/usuario/meusdados');
        }
    }

}
