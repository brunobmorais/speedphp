<?php
namespace App\Modules\Usuario\Logoff;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerModuleInterface;
use App\Libs\CookieLib;
use App\Libs\SessionLib;

class LogoffController extends ControllerCore implements ControllerModuleInterface
{
    public function index($args = null)
    {
        try {
            $redireciona = SessionLib::getValue("REDIRECIONA");
            SessionLib::apagaSessao();
            CookieLib::deleteValue("TOKEN");
            SessionLib::setValue("REDIRECIONA", $redireciona);
            $this->redirect("/usuario/login");
        } catch (\Error $e) {
            return $e;
        }
    }

    public function action(array $args = [])
    {

    }
}