<?php

namespace App\Core\Template;

use App\Core\Controller\ControllerCore;
use App\Core\PageCore;
use App\Libs\AlertLib;
use App\Libs\FuncoesLib;
use App\Libs\JwtLib;
use App\Libs\SessionLib;
use App\Libs\twig\TwigLib;

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

    public function __construct(){
        $this->controller = new ControllerCore();

    }

    protected function navigationBottom($menuAtivo = 1, $data = null)
    {
        $dataSessao = $data['SESSAO'];

        $menuAtivo == 1 ? $menu[1] = "navigation-menu-item-active" : $menu[1] = "";
        $menuAtivo == 2 ? $menu[2] = "navigation-menu-item-active" : $menu[2] = "";
        $menuAtivo == 3 ? $menu[3] = "navigation-menu-item-active" : $menu[3] = "";
        $menuAtivo == 4 ? $menu[4] = "navigation-menu-item-active" : $menu[4] = "";


        return (new PageCore)->render("components/navigation_bottom", [
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
        $dataSessao = $data['SESSAO'];

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

        return (new PageCore)->render("components/navbar", [
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

        header("Cache-Control: max-age=1800, public");
        return $this->render("components/head",[
            "author" => $this->headerAuthor,
            "title" => $this->headerTitle,
            "description" => $this->headerDescription,
            "url" => $this->headerUrl,
            "image" => $this->headerImage,
            "keywords" => $this->headerKeywords,
            "color" => $this->headerColor,
            "fbAppId" => $this->headerFbAppId,
            "configVersionCode" => CONFIG_VERSION_CODE]);

    }

    //CARREGA FIM HTML
    protected function javascript($view){

        $result = $this->render("components/javascript",[
            "configVersionCode" => CONFIG_VERSION_CODE], false);

        $this->setView($view);

        if (file_exists(dirname(__DIR__, 2) . "/templates/{$this->viewDirectory}/{$this->viewDirectory}.js")) {
            $result .= "<!--SCRIPT CONTROLLER-->\n";
            $result .= "<script>";
            $result .= file_get_contents(dirname(__DIR__,2)."/templates/{$this->viewDirectory}/{$this->viewDirectory}.js");
            $result .= "</script>";
        }

        if (file_exists(dirname(__DIR__, 2) . "/templates/{$this->viewUrl}/{$this->viewFile}.js")) {
            $result .= "<!--SCRIPT VIEW-->\n";
            $result .= "<script>";
            $result .= file_get_contents(dirname(__DIR__,2)."/templates/{$this->viewUrl}/{$this->viewFile}.js");
            $result .= "</script>";
        }

        // SERVICE
        if (file_exists(dirname(__DIR__, 3) . "/src/templates/{$this->viewUrl}/{$this->viewFile}_service.js")) {
            $result .= "<!--SCRIPT SERVICE-->\n";
            $result .= "<script>";
            $result .= file_get_contents(dirname(__DIR__, 3)."/src/templates/{{$this->viewUrl}}/{$this->viewFile}_service.js");
            $result .= "</script>";
        }


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

    protected function setHead($title = null, $description = null, $image = null){
        $funcoesClass = new FuncoesLib();
        $this->controller = new ControllerCore();

        $this->titlePage = $title;

        $this->headerAuthor = CONFIG_HEADER['author'];
        $this->headerTitle = empty($title) ? CONFIG_HEADER['title'] : CONFIG_SITE['name'] . " › " . $title;
        $this->headerDescription = empty($description) ? CONFIG_HEADER['description'] : $description;
        $this->headerUrl = $funcoesClass->pegarUrlAtual();
        $this->headerImage = empty($image) ? CONFIG_HEADER['image'] : $image;
        $this->headerKeywords = CONFIG_HEADER['keywords'];
        $this->headerColor = CONFIG_HEADER['color'];
        $this->headerFbAppId = CONFIG_HEADER['fbAppId'];
    }

    protected function breadcrumb($data = []){
        $title = $data["TITLE"]??"Início";
        $titleBreadcrumb = $data["TITLEBREADCRUMB"]??'<li class="breadcrumb-item-custom"><a href="/">Início</a></li>';
        $titleImage = $data["TITLEIMAGE"]??"mdi-home-outline";

        if (!empty($data["TITLE"]))
            return '<header class="view-navbar">
                        <div class="container my-container pt-1">
                            <div class="div-title-page px-0">
                                <div class="row m-0">
                                    <div class="w-auto pe-0 align-middle" style="line-height: 0px;padding-top: 16px;margin-bottom: 10px;"><span class="mdi mdi-48px '.$titleImage.'" style="color: grey"></span></div>
                                    <div class="col-9 p-0">
                                        <div class="ps-2 pb-1">
                                            <nav aria-label="breadcrumb">
                                                <ul class="breadcrumb breadcrumb-custom">
                                                    '.$titleBreadcrumb.'
                                                </ul>
                                            </nav>
                                        </div>
                                        <div class="ps-2">
                                            <p class="title-page"> '.$title.'</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>';
        else
            return "";
    }

    public function render(string $view, $data = [], $print = true, $cache = false){
        return (new TwigLib())->render($view, $data, $print, $cache);
    }

    protected function addCss(string $url)
    {
        return "<link href='{$url}?v=" . CONFIG_VERSION_CODE . "' rel='stylesheet'>";
    }

    protected function addJs(string $url)
    {
        return "<script src='{$url}?v=" . CONFIG_VERSION_CODE . "'></script>";
    }

    protected function setView($url)
    {
        $controller = explode("/", $url);

        $this->viewUrl = $url;
        $this->viewDirectory = $controller[0];
        $this->viewFile = $controller[1];
    }

    protected function addServiceWork(){
        return "<script>
                if ('serviceWorker' in navigator) {
                     window.addEventListener('load', () => {
                        navigator.serviceWorker.register('/sw.js',{scope: './'}).then(function (registration) {
                            //console.log('ServiceWorker registration successful with scope: ', registration.scope);
                            }, function (err) {
                            //console.log('ServiceWorker registration failed: ', err);
                            }
                        );
                    });
                }
            </script>";
    }

    protected function pageDefault(string $view, $head = [], $data = [], $css = [], $js = [])
    {
        try {
            $this->setHead($head['TITLE'] ?? "");

            $data['SESSAO'] = SessionLib::getDataSession();
            $data['JWT'] = (new JwtLib())->encode();

            $data['head'] = $this->head();
            $data['main'] = $this->render($view, $data, false);
            $data['footer'] = $this->footer();
            $data['javascript'] = $this->javascript($view);
            $data['css'] = $this->addCssJsPage($css, "css");
            $data['js'] = $this->addCssJsPage($js, "js");

            return $this->render("components/theme", $data);

        } catch (\Error $e) {
            return $e;
        }

    }

    protected function addCssJsPage($cssjs = [], $type = "css"): array
    {
        $data = [];
        foreach ($cssjs as $item) {
            if ($type === "css")
                $data[] = $this->addCss($item);
            else
                $data[] = $this->addJs($item);

        }

        return $data;

    }

}