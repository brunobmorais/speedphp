<?php

namespace App\Modules\Usuario;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerModuleInterface;

class UsuarioController extends ControllerCore implements ControllerModuleInterface
{
    public function index($args = null)
    {
        $this->redirect("/");
    }

    public function action(array $args = [])
    {
        // TODO: Implement action() method.
    }
}
