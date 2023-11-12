<?php

namespace App\Core;

use App\Api\Api;
use App\Controllers\ErroController;

/**
 * Esta classe é responsável por obter da URL o controller, método (ação) e os parâmetros
 * e verificar a existência dos mesmo.
 */
class AppCore
{
    protected $controller = 'Home';
    protected $controllerName = 'Home';
    protected $method = 'index';
    protected $page404 = false;
    protected $params = [];
    private $funcaoSemMetodo = false;

    // Método construtor
    public function __construct()
    {
        try {
            $URL_ARRAY = $this->parseUrl();

            if (!empty($URL_ARRAY[0]) && isset($URL_ARRAY[0])) {
                if ($URL_ARRAY[0] === "api") {
                    Api::run();
                } else {
                    $this->getControllerFromUrl($URL_ARRAY);
                    $this->getMethodFromUrl($URL_ARRAY);
                    $this->getParamsFromUrl($URL_ARRAY);
                    // chama um método de uma classe passando os parâmetros
                    $response = call_user_func_array([$this->controller, $this->method], $this->params);
                }
            } else {
                $this->getControllerFromUrl($URL_ARRAY);
                $this->getMethodFromUrl($URL_ARRAY);
                $this->getParamsFromUrl($URL_ARRAY);
                // chama um método de uma classe passando os parâmetros
                $response = call_user_func_array([$this->controller, $this->method], $this->params);
            }

            if (!empty($response)) {
                $e = $response;
                $message = "<h4>Erro ao acessar essa página</h5><hr>";
                $message .= "<p><b>Arquivo:</b>  " . $e->getFile() . "<br/>";
                $message .= "<b>Linha:</b>  " . $e->getLine() . "<br/>";
                $message .= "<b>Mensagem:</b>  " . $e->getMessage() . "<br/></p>";

                if (CONFIG_DISPLAY_ERROR_DETAILS) {
                    echo $message;
                } else {
                    (new ErroController())->database();
                }
            }
        } catch (\Exception $e) {
            (new ErroController())->database();
        }
    }

    /**
     * Este método pega as informações da URL (após o dominio do site) e retorna esses dados
     *
     * @return array
     */
    private function parseUrl()
    {
        $REQUEST_URI = explode('?', $_SERVER['REQUEST_URI']);
        $REQUEST_URI = explode('/', substr($REQUEST_URI[0], 1));
        return str_replace("-","",$REQUEST_URI);
    }

    /**
     * Este método verifica se o array informado possui dados na psoição 0 (controlador)
     * caso exista, verifica se existe um arquivo com aquele nome no diretório Application/controllers
     * e instancia um objeto contido no arquivo, caso contrário a variável $page404 recebe true.
     *
     * @param array $url Array contendo informações ou não do controlador, método e parâmetros
     */
    private function getControllerFromUrl($url)
    {
        if (!empty($url[0]) && isset($url[0])) {
            if (file_exists(dirname(__DIR__, 2) . '/src/Controllers/' . ucfirst($url[0]) . 'Controller.php')) {
                $this->controller = ucfirst($url[0]);
                $this->controllerName = ucfirst($url[0]);
            } else {
                $this->funcaoSemMetodo = true;
                $this->controller = "Home";
                $this->controllerName = 'Home';
                $this->page404 = true;
            }
        }

        $this->controller = 'App\\Controllers\\' . $this->controller . "Controller";
        $this->controller = new $this->controller();

    }

    /**
     * Este método verifica se o array informado possui dados na psoição 1 (método)
     * caso exista, verifica se o método existe naquele determinado controlador
     * e atribui a variável $method da classe.
     *
     * @param array $url Array contendo informações ou não do controlador, método e parâmetros
     */
    private function getMethodFromUrl($url)
    {
        if (!empty($url[1]) && isset($url[1])) {
            $url[1] = str_replace("-","",$url[1]);
            if (method_exists($this->controller, $url[1]) && !$this->page404) {
                $this->method = strtolower($url[1]);
            } else {
                // caso a classe ou o método informado não exista, o método pageNotFound
                // do Controller é chamado.
                $this->page404 = true;
                $this->controller = 'App\\Controllers\\ErroController';
                $this->controller = new $this->controller();
                $this->method = 'index';
            }
        } else {
            $this->method = 'index';
        }
    }

    /**
     * Este método verifica se o array informador possui a quantidade de elementos maior que 2
     * ($url[0] é o controller e $url[1] o método/ação a executar), caso seja, é atrbuido
     * a variável $params da classe um novo array  apartir da posição 2 do $url
     *
     * @param array $url Array contendo informações ou não do controlador, método e parâmetros
     */
    private function getParamsFromUrl($url)
    {
        // VERIFICA SE É A PAGINA HOME COM O METODO INDEX
        if ($this->controllerName == 'Home' && $this->method == 'index') {
            for ($i = 0; $i < count($url); $i++) {
                $this->params[$i] = array_slice($url, $i);
            }
            return;
        }

        // VERIFICA SE É UM CONTROLER SEM METODO
        if ($this->funcaoSemMetodo) {
            for ($i = 1; $i < count($url); $i++) {
                if (count($url) > $i) {
                    $this->params[$i - 1] = array_slice($url, $i);
                }
            }
        } else {
            for ($i = 2; $i < count($url); $i++) {
                if (count($url) > $i) {
                    $this->params[$i - 2] = array_slice($url, $i);
                }
            }
        }
    }
}
