<?php

namespace App\Modules\Usuario\Login;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Core\Controller\ControllerModuleInterface;
use App\Daos\AcessoDao;
use App\Enums\LocalAcesso;
use App\Libs\SessionLib;
use App\Libs\Template\TemplateAbstract;
use Ramsey\Uuid\Uuid;

class LoginController extends ControllerCore implements ControllerModuleInterface
{
    public function index($args = null)
    {
        try {
            (new AcessoDao())->setVisita(LocalAcesso::LOGIN);

            $data['HEAD']['title'] = "Login";
            //SessionLib::apagaSessao();
            SessionLib::setValue("CSRF", Uuid::uuid4()->toString());
            return $this->render(
                TemplateAbstract::BLANK,
                'usuario/login',
                $data
            );
        } catch (\Error $e) {
            return $e;
        }
    }

    public function action(array $args = [])
    {
        // TODO: Implement action() method.
    }
}