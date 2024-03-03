<?php
namespace App\Controllers;

use App\Components\NavbarComponents;
use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Core\PageCore;
use App\Core\Template\LoggedTemplate;
use App\Core\Template\TemplateAbstract;
use App\Daos\ModuloDao;
use App\Daos\SisModuloDao;
use App\Libs\SessionLib;

class HomeController extends ControllerCore implements ControllerInterface
{
    /*
    * chama a view index.php   /
    */
    public function index($args = null)
    {
        // VERIFICA SE ESTA LOGADO
        try {
            $this->isLogged();
            $modulos = (new ModuloDao())->buscaModulosUsuario(SessionLib::getValue("CODUSUARIO"));

            return $this->render(
                TemplateAbstract::LOGGED,
                'home/index',
                array(
                    'MODULOS' => $modulos,
                    'TITLE' => "Início"
                ),
            );
         } catch (\Error $e) {
            return $e;
        }
    }

}
