<?php

namespace App\Core;

use App\Api\Api;
use App\Controllers\ErroController;

/**
 * Classe principal de roteamento da aplicação.
 * Interpreta a URL e carrega o controlador, método e parâmetros adequados.
 */
class AppCore
{
    protected $controller;
    protected $controllerName;
    protected $modulePath = '';
    protected $method = 'index';
    protected $page404 = false;
    protected $params = [];
    private $debugString = '';
    private $controllersSemMetodo = CONFIG_FRAMEWORK["controller_without_method"];

    public function __construct()
    {
        $urlArray = $this->parseUrl();

        if (!empty($urlArray[0]) && $urlArray[0] === 'api') {
            Api::run();
            return;
        }

        $this->processUrl($urlArray);

        if (!empty($this->debugString)) {
            echo $this->debugString;
            exit();
        }

        if ($this->controller) {
            $response = call_user_func_array([$this->controller, $this->method], [$this->params]);

            if (!empty($response)) {
                $this->handleError($response);
            }
        }
    }

    protected function processUrl($urlArray)
    {
        if (empty($urlArray[0])) {
            $this->loadDefaultController();
            return;
        }

        $this->debug("Processando URL", $urlArray);

        $legacyResult = $this->tryLoadLegacyController($urlArray);

        if ($legacyResult === true) {
            $this->debug("Controller legado carregado com sucesso");
            return;
        } elseif ($legacyResult === 'method_not_found') {
            $this->debug("Método não encontrado no controller legado. Tentando submódulo...");

            if (count($urlArray) > 1) {
                $firstSegment = $urlArray[0];
                $secondSegment = $urlArray[1];
                $modulePath = dirname(__DIR__, 2) . '/src/Modules/' . ucfirst($firstSegment);
                $subModulePath = $modulePath . '/' . ucfirst($secondSegment);

                if (is_dir($modulePath) && is_dir($subModulePath)) {
                    $this->debug("Submódulo encontrado", [
                        'Módulo' => $modulePath,
                        'Submódulo' => $subModulePath
                    ]);

                    if ($this->tryLoadModularController($urlArray)) {
                        return;
                    }
                }
            }

            $this->debug("404: método não encontrado nem submódulo existente");
            $this->load404Controller();
            return;
        }

        if ($this->tryLoadModularController($urlArray)) {
            return;
        }

        $this->loadDefaultController();
    }

    protected function tryLoadLegacyController($urlArray)
    {
        if ($this->isValidLegacyController($urlArray[0])) {
            $this->controllerName = ucfirst($urlArray[0]);
            $controllerClass = 'App\\Controllers\\' . $this->controllerName . 'Controller';
            $this->controller = new $controllerClass();

            if (in_array($this->controllerName, $this->controllersSemMetodo)) {
                $this->params = array_slice($urlArray, 1);
                $this->requestMethodIndex();
                return true;
            } elseif (!empty($urlArray[1])) {
                $methodName = str_replace('-', '', $urlArray[1]);

                if (method_exists($this->controller, $methodName)) {
                    $this->method = strtolower($methodName);
                    $this->params = array_slice($urlArray, 2);
                    return true;
                } else {
                    $this->debug("Método '$methodName' não existe no controller legado");
                    return 'method_not_found';
                }
            } else {
                $this->requestMethodIndex();
                $this->params = [];
                return true;
            }
        }

        return false;
    }

    protected function tryLoadModularController($urlArray)
    {
        $currentPath = dirname(__DIR__, 2) . '/src/Modules';
        $validPath = [];
        $lastValidController = null;

        foreach ($urlArray as $index => $segment) {
            $folderSegment = ucfirst($segment);
            $currentPath .= '/' . $folderSegment;

            if (is_dir($currentPath)) {
                $validPath[] = $folderSegment;
                $controllerPath = $currentPath . '/' . $folderSegment . 'Controller.php';

                if (file_exists($controllerPath)) {
                    $controllerClass = 'App\\Modules\\' . implode('\\', $validPath) . '\\' . $folderSegment . 'Controller';

                    if (class_exists($controllerClass)) {
                        $lastValidController = [
                            'class' => $controllerClass,
                            'name' => $folderSegment . 'Controller',
                            'index' => $index
                        ];
                    }
                }
            } else {
                break;
            }
        }

        if ($lastValidController) {
            $this->controller = new $lastValidController['class']();
            $this->controllerName = $lastValidController['name'];
            $this->modulePath = implode('\\', array_slice($urlArray, 0, $lastValidController['index'] + 1));
            $remainingSegments = array_slice($urlArray, $lastValidController['index'] + 1);
            $this->determineMethodAndParams($remainingSegments);

            $this->debug("Controller modular carregado: " . $lastValidController['class'], [
                'Método' => $this->method,
                'Parâmetros' => $this->params
            ]);
            return true;
        }

        $this->debug("Nenhum controller modular encontrado");
        return false;
    }

