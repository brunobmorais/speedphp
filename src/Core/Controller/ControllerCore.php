<?php

namespace App\Core\Controller;

use App\Controllers\ErroController;
use App\Daos\InstituicaoDao;
use App\Daos\ModuloDao;
use App\Daos\UsuarioDao;
use App\Libs\AlertLib;
use App\Libs\CookieLib;
use App\Libs\FuncoesLib;
use App\Libs\JwtLib;
use App\Libs\SessionLib;
use App\Libs\Twig\TwigLib;

/**
 * Esta classe é responsável por instanciar um model e chamar a view correta
 * passando os dados que serão usados.
 */
class ControllerCore
{

    private $arrayComponentTop = [];
    private $arrayComponentBottom = [];

    public function __construct()
    {
    }

    public function redirect(string $url, string $message = "")
    {
        if (!empty($message))
            (new AlertLib())->warning($message,$url);
        else
            header("location: " . $url);

        exit;

    }

    public function jsonParams(): array
    {
        // header('Content-type:application/json;charset=utf-8');
        return json_decode(file_get_contents('php://input'), true);
    }

    public function getParams($valor) {
        $return = $_GET[$valor]??null;
        if (is_string($return))
            return trim($return??'');
        if (is_array($return))
            return $return??[];
        if (!isset($valor))
            return $return;
    }

    public function getRouter(){
        return $_SERVER["REQUEST_URI"]??"/";
    }

    /**
     * @param $valor
     * @return array|string
     */
    public function postParams($valor){
        $return = $_POST[$valor]??null;
        if (is_string($return))
            return trim($return??'');
        if (is_array($return))
            return $return??[];
        if (!isset($valor))
            return $return;
    }

    /**
     * @param $valor
     * @return array|string
     */
    public function filesParams($valor){
        return $_FILES[$valor]??[];
    }

    public function validateRequestMethod($method = 'POST', $api = false)
    {
        if ($_SERVER['REQUEST_METHOD'] != $method) {
            if ($api) {
                $retorno['error'] = true;
                $retorno['msg'] = "Metodo incorreto";
                $this->response($retorno);
            } else {
                (new AlertLib())->warning("Método incorreto!", "/");
            }
        }
    }

    public function isModeDeveloper(){
        if (strpos($_SERVER['SERVER_NAME'],"localhost")){
            return true;
        } else {
            return false;
        }

    }

    public function getServicosFromModulo(){
        $moduloDao = new ModuloDao();
        $alertaDao = new AlertLib();
        $controller = explode("/", $_SERVER["REQUEST_URI"]);
        $modulo = $controller[1]??"";
        $id = SessionLib::getValue("CODUSUARIO");

        if (!empty($modulo)) {
            $servicos = $moduloDao->buscaServicosUsuario($id,$modulo);
            if (empty($servicos)){
                $alertaDao->danger("Sem privilégio de acesso!","./");
            } else {
                $data["TITLE"] = $servicos[0]["TITULOMODULO"];
                $data["TITLEIMAGE"] = $servicos[0]["ICONEMODULO"];
                $data["TITLEBREADCRUMB"] = "<li class='breadcrumb-item-custom'><a href='/'>Inicio</span></a><i class='mdi mdi-chevron-right mx-1' aria-hidden='true'></i></li><li class='breadcrumb-item-custom '><a href='/" . $modulo . "/'>" . $servicos[0]["TITULOMODULO"] . "</a></li>";
                $data["SERVICO"]["CONTROLLERMODULO"] = $servicos[0]["CONTROLLERMODULO"];
                $data["SERVICOS"] = $servicos;

                return $data;
            }
        } else {
            $alertaDao->danger("Sem privilégio de acesso!","/");
        }
    }

    public function getServico(){
        $sisModuloDao = new ModuloDao();
        $alertaDao = new AlertLib();

        if (empty(SessionLib::getValue("CODINSTITUICAO"))) {
            $this->redirect("/usuario/selecionainstituicao");
            exit();
        }

        $url = explode("/", $_SERVER["REQUEST_URI"]);
        $servicoParams = explode("-", $url[2]??"");
        $moduleUrl = $url[1]??"";
        $serviceUrl = $servicoParams[0]??"";
        $folderUrl = $servicoParams[1]??"";
        $codusuario = SessionLib::getValue("CODUSUARIO");
        $template = "{$moduleUrl}".($serviceUrl?"/{$serviceUrl}":"").($folderUrl?"/{$folderUrl}":"");

        if (empty($serviceUrl)) {
            $alertaDao->warning("Serviço não encontrado!","./");
            exit();
        }

        $servico = $sisModuloDao->buscaServicoUsuario($codusuario,$moduleUrl, $serviceUrl);
        if (empty($servico)) {
            $alertaDao->warning("Sem privilégio de acesso!", "/");
            exit();
        }

        $data["TITLE"] = $servico["TITULO"];
        $data["TITLEIMAGE"] = $servico["ICONE"];
        $data["TITLEBREADCRUMB"] = "<li class='breadcrumb-item-custom'><a href='/'>Inicio</span></a><i class='mdi mdi-chevron-right mx-1' aria-hidden='true'></i></li><li class='breadcrumb-item-custom '><a href='/" . $moduleUrl . "/'>" . $servico["TITULOMODULO"] . "</a></li><i class='mdi mdi-chevron-right mx-1' aria-hidden='true'></i></li><li class='breadcrumb-item-custom '><a href='/{$moduleUrl}/{$serviceUrl}/'>" . $servico["TITULO"] . "</a></li>";

        $data["SERVICO"] = $servico;
        $data["SERVICO"]["URL"] = "/{$moduleUrl}/{$serviceUrl}";
        $data["SERVICO"]["TEMPLATE"] = $template;
        $data["SERVICO"]["MODULO"] = $moduleUrl;
        $data["SERVICO"]["SERVICONOME"] = $url[2]??"";

        return $data;
    }

