<?php

namespace App\Core\Controller;

use App\Controllers\ErroController;
use App\Core\Template\TemplateInterface;
use App\Daos\ModuloDao;
use App\Daos\PessoaDao;
use App\Libs\AlertLib;
use App\Libs\CookieLib;
use App\Libs\FuncoesLib;
use App\Libs\JwtLib;
use App\Libs\SessionLib;

/**
 * Esta classe é responsável por instanciar um model e chamar a view correta
 * passando os dados que serão usados.
 */
class ControllerCore
{

    public function __construct()
    {
    }

    public function response($objArray = [], $responseCode = 200, $type="application/json" )
    {
        header_remove();
        header("Content-type:{$type};charset=utf-8");
        header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
        header("Pragma: no-cache"); //HTTP 1.0
        http_response_code($responseCode);
        if ($type==="application/json")
            echo json_encode($objArray, JSON_UNESCAPED_UNICODE);
        else
            echo $objArray;
        exit;
    }

    public function redirect(string $url, string $message = "")
    {
        if (!empty($message))
            (new AlertLib())->warning($message,$url);
        else
            header("location:" . $url);

        exit;

    }

    public function getJson(): array
    {
        // header('Content-type:application/json;charset=utf-8');
        return json_decode(file_get_contents('php://input'), true);
    }

    public function getParams($valor): string{
        return $_GET[$valor]??'';
    }

    public function postParams($valor): string{
        return $_POST[$valor]??'';
    }

    public function validateRequestMethod($method = 'POST', $api = false)
    {
        if ($_SERVER['REQUEST_METHOD'] != $method) {
            if ($api) {
                $retorno['error'] = true;
                $retorno['msg'] = "Metodo incorreto";
                $this->response($retorno);
            } else {
                $this->redirect('/');
            }
        }
    }

    public function validateRequestOrigem(){
        $permission_domains = CONFIG_SECURITY['permission_domains'];
        $request = $_SERVER['SERVER_NAME'];

        if (in_array($request, (array)$permission_domains)) {
            return true;
        } else {
            return false;
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

        $controller = explode("/", $_SERVER["REQUEST_URI"]);
        $moduloParams = $controller[1]??"";
        $servicoParams = explode("-", $controller[2]);
        $moduloUrl = $moduloParams??"";
        $servicoUrl = $servicoParams[0]??"";
        $codusuario = SessionLib::getValue("CODUSUARIO");
        if (!empty($servicoUrl)) {
            $servico = $sisModuloDao->buscaServicoUsuario($codusuario,$moduloUrl, $servicoUrl);
            if (empty($servico)){
                $alertaDao->danger("Sem privilégio de acesso!","/");
                exit();
            } else {
                $data["TITLE"] = $servico["TITULO"];
                $data["TITLEIMAGE"] = $servico["ICONE"];
                $data["TITLEBREADCRUMB"] = "<li class='breadcrumb-item-custom'><a href='/'>Inicio</span></a><i class='mdi mdi-chevron-right mx-1' aria-hidden='true'></i></li><li class='breadcrumb-item-custom '><a href='/" . $moduloUrl . "/'>" . $servico["TITULOMODULO"] . "</a></li><i class='mdi mdi-chevron-right mx-1' aria-hidden='true'></i></li><li class='breadcrumb-item-custom '><a href='/{$moduloUrl}/{$servicoUrl}/'>" . $servico["TITULO"] . "</a></li>";
                $data["SERVICO"] = $servico;

                $data["GETPARAMS"]["buscar"] = $this->getParams("buscar") ?: "";
                $data["GETPARAMS"]["pg"] = $this->getParams("pg") ?: "1";
                $data["SERVICO"]["url"] = "{$moduloUrl}/{$servicoUrl}";
                $data["SERVICO"]["modulo"] = $moduloUrl;
                $data["SERVICO"]["servico"] = $servico;


                return $data;
            }
        } else {
            $alertaDao->danger("Serviço não encontrado!","./");
            exit();
        }
    }

    public function isLogged(){
        $funcoes = new FuncoesLib();
        $cookie = new CookieLib();
        $usuarioDao = new PessoaDao();
        $jwtTokenClass = new JwtLib();

        $codusuarioSessao = SessionLib::getValue("CODUSUARIO");
        if (empty($codusuarioSessao)) {
            if (empty($tokenuser)) {
                SessionLib::setValue("REDIRECIONA", $funcoes->pegarUrlAtual());
                $this->redirect("/usuario/logoff");
                exit();
            } else {
                $dataToken = $jwtTokenClass->decode($tokenuser);
                if ($dataToken) {
                    $codusuarioCookie = $dataToken->data->id;
                    SessionLib::setDataSession($usuarioDao->buscarFuncionarioModelId($codusuarioCookie));
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
        if (file_exists(dirname(__DIR__, 3) . '/src/Core/Template/' . ucfirst($template) . 'Template.php')) {
            $template = "App\\Core\\Template\\" . $template . "Template";
            $template = new $template;
            return $template->build($view, $data, $css, $js);
        } else {
            $template = "App\\Core\\Template\\DefaultTemplate";
            $template = new $template;
            return $template->build('erro/index',['TITLE' => 'Página não encontrada']);
        }
    }

    /**
     * @param array $params
     * @return void
     */
    public function __call($name, $arguments)
    {
        $this->pageNotFound();
    }


}