    protected function isValidLegacyController($controller)
    {
        $controllerPath = dirname(__DIR__, 2) . '/src/Controllers/' . ucfirst($controller) . 'Controller.php';

        $this->debug("Verificando controlador legado", [
            'Controller' => $controller,
            'Path' => $controllerPath,
            'Existe' => file_exists($controllerPath) ? 'sim' : 'não'
        ]);

        return file_exists($controllerPath);
    }

    protected function determineMethodAndParams($segments)
    {
        if (empty($segments)) {
            $this->requestMethodIndex();
            $this->params = [];
            return;
        }

        $methodName = str_replace('-', '', $segments[0]);

        if (method_exists($this->controller, $methodName)) {
            $this->method = $methodName;
            $this->params = array_slice($segments, 1);
        } else {
            $this->requestMethodIndex();
            $this->params = $segments;
        }
    }

    protected function loadDefaultController()
    {
        $default = CONFIG_FRAMEWORK["controller_default"];

        if ($this->tryLoadDefaultLegacyController($default)) return;
        if ($this->tryLoadDefaultModularController($default)) return;

        $this->debug("Controlador padrão não encontrado");
        $this->load404Controller();
    }

    protected function tryLoadDefaultLegacyController($default)
    {
        $controllerClass = "App\\Controllers\\" . $default . "Controller";

        if (class_exists($controllerClass)) {
            $this->controller = new $controllerClass();
            $this->controllerName = $default;
            $this->requestMethodIndex();
            $this->params = [];
            $this->debug("Controlador padrão legado carregado");
            return true;
        }

        return false;
    }

    protected function tryLoadDefaultModularController($default)
    {
        $modulePath = dirname(__DIR__, 2) . '/src/Modules/' . $default;
        $controllerPath = $modulePath . '/' . $default . 'Controller.php';

        if (is_dir($modulePath) && file_exists($controllerPath)) {
            $controllerClass = 'App\\Modules\\' . $default . '\\' . $default . 'Controller';

            if (class_exists($controllerClass)) {
                $this->controller = new $controllerClass();
                $this->controllerName = $default . 'Controller';
                $this->modulePath = $default;
                $this->requestMethodIndex();
                $this->params = [];
                $this->debug("Controlador padrão modular carregado");
                return true;
            }
        }

        return false;
    }

    protected function load404Controller()
    {
        $this->page404 = true;
        $this->controller = new ErroController();
        $this->requestMethodIndex();
        $this->params = [];
    }

    protected function handleError($error)
    {
        if (CONFIG_DISPLAY_ERROR_DETAILS) {
            throw new \ErrorException($error instanceof \Throwable ? $error->getMessage() : $error);
        } else {
            (new ErroController())->database();
        }
    }

    protected function parseUrl()
    {
        $url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
        $url = explode('?', $url)[0];
        $segments = array_filter(explode('/', trim($url, '/')), fn($v) => $v !== '');
        return array_values($segments);
    }

    protected function requestMethodIndex()
    {
        $this->method = ($_SERVER['REQUEST_METHOD'] === 'GET') ? 'index' : 'action';
    }

    protected function debug($message, $data = null)
    {
        if (defined('DEBUG_ROUTER') && DEBUG_ROUTER === true) {
            $this->debugString .= "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd; margin:5px;'>";
            $this->debugString .= "<strong>DEBUG:</strong> " . $message . "\n";

            if ($data !== null) {
                $this->debugString .= "Data: ";
                $this->debugString .= print_r($data, true);
            }

            $this->debugString .= "</pre>";
        }
    }
}