<?php

namespace App\Libs\Template;

use App\Controllers\EventoController;
use App\Controllers\OrganizadorController;
use App\Core\Controller\ControllerCore;
use App\Daos\UsuarioDao;
use App\Libs\AlertLib;
use App\Libs\FuncoesLib;
use App\Libs\SessionLib;
use App\Libs\Twig\TwigLib;

Trait TemplateTrait
{

    /**
     * @var string
     */
    private $alertaMsgRecebida;

    /**
     * @var ControllerCore
     */
    protected $controller;

    private $viewDirectory = null;
    private $viewFile = null;
    private $viewUrl = null;

    private $headerAuthor;
    private $headerTitle;
    private $headerDescription;
    private $headerUrl;
    private $headerImage;
    private $headerKeywords;
    private $headerColor;
    private $headerFbAppId;

    /**
     * @var string
     */
    private $titlePage;

    private $levelDirectory = 3;

    public function __construct(){
        $this->controller = new ControllerCore();

    }

    protected function navigationBottom($menuAtivo = 1, $data = null)
    {
        $dataSessao = SessionLib::getDataSession();

        $menuAtivo == 1 ? $menu[1] = "navigation-menu-item-active" : $menu[1] = "";
        $menuAtivo == 2 ? $menu[2] = "navigation-menu-item-active" : $menu[2] = "";
        $menuAtivo == 3 ? $menu[3] = "navigation-menu-item-active" : $menu[3] = "";
        $menuAtivo == 4 ? $menu[4] = "navigation-menu-item-active" : $menu[4] = "";

        $data["PERFILUSUARIO"]["ADMINISTRADOR"] = (new UsuarioDao())->isAdministrador();
        $data["PERFILUSUARIO"]["ORGANIZADOR"] = (new UsuarioDao())->isOrganizadorEvento();
        $data["PERFILUSUARIO"]["POSSUIEVENTO"] = (new OrganizadorController())->hasEventoColaborador();
        $data["menu1"] = $menu[1];
        $data["menu2"] = $menu[2];
        $data["menu3"] = $menu[3];
        $data["menu4"] = $menu[4];

        return $this->render("components/theme/navigation_bottom", $data, false);
    }

    protected function navbar($data = [])
    {
        $dataSessao = SessionLib::getDataSession();

        if (isset($dataSessao)) {
            $primeiroNome = $dataSessao['PRIMEIRONOME']??"";
        } else {
            $primeiroNome = '';
        }

        $urlNavbar = substr(filter_input(INPUT_SERVER, 'REQUEST_URI')??"", 1);
        $urlNavbar = explode('/', $urlNavbar);
        $urlNavbar = $urlNavbar[0] ?? '';
        $btnVoltarNavbar = $urlNavbar != "" ? "" : "d-none";

        $data["PERFILUSUARIO"]["ADMINISTRADOR"] = (new UsuarioDao())->isAdministrador();
        $data["PERFILUSUARIO"]["ORGANIZADOR"] = (new UsuarioDao())->isOrganizadorEvento();
        $data["PERFILUSUARIO"]["POSSUIEVENTO"] = (new OrganizadorController())->hasEventoColaborador();

        $data["BTNVOLTAR"] = $btnVoltarNavbar;
        $data["PRIMEIRONOME"] = $primeiroNome;

        return $this->render("components/theme/navbar",
            $data,
            false);

    }

    protected function head($data = null)
    {

        //VERIFICA SE TEM MENSAGENS A SEREM MOSTRADAS AO USUÁRIO
        $alertaClass = new AlertLib();
        $this->alertaMsgRecebida = $alertaClass->checkAlert();

        $data["color"] = CONFIG_COLOR["color-primary"];

        return $this->render("components/theme/head",
            $data,
            false);
    }

    //CARREGA FIM HTML
    protected function javascript($view){

        $result = $this->render("components/theme/javascript",[
            "CONFIG_VERSION_CODE" => CONFIG_VERSION_CODE], false);

        //MOSTRA MENSAGEM AO USUÁRIO SE TIVER
        $result .= $this->alertaMsgRecebida;

        return $result;

    }

    //CARREGA RODAPE HTML
    protected function footer($data = null)
    {
        return $this->render("components/theme/footer",[
            "nameFull" => CONFIG_DEVELOPER['nameFull']
        ], false);

    }

    protected function sidebar($data = [])
    {
        $data["PERFILUSUARIO"]["ADMINISTRADOR"] = (new UsuarioDao())->isAdministrador();
        $data["PERFILUSUARIO"]["ORGANIZADOR"] = (new UsuarioDao())->isOrganizadorEvento();
        $data["PERFILUSUARIO"]["POSSUIEVENTO"] = (new OrganizadorController())->hasEventoColaborador();
        return $this->render("components/theme/sidebar",$data, false);

    }

    protected function breadcrumb($data = []){
        $data["TITLE"] = $data["TITLE"]??"Início";
        $data["TITLEBREADCRUMB"] = $data["TITLEBREADCRUMB"]??'<li class="breadcrumb-item-custom"><a href="/">Início</a></li>';
        $data["TITLEIMAGE"] = $data["TITLEIMAGE"]??"mdi-view-grid-outline";

        if (!empty($data["TITLE"]))
            return (new TwigLib())->renderPage("components/theme/header", $data, false);
        else
            return "";
    }

    public function render(string $view, $data = [], $print = true, $cache = false){
        return (new TwigLib())->renderPage($view, $data, $print, $cache);
    }

    public function servicesJS($data, $print)
    {
        return (new TwigLib())->servicesJS($data, $print);
    }

    protected function addCss(string $url)
    {
        return "<link href='{$url}?v=" . CONFIG_VERSION_CODE . "' rel='stylesheet'>";
    }

    protected function addJs(string $url)
    {
        return "<script src='{$url}?v=" . CONFIG_VERSION_CODE . "' ></script>";
    }

    protected function addCssJsPage($cssjs = [], $type = "css"): array
    {
        $data = [];
        foreach ($cssjs as $item) {
            if ($type == "css")
                $data[] = $this->addCss($item);
            else
                $data[] = $this->addJs($item);

        }

        return $data;

    }

}