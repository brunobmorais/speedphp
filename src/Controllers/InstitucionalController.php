<?php
namespace App\Controllers;

use App\Components\NavbarComponents;
use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Core\PageCore;
use App\Core\Template\LoggedTemplate;
use App\Core\Template\TemplateAbstract;

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
                TemplateAbstract::LOGGED,
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

}
