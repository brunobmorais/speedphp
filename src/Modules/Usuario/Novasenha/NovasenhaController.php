<?php

namespace App\Modules\Usuario\Novasenha;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerModuleInterface;
use App\Daos\AcessoDao;
use App\Daos\SICodigoValidadorDao;
use App\Enums\LocalAcesso;
use App\Libs\AlertLib;
use App\Libs\JwtLib;
use App\Libs\Template\TemplateAbstract;

class NovasenhaController extends ControllerCore implements ControllerModuleInterface
{
    public function index($args = [])
    {
        try {
            (new AcessoDao())->setVisita(LocalAcesso::NOVA_SENHA);

            $siCodigoValidador = new SICodigoValidadorDao();
            $jwtLib = new JwtLib();

            //PEGANDO PARAMETRO GET
            if (empty($args[0])) {
                $this->redirect("/usuario/login");
            }

            $data['TOKEN'] = $args[0];
            $dataToken = $jwtLib->decode($data["TOKEN"]);

            if ($dataToken) {
                $data["CODIGOVALIDADOR"] = $siCodigoValidador->validarToken($data['TOKEN']);
                if (!empty($data["CODIGOVALIDADOR"])) {
                    $data['HEAD']['title'] = "Nova senha";
                    return $this->render(
                        TemplateAbstract::BLANK,
                        'usuario/novasenha',
                        $data
                    );
                }

                (new AlertLib())->warning("Token não encontrado ou expirou", "/usuario/login");
            } else {
                (new AlertLib())->warning("Token exipirou ou é inválido", "/usuario/login");
            }
        } catch (\Error $e) {
            return $e;
        }
    }

    public function action($args = [])
    {
        // Código do método action
    }
}