<?php

namespace App\Modules\Usuario\Addnovasenha;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerModuleInterface;
use App\Daos\AcessoDao;
use App\Daos\SICodigoValidadorDao;
use App\Daos\UsuarioDao;
use App\Enums\LocalAcesso;
use App\Libs\AlertLib;
use App\Libs\FuncoesLib;
use App\Libs\JwtLib;
use App\Libs\Template\TemplateAbstract;
use App\Models\UsuarioModel;

class AddnovasenhaController extends ControllerCore implements ControllerModuleInterface
{
    public function index($args = [])
    {

    }

    public function action(array $args = [])
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
}