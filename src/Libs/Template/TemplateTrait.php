<?php

namespace App\Libs\Template;

use App\Core\Controller\ControllerCore;
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


        return $this->render("components/navigation_bottom", [
            "nomeUsuario" => $dataSessao['PRIMEIRONOME'],
            "emailUsuario" => $dataSessao['EMAIL'],
            "imagemUsuario" => $dataSessao['IMAGEM']??"",
            "menu1" => $menu[1],
            "menu2" => $menu[2],
            "menu3" => $menu[3],
            "menu4" => $menu[4],
        ], false);
    }

    protected function navbar($data = null)
    {
        $dataSessao = SessionLib::getDataSession();

        if (isset($dataSessao)) {
            $primeiroNome = $dataSessao['PRIMEIRONOME']??"";
            $perfil = '';
        } else {
            $primeiroNome = '';
            $perfil = 'd-none';
        }

        $urlNavbar = explode('/', substr(filter_input(INPUT_SERVER, 'REQUEST_URI'), 1));
        $urlNavbar = $urlNavbar[0] ?? '';
        $btnVoltarNavbar = $urlNavbar != "" ? "" : "d-none";

        return $this->render("components/navbar", [
            "btnVoltar" => $btnVoltarNavbar,
            "primeiroNome" => $primeiroNome,
            "perfil" => $perfil
        ], false);

    }

    protected function head()
    {

        //VERIFICA SE TEM MENSAGENS A SEREM MOSTRADAS AO USUÁRIO
        $alertaClass = new AlertLib();
        $this->alertaMsgRecebida = $alertaClass->verificaMsg();

        return $this->render("components/head",
            [
                "author" => $this->headerAuthor,
                "title" => $this->headerTitle,
                "description" => $this->headerDescription,
                "url" => $this->headerUrl,
                "image" => $this->headerImage,
                "keywords" => $this->headerKeywords,
                "color" => $this->headerColor,
                "fbAppId" => $this->headerFbAppId,
                "configVersionCode" => CONFIG_VERSION_CODE
            ],
            false);
    }

    //CARREGA FIM HTML
    protected function javascript($view){

        $result = $this->render("components/javascript",[
            "configVersionCode" => CONFIG_VERSION_CODE], false);

        $this->setView($view);

        //MOSTRA MENSAGEM AO USUÁRIO SE TIVER
        $result .= $this->alertaMsgRecebida;

        return $result;

    }

    //CARREGA RODAPE HTML
    protected function footer($data = null)
    {
        return $this->render("components/footer",[
            "nameFull" => CONFIG_DEVELOPER['nameFull']
        ], false);

    }

    protected function sidebar($data = [])
    {
        return $this->render("components/sidebar",$data, false);

    }

    protected function setHead($title = null, $description = null, $image = null){
        $funcoesClass = new FuncoesLib();
        $this->controller = new ControllerCore();

        $this->titlePage = $title;

        $this->headerAuthor = CONFIG_HEADER['author'];
        $this->headerTitle = empty($title) ? CONFIG_HEADER['title'] : CONFIG_SITE['name'] . " › " . $title;
        $this->headerDescription = empty($description) ? CONFIG_HEADER['description'] : $description;
        $this->headerUrl = CONFIG_SITE["url"].$funcoesClass->pegarUrlAtual();
        $this->headerImage = empty($image) ? CONFIG_HEADER['image'] : $image;
        $this->headerKeywords = CONFIG_HEADER['keywords'];
        $this->headerColor = CONFIG_HEADER['color'];
        $this->headerFbAppId = CONFIG_HEADER['fbAppId'];
    }

    protected function breadcrumb($data = []){
        $data["TITLE"] = $data["TITLE"]??"Início";
        $data["TITLEBREADCRUMB"] = $data["TITLEBREADCRUMB"]??'<li class="breadcrumb-item-custom"><a href="/">Início</a></li>';
        $data["TITLEIMAGE"] = $data["TITLEIMAGE"]??"mdi-view-grid-outline";

        if (!empty($data["TITLE"]))
            return (new TwigLib())->renderPage("components/header", $data, false);
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

    protected function setView($url)
    {
        $controller = explode("/", $url);

        $this->viewUrl = $url;
        $this->viewDirectory = $controller[0];
        $this->viewFile = $controller[1];
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