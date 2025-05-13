<?php

namespace App\Modules\Usuario\Cadastro;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerModuleInterface;
use App\Daos\AcessoDao;
use App\Enums\LocalAcesso;
use App\Libs\SessionLib;
use App\Libs\Template\TemplateAbstract;
use Ramsey\Uuid\Uuid;

class CadastroController extends ControllerCore implements ControllerModuleInterface
{
    public function index($args = [])
    {
        try {
            (new AcessoDao())->setVisita(LocalAcesso::CADASTRO);
            $data['HEAD']['title'] = "Cadastro";
            //SessionLib::apagaSessao();
            SessionLib::setValue("CSRF", Uuid::uuid4()->toString());
            return $this->render(
                TemplateAbstract::BLANK,
                'usuario/cadastro',
                $data
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