    /*public function isLogged(){
        $funcoes = new FuncoesLib();
        $cookie = new CookieLib();
        $usuarioDao = new UsuarioDao();
        $jwtTokenClass = new JwtLib();

        $codusuarioSessao = SessionLib::getValue("CODUSUARIO");
        $tokenuser = CookieLib::getValue("TOKEN_JWT");
        if (empty($codusuarioSessao)) {
            if (empty($tokenuser)) {
                SessionLib::setValue("REDIRECIONA", $funcoes->pegarUrlAtual());
                $this->redirect("/usuario/logoff");
                exit();
            } else {
                $dataToken = $jwtTokenClass->decode($tokenuser);
                if (!empty($dataToken)) {
                    $codusuarioCookie = $dataToken->data->id;
                    $usuarioResult = $usuarioDao->buscarCodusuario($codusuarioCookie);
                    $objInstituicao = (new InstituicaoDao())->buscarInstituicoesUsuario($usuarioResult->getCODPESSOA());
                    $token = $jwtTokenClass->encode(43200, ["id" => $usuarioResult->getCODUSUARIO()]);

                    if (count($objInstituicao??[]) == 0){
                        $this->redirect("/usuario/logoff");
                        exit();
                    }

                    CookieLib::setValue("TOKEN_JWT",$token);
                    SessionLib::setDataSession($usuarioResult);

                    if (count($objInstituicao??[]) == 1){
                        $usuarioResult->setCODINSTITUICAO($objInstituicao[0]->CODINSTITUICAO);
                    } else {
                        $this->redirect('/usuario/selecionainstituicao');
                        exit();
                    }

                    return true;
                } else {
                    SessionLib::setValue("REDIRECIONA", $funcoes->pegarUrlAtual());
                    $this->redirect("/usuario/logoff");
                    exit();
                }
            }
        } else {
            return true;
        }
    }*/

    public function isLogged(){
        $funcoes = new FuncoesLib();

        $codusuarioSessao = SessionLib::getValue("CODUSUARIO");
        if (empty($codusuarioSessao)) {
            SessionLib::setValue("REDIRECIONA", $funcoes->pegarUrlAtual());
            $this->redirect("/usuario/logoff");
            exit();
        }

        return true;
    }

    public function pageNotFound(){
        (new ErroController())->index();
    }

    /**
     * @param string $template
     * @param string $view
     * @param array $data
     * @param array $css
     * @param array $js
     * @return mixed
     */
    public function render(string $template, string $view = "", array $data = [], array $css = [], array $js = []) {
        if (file_exists(dirname(__DIR__, 3) . '/src/Libs/Template/' . ucfirst($template) . 'Template.php')) {
            $data["components"]["top"] = $this->arrayComponentTop;
            $data["components"]["bottom"] = $this->arrayComponentBottom;
            $template = "App\\Libs\\Template\\" . $template . "Template";
            $template = new $template;
            return $template->build($view, $data, $css, $js);
        } else {
            $template = "App\\Libs\\Template\\DefaultTemplate";
            $template = new $template;
            return $template->build('erro/index',['TITLE' => 'Página não encontrada']);
        }
    }

    public function renderComponent(string $component,  array $data = [], $print = false) {
        if (!file_exists(dirname(__DIR__, 3) . "/templates/{$component}")) {
            throw new \ErrorException("Component >>> /templates/{$component} <<< não encontrado!");
        }

        return (new TwigLib())->renderComponent($component, $data, $print);
    }

    /**
     * @param array $params
     * @return void
     */
    public function __call($name, $arguments)
    {
        $this->pageNotFound();
    }

    public function addComponentTop(string $component)
    {
        $this->arrayComponentTop[] = $component;
    }

    public function addComponentBottom(string $component)
    {
        $this->arrayComponentBottom[] = $component;
    }

    public function debug($value){
        if (CONFIG_DISPLAY_ERROR_DETAILS){
            echo "<pre>";
            print_r($value);
            echo "</pre>";
            exit();
        }
    }

    public function noCache()
    {
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    }


}
