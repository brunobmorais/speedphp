<?php

namespace App\Modules\Home;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerModuleInterface;
use App\Daos\AcessoDao;
use App\Daos\EventoDao;
use App\Daos\ModuloDao;
use App\Enums\LocalAcesso;
use App\Libs\SessionLib;
use App\Libs\Template\TemplateAbstract;

class HomeController extends ControllerCore implements ControllerModuleInterface
{
    public function index($args = null)
    {
// VERIFICA SE ESTA LOGADO
        try {
            $this->isLogged();
            $modulos = (new ModuloDao())->buscaModulosUsuario(SessionLib::getValue("CODUSUARIO"));

            return $this->render(
                TemplateAbstract::LOGGED,
                'home/index',
                array('MODULOS' => $modulos, 'TITLE' => "Início"
                ),
            );
        } catch (\Error $e) {
            return $e;
        }
    }
    
    public function action($args = [])
    {
        // Código do método action
    }
}