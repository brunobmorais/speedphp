<?php
namespace App\Modules\Usuario\Esquecisenha;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerModuleInterface;
use App\Daos\AcessoDao;
use App\Enums\LocalAcesso;
use App\Libs\CookieLib;
use App\Libs\SessionLib;
use App\Libs\Template\TemplateAbstract;

class EsquecisenhaController extends ControllerCore implements ControllerModuleInterface
{
    public function index($args = null)
    {
        try {
            $data['HEAD']['title'] = "Esqueci Senha";
            return $this->render(
                TemplateAbstract::BLANK,
                'usuario/esquecisenha',
                $data
            );
        } catch (\Error $e) {
            return $e;

        }
    }

    public function action(array $args = [])
    {

    }
}