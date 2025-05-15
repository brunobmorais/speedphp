<?php
namespace App\Controllers;

use App\Components\NavbarComponents;
use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Core\PageCore;
use App\Daos\AcessoDao;
use App\Enums\LocalAcesso;
use App\Libs\Template\TemplateAbstract;

class InstitucionalController extends ControllerCore implements ControllerInterface
{
    /*
    * chama a view index.php   /
    */
    public function index($getParametro = null)
    {
        $this->redirect("/");
    }

    public function privacidade($args = null){

        try {
            // CARREGA VIEW
            return $this->render(
                TemplateAbstract::NOT_LOGGED,
                'institucional/privacidade',
                ['TITLE' => "Termo de uso e privacidade",
                    "configSiteName" => CONFIG_SITE['name'],
                    "configSiteNameFull" => CONFIG_SITE['nameFull'],
                    "configSiteEmail" => CONFIG_SITE['email'],
                    "configSitePhone" => CONFIG_SITE['phone'],
                    "configSiteUrl" => CONFIG_SITE['url'],
                    "configSiteDomain" => CONFIG_SITE['domain'],
                    "configSiteAndress" => CONFIG_SITE['andress'],
                    "configSiteCnpj" => CONFIG_SITE['cnpj'],
                ]
            );
        } catch (\Error $e) {
            return $e;
        }
    }

    public function sobre($args = null)
    {

        try {
            $data['HEAD']['title'] = "Sobre a Via Esporte";
            $data['TITLE'] = "Sobre a Via Esporte";

            // CARREGA VIEW
            return $this->render(
                TemplateAbstract::NOT_LOGGED,
                'institucional/sobre',
                $data
            );
        } catch (\Error $e) {
            return $e;
        }
    }

}
