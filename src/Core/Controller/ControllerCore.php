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

    private array $servico = [];

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
        $return = $_GET[$valor]??"";
        if (is_string($return))
            return trim(htmlspecialchars($return??''));
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
        $return = $_POST[$valor]??"";
        if (is_string($return))
            return trim(htmlspecialchars($return??''));
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
        $serverName = $_SERVER['SERVER_NAME'] ?? '';

        // Verifica se termina com .localhost ou é exatamente localhost
        return $serverName === 'localhost'
            || str_ends_with($serverName, '.localhost')
            || $serverName === '127.0.0.1'
            || $serverName === '::1';
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
                $alertaDao->danger("Sem privilégio de acesso!","/");
            } else {
                $data["HEAD"]["title"] = $servicos[0]["TITULOMODULO"];

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

        $data["HEAD"]["title"] = $servico["TITULO"];

        $data["TITLE"] = $servico["TITULO"];
        $data["TITLEIMAGE"] = $servico["ICONE"];
        $data["TITLEBREADCRUMB"] = "<li class='breadcrumb-item-custom'><a href='/'>Inicio</span></a><i class='mdi mdi-chevron-right mx-1' aria-hidden='true'></i></li><li class='breadcrumb-item-custom '><a href='/" . $moduleUrl . "/'>" . $servico["TITULOMODULO"] . "</a></li><i class='mdi mdi-chevron-right mx-1' aria-hidden='true'></i></li><li class='breadcrumb-item-custom '><a href='/{$moduleUrl}/{$serviceUrl}/'>" . $servico["TITULO"] . "</a></li>";

        $data["SERVICO"] = $servico;
        $data["SERVICO"]["URL"] = "/{$moduleUrl}/{$serviceUrl}";
        $data["SERVICO"]["TEMPLATE"] = $template;
        $data["SERVICO"]["MODULO"] = $moduleUrl;
        $data["SERVICO"]["SERVICONOME"] = $url[2]??"";
        $this->servico = $data["SERVICO"];


        return $data;
    }

    public function getSession(): bool
    {
        $usuarioDao = new UsuarioDao();
        $jwtTokenClass = new JwtLib();

        // 1. Verifica se já existe código de usuário na sessão
        $codusuarioSessao = SessionLib::getValue("CODUSUARIO");
        if (!empty($codusuarioSessao)) {
            // Se já há um usuário logado na sessão, não precisamos refazer token/cookie
            return true;
        }

        // 2. Se não existe sessão, tenta recuperar do TOKEN no Cookie
        $tokenCookie = CookieLib::getValue("TOKEN");
        if (empty($tokenCookie)) {
            return false;
        }

        // 3. Decodifica o token JWT
        $dataToken = $jwtTokenClass->decode($tokenCookie);
        if (empty($dataToken) || empty($dataToken->data->id)) {
            // Se não foi possível decodificar corretamente ou não veio "id", token é inválido
            return false;
        }

        // 4. Busca o usuário no banco usando o "id" do token
        $codUsuarioCookie = $dataToken->data->id;
        $usuarioModel = $usuarioDao->buscarCodusuario($codUsuarioCookie);
        if (!$usuarioModel) {
            // Caso não encontre o usuário no banco, interrompe
            return false;
        }

        // 5. Gera um novo token JWT (por exemplo, com prazo de 43200 segundos = 12h)
        $novoToken = $jwtTokenClass->encode(43200, [
            "id" => $usuarioModel->getCODUSUARIO()
        ]);

        // 6. Atualiza o TOKEN no Cookie e configura sessão
        CookieLib::setValue("TOKEN", $novoToken, true);
        SessionLib::regenerate(); // ✅ Novo ID de sessão
        SessionLib::setDataSession($usuarioModel->getDataSession());

        return true;
    }

    public function isLogged(){
        $funcoes = new FuncoesLib();
        $cookie = new CookieLib();
        $usuarioDao = new UsuarioDao();
        $jwtTokenClass = new JwtLib();

        $codusuarioSessao = SessionLib::getValue("CODUSUARIO");
        $tokenCookie = CookieLib::getValue("TOKEN");
        if (empty($codusuarioSessao)) {
            if (empty($tokenCookie)) {
                SessionLib::setValue("REDIRECIONA", $funcoes->pegarUrlAtual());
                $this->redirect("/usuario/logoff");
                exit();
            } else {
                $dataToken = $jwtTokenClass->decode($tokenCookie);
                if (!empty($dataToken)) {
                    $codusuarioCookie = $dataToken->data->id;
                    $usuarioMOdel = $usuarioDao->buscarCodusuario($codusuarioCookie);

                    $token = $jwtTokenClass->encode(43200, ["id" => $usuarioMOdel->getCODUSUARIO()]);

                    CookieLib::setValue("TOKEN",$token, true);
                    SessionLib::regenerate(); // ✅ Novo ID de sessão
                    SessionLib::setDataSession($usuarioMOdel->getDataSession());

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
    }

    public function isLogged2(){
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
            return "ERRO COMPONENT >> {$component}";
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
            /*echo "<pre>";
            print_r($value);
            echo "</pre>";
            exit();*/
            dump($value); exit;

        }
    }

    public function noCache()
    {
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    }

    /**
     * @param $permissao string|array
     * @return bool
     * @string SALVAR, ALTERAR, EXCLUIR, LER, OUTROS
     */
    public function checkPermission($permissao)
    {
        if (gettype($permissao) != 'array') {
            return $this->servico[$permissao] == 1;

        }
        if(is_array($permissao)){

            $count = 0;
            foreach ($permissao as $chave => $valor) {
                if (isset($this->servico[$valor]) and $this->servico[$valor] == 1) {
                    $count++;
                }
            }
            return $count > 0  ;
        }

        return false;
    }

    public function returnPostAction($servico="", $id="") {
        $queryParams = [];
        if ($this->postParams("pg") !== "") $queryParams[] = "pg=" . urlencode($this->postParams("pg"));
        if ($this->postParams("b") !== "") $queryParams[] = "b=" . urlencode($this->postParams("b"));
        if ($this->postParams($id) !== "") $queryParams[] = $id."=" . urlencode($this->postParams($id));


        $returnParams = count($queryParams) > 0 ? "?" . implode("&", $queryParams) : "";
        if (!empty($servico))
            return "{$this->servico["URL"]}-{$servico}/" . $returnParams;
        else
            return "{$this->servico["URL"]}/" . $returnParams;
    }


}
