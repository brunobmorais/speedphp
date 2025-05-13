<?php

namespace App\Modules\Usuario\Alterarsenha;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerModuleInterface;
use App\Daos\PessoaDao;
use App\Libs\AlertLib;
use App\Libs\FuncoesLib;
use App\Models\UsuarioModel;

class AlterarsenhaController extends ControllerCore implements ControllerModuleInterface
{
    public function index($args = [])
    {
        $this->isLogged();
        $this->validateRequestMethod("POST");

        $func = new FuncoesLib();
        $usuarioModel = new UsuarioModel();
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
    
    public function action($args = [])
    {
        // Código do método action
    }
}