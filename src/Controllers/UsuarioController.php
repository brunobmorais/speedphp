<?php

namespace App\Controllers;


use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Core\PageCore;
use App\Core\Template\DefaultTemplate;
use App\Daos\PessoaDao;
use App\Daos\SICodigoValidadorDao;
use App\Daos\UsuarioDao;
use App\Libs\AlertLib;
use App\Libs\CookieLib;
use App\Libs\FuncoesLib;
use App\Libs\JwtLib;
use App\Libs\SessionLib;
use App\Libs\Template\TemplateAbstract;
use App\Models\PessoaModel;
use App\Models\UsuarioModel;
use Ramsey\Uuid\Uuid;

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
     * @return \Error|\Error|null
     */
    public function meusdados($args = null)
    {
        try {

            $this->isLogged();

            $data["TITLE"] = "Meus dados";
            $data["TITLEIMAGE"] = "mdi mdi-account-outline";
            $data["TITLEBREADCRUMB"] = "<li class='breadcrumb-item-custom '><a href='/'>Inicio</a></li><i class='mdi mdi-chevron-right mx-1' aria-hidden='true'></i></li><li class='breadcrumb-item-custom '><a href='./'>Meus Dados</a></li>";

            return $this->render(
                TemplateAbstract::LOGGED,
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
            $resultUsuarioModel = $pessoaDao->buscarUsuarioModelId($_POST['CODUSUARIO']);
            $sessaoClass->setDataSession($resultUsuarioModel->getDataSession());

            $alertaClass->success("Atualização realizada com sucesso!", "/usuario/meusdados");
        } else {
            $alertaClass->danger("Erro ao atualizar informações", "/usuario/meusdados");
        }
    }

    public function logoff($args = [])
    {
        try {
            $redireciona = SessionLib::getValue("REDIRECIONA");
            SessionLib::apagaSessao();
            SessionLib::setValue("REDIRECIONA", $redireciona);
            $this->redirect("/usuario/login");
        } catch (\Error $e) {
            return $e;
        }
    }

    public function login($args = null)
    {
        try {
            $data['HEAD']['title'] = "Login";
            //SessionLib::apagaSessao();
            SessionLib::setValue("CSRF", Uuid::uuid4()->toString());
            return $this->render(
                TemplateAbstract::BLANK,
                'usuario/login', $data);
        } catch (\Error $e) {
            return $e;
        }

    }

    public function esquecisenha($args = null)
    {
        try {
            $head['TITLE'] = "Esqueci senha";
            return $this->render(
                TemplateAbstract::BLANK,
                'usuario/esquecisenha', $head);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function novasenha($args = null)
    {
        try {
            (new FuncoesLib())->setDisplayError();
            $siCodigoValidador = new SICodigoValidadorDao();

            //PEGANDO PARAMETRO GET
            if (empty($args[0])) {
                $this->redirect("/usuario/login");
                return;
            }
            $data['TOKEN'] = $args[0];
            $dataToken = (new JwtLib())->decode($data["TOKEN"]);
            if ($dataToken) {
                $data["CODIGOVALIDADOR"] = $siCodigoValidador->validarToken($data['TOKEN']);
                if (!empty($data["CODIGOVALIDADOR"])) {
                    $data['TITLE'] = "Nova senha";
                    return $this->render(
                        TemplateAbstract::BLANK,
                        'usuario/novasenha',
                        $data);
                }

                (new AlertLib())->warning("Token não encontrado ou expirou", "/usuario/login");
            } else {
                (new AlertLib())->warning("Token exipirou ou é inválido", "/usuario/login");
            }
        } catch (\Error $e) {
            return $e;
        }
    }

    public function addnovasenha()
    {
        try {
            $this->validateRequestMethod("POST");

            $func = new FuncoesLib();
            $usuarioModel = new UsuarioModel($_POST);
            $alerta = new AlertLib();
            $siCodigoTokenDao = new SICodigoValidadorDao();
            $usuarioDao = new UsuarioDao();

            $usuarioModel->setSenha($func->create_password_hash($usuarioModel->getSENHA()));

            $objCodigoValidador = $siCodigoTokenDao->validarToken($this->postParams("TOKEN"));

            // VERIFICA SE É VALIDO
            if (!$objCodigoValidador) {
                $alerta->warning('Sua solicitação expirou! Tente novamente.', '/usuario/esquecisenha');
            }

            // ATUALIZA TOKEN COMO UTILIZADO
            $siCodigoTokenDao->update("SITUACAO", array(0, $objCodigoValidador[0]->CODCODIGOVALIDADOR), "CODCODIGOVALIDADOR=?");

            // ATUALIZA SENHA
            if (!$usuarioDao->updateSenha($usuarioModel)) {
                $alerta->warning('Aconteceu um erro, tente mais tarde', '/usuario/login');
            }

            $alerta->success("Alteração realizada!", '/usuario/login');
        } catch (\Error $e) {
            return $e;
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

        $resultUsuarioModel = $usuarioDao->buscarUsuarioModelId($_POST["codusuario"]);

        if (!empty($resultUsuarioModel)) {
            if ($func->verify_password_hash($usuarioModel->getSENHA(), $resultUsuarioModel->getSENHA())) {
                $usuarioModel->setSENHA($func->create_password_hash($_POST['novasenha']));
                if (!$usuarioDao->updateSenha($usuarioModel)) {
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
