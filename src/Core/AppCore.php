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
    private $controllersSemMetodo = CONFIG_FRAMEWORK["controller_without_method"];

    // Método construtor
    public function __construct()
    {
        $urlArray = $this->parseUrl();

        if (!empty($urlArray[0]) && $urlArray[0] === "api") {
            Api::run();
            return;
        }

        if (!empty($urlArray)) {
            if ($this->isValidController($urlArray[0])) {
                $this->getControllerFromUrl($urlArray);
                $this->getMethodOrParamsFromUrl($urlArray);
                $this->getParamsFromUrl($urlArray);
            } else {
                // Se não for um controlador válido, assume que é um método do HomeController
                $this->controller = "App\\Controllers\\".CONFIG_FRAMEWORK["controller_default"]."Controller";
                $this->controllerName = CONFIG_FRAMEWORK["controller_default"];
                $this->controller = new $this->controller();

                if (in_array(CONFIG_FRAMEWORK["controller_default"], $this->controllersSemMetodo)) {
                    $this->params = $urlArray;
                    $this->method = "index";
                } elseif (method_exists($this->controller, $urlArray[0])) {
                    $this->method = $urlArray[0];
                    $this->params = array_slice($urlArray, 1);
                } else {
                    // Se o método também não existir, redireciona para erro 404
                    $this->page404 = true;
                    $this->controller = new ErroController();
                    $this->method = 'index';
                }
            }
        } else {
            $this->controller = "App\\Controllers\\".CONFIG_FRAMEWORK["controller_default"]."Controller";
            $this->controller = new $this->controller();
            $this->method = 'index';
            $this->params = [];
        }

        $response = call_user_func_array([$this->controller, $this->method], [$this->params]);

        if (!empty($response)) {
            $this->handleError($response);
        }
    }

    private function isValidController($controller)
    {
        return file_exists(dirname(__DIR__, 2) . '/src/Controllers/' . ucfirst($controller) . 'Controller.php');
    }

    private function handleError(mixed $error): void
    {
        if (CONFIG_DISPLAY_ERROR_DETAILS) {
            throw new \ErrorException($error instanceof \Throwable ? $error->getMessage() : $error);
        } else {
            (new ErroController())->database();
        }
    }

    private function parseUrl()
    {
        $REQUEST_URI = explode('?', $_SERVER['REQUEST_URI']);
        $REQUEST_URI = explode('/', substr($REQUEST_URI[0], 1));
        return $REQUEST_URI;
    }

    private function getControllerFromUrl($url)
    {
        $this->controllerName = ucfirst($url[0]);
        $this->controller = 'App\\Controllers\\' . $this->controllerName . "Controller";
        $this->controller = new $this->controller();
    }

    private function getMethodOrParamsFromUrl($url)
    {
        if (in_array($this->controllerName, $this->controllersSemMetodo)) {
            $this->params = array_slice($url, 1);
            $this->method = 'index';
        } elseif (!empty($url[1]) && isset($url[1])) {
            $url[1] = str_replace("-", "", $url[1]);
            if (method_exists($this->controller, $url[1]) && !$this->page404) {
                $this->method = strtolower($url[1]);
            } else {
                $this->page404 = true;
                $this->controller = new ErroController();
                $this->method = 'index';
            }
        } else {
            $this->method = 'index';
        }
    }

    private function getParamsFromUrl($url)
    {
        $startIndex = in_array($this->controllerName, $this->controllersSemMetodo) ? 1 : 2;
        $this->params = array_slice($url, $startIndex);
    }
}
