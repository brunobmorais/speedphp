<?php

namespace App\Core;

use App\Api\Api;
use App\Controllers\ErroController;

/**
 * Esta classe é responsável por obter da URL o controlador, método e parâmetros
 * utilizando uma estrutura modular e escalável.
 */
class AppCore
{
    protected $controller;
    protected $controllerName;
    protected $modulePath = '';
    protected $method = 'index';
    protected $page404 = false;
    protected $params = [];
    private $debugString = "";
    private $controllersSemMetodo = CONFIG_FRAMEWORK["controller_without_method"];

    // Método construtor
    public function __construct()
    {
        $urlArray = $this->parseUrl();

        // Verificar se é uma chamada para API
        if (!empty($urlArray[0]) && $urlArray[0] === "api") {
            Api::run();
            return;
        }

        // Processar a URL para determinar o controlador, método e parâmetros
        $this->processUrl($urlArray);

        // Se um controlador foi encontrado, chamar o método com os parâmetros
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

    /**
     * Processa a URL para determinar controlador, método e parâmetros
     */
    protected function processUrl($urlArray)
    {
        if (empty($urlArray[0])) {
            // URL vazia, carregar controlador padrão
            $this->loadDefaultController();
            return;
        }

        $this->debug("Processando URL", $urlArray);

        // Primeiro tenta carregar o controlador específico usando o sistema legado
        $legacyResult = $this->tryLoadLegacyController($urlArray);

        if ($legacyResult === true) {
            // Controller legado encontrado com método válido
            $this->debug("Controller legado encontrado e carregado com método válido");
            return;
        }
        else if ($legacyResult === 'method_not_found') {
            // Controller legado encontrado, mas método não existe
            // Tenta encontrar no sistema modular se existe um submódulo correspondente
            $this->debug("Controller legado encontrado, mas método não existe. Tentando sistema modular");

            if (count($urlArray) > 1) {
                // Verifica se existe um submódulo correspondente no módulo
                $firstSegment = $urlArray[0];
                $secondSegment = $urlArray[1];

                // Verifica se existe módulo e submódulo correspondentes
                $modulePath = dirname(__DIR__, 2) . '/src/Modules/' . ucfirst($firstSegment);
                $subModulePath = $modulePath . '/' . ucfirst($secondSegment);

                if (is_dir($modulePath) && is_dir($subModulePath)) {
                    $this->debug("Encontrada estrutura modular correspondente", [
                        'Módulo' => $modulePath,
                        'Submódulo' => $subModulePath
                    ]);

                    // Tenta carregar o controller do submódulo
                    if ($this->tryLoadModularController($urlArray)) {
                        $this->debug("Controller modular de submódulo carregado com sucesso");
                        return;
                    }
                }
            }

            // Se chegou aqui, não encontrou um submódulo correspondente
            // Carregando página 404 porque o método não foi encontrado no legado
            $this->debug("Método não encontrado no legado e submódulo não existe. Carregando 404");
            $this->load404Controller();
            return;
        }

        // Se não encontrou usando o sistema legado, tenta o modular
        if ($this->tryLoadModularController($urlArray)) {
            $this->debug("Controller modular encontrado e carregado");
            return;
        }

        $this->loadDefaultController();
    }

    /**
     * Tenta carregar um controlador usando a estrutura modular
     */
    protected function tryLoadModularController($urlArray)
    {
        if (empty($urlArray)) {
            return false;
        }

        $currentPath = dirname(__DIR__, 2) . '/src/Modules';
        $validPath = [];
        $controllerClass = null;
        $lastValidController = null;
        $remainingSegments = $urlArray;

        $this->debug("Tentando encontrar o controlador mais específico", $urlArray);

        // Itera pelos segmentos para encontrar o controlador mais específico
        foreach ($urlArray as $index => $segment) {
            $folderSegment = ucfirst($segment);
            $currentPath .= '/' . $folderSegment;

            // Verifica se o segmento corresponde a uma pasta válida
            if (is_dir($currentPath)) {
                $validPath[] = $folderSegment;

                // Verifica se o controlador existe neste nível
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
                // Se o caminho deixa de ser válido, para a busca
                break;
            }
        }

        // Se encontramos um controlador válido, usamos o mais específico
        if ($lastValidController) {
            $this->controller = new $lastValidController['class']();
            $this->controllerName = $lastValidController['name'];
            $this->modulePath = implode('\\', array_slice($urlArray, 0, $lastValidController['index'] + 1));

            // Define os segmentos restantes como método e parâmetros
            $remainingSegments = array_slice($urlArray, $lastValidController['index'] + 1);
            $this->determineMethodAndParams($remainingSegments);

            $this->debug("Controller mais específico encontrado e carregado: " . $lastValidController['class'], [
                'Método' => $this->method,
                'Parâmetros' => $this->params
            ]);

            return true;
        }

        // Se nenhum controlador foi encontrado, retorna falso
        $this->debug("Nenhum controlador modular encontrado");
        return false;
    }
    /**
     * Tenta carregar um controlador usando a estrutura legada
     * @return bool|string Retorna true se encontrou controller e método, 'method_not_found' se encontrou
     * controller mas não encontrou o método, ou false se não encontrou o controller
     */
    protected function tryLoadLegacyController($urlArray)
    {
        // Verifica se o primeiro segmento é um controlador válido
        if ($this->isValidLegacyController($urlArray[0])) {
            $this->controllerName = ucfirst($urlArray[0]);
            $controllerClass = 'App\\Controllers\\' . $this->controllerName . 'Controller';
            $this->controller = new $controllerClass();

            // Determina o método e parâmetros
            if (in_array($this->controllerName, $this->controllersSemMetodo)) {
                $this->params = array_slice($urlArray, 1);
                $this->requestMethodIndex();
                return true;
            } else if (!empty($urlArray[1])) {
                $methodName = str_replace("-", "", $urlArray[1]);

                if (method_exists($this->controller, $methodName)) {
                    $this->method = strtolower($methodName);
                    $this->params = array_slice($urlArray, 2);
                    return true;
                } else {
                    // Controller encontrado, mas método não existe
                    $this->debug("Controller legado encontrado, mas método '$methodName' não existe");
                    return 'method_not_found';
                }
            } else {
                // Sem segmentos adicionais, use o método padrão
                $this->requestMethodIndex();
                $this->params = [];
                return true;
            }
        }

        // CORREÇÃO: Não verificar controlador padrão aqui
        return false;
    }

    /**
     * Verifica se um controlador legado existe
     */
    protected function isValidLegacyController($controller)
    {
        // Adicionar debug para verificar caminhos
        $basePath = dirname(__DIR__, 2);
        $controllerPath = $basePath . '/src/Controllers/' . ucfirst($controller) . 'Controller.php';

        $this->debug("Verificando controlador legado", [
            'Controller' => $controller,
            'Base Path' => $basePath,
            'Controller Path' => $controllerPath,
            'File Exists' => file_exists($controllerPath) ? 'true' : 'false'
        ]);

        return file_exists($controllerPath);
    }

    /**
     * Determina o método e parâmetros para controladores modulares
     */
    protected function determineMethodAndParams($remainingSegments)
    {
        if (empty($remainingSegments)) {
            // Sem mais segmentos, use o método padrão
            $this->requestMethodIndex();
            $this->params = [];
            return;
        }

        $methodName = $remainingSegments[0];
        $formattedMethodName = str_replace("-", "", $methodName);

        if (method_exists($this->controller, $formattedMethodName)) {
            $this->method = $formattedMethodName;
            $this->params = array_slice($remainingSegments, 1);
        } else {
            // Se o método não existe, use o método padrão e inclua todos os segmentos como parâmetros
            $this->requestMethodIndex();
            $this->params = $remainingSegments;
        }
    }

    /**
     * Carrega o controlador padrão
     */
    protected function loadDefaultController()
    {
        $defaultControllerName = CONFIG_FRAMEWORK["controller_default"];

        // Primeiro tenta o controlador padrão como legado
        if ($this->tryLoadDefaultLegacyController($defaultControllerName)) {
            return;
        }

        // Se não encontrou no sistema legado, tenta o modular
        if ($this->tryLoadDefaultModularController($defaultControllerName)) {
            return;
        }

        // Se não encontrou em nenhum lugar, exibe erro 404
        $this->debug("Controlador padrão não encontrado em nenhum lugar");
        $this->load404Controller();
    }

    /**
     * Tenta carregar o controlador padrão modular
     * @return bool Retorna true se encontrou e carregou o controlador
     */
    protected function tryLoadDefaultModularController($defaultControllerName)
    {
        $modulePath = dirname(__DIR__, 2) . '/src/Modules/' . $defaultControllerName;
        $controllerPath = $modulePath . '/' . $defaultControllerName . 'Controller.php';

        if (is_dir($modulePath) && file_exists($controllerPath)) {
            $controllerClass = 'App\\Modules\\' . $defaultControllerName . '\\' . $defaultControllerName . 'Controller';

            if (class_exists($controllerClass)) {
                $this->controller = new $controllerClass();
                $this->controllerName = $defaultControllerName . 'Controller';
                $this->modulePath = $defaultControllerName;
                $this->requestMethodIndex();
                $this->params = [];
                $this->debug("Controlador padrão modular carregado");
                return true;
            }
        }

        return false;
    }

    /**
     * Tenta carregar o controlador padrão legado
     * @return bool Retorna true se encontrou e carregou o controlador
     */
    protected function tryLoadDefaultLegacyController($defaultControllerName)
    {
        $controllerClass = "App\\Controllers\\" . $defaultControllerName . "Controller";

        if (class_exists($controllerClass)) {
            $this->controller = new $controllerClass();
            $this->controllerName = $defaultControllerName;
            $this->requestMethodIndex();
            $this->params = [];
            $this->debug("Controlador padrão legado carregado");
            return true;
        }

        return false;
    }

    /**
     * Carrega o controlador de erro 404
     */
    protected function load404Controller()
    {
        $this->page404 = true;
        $this->controller = new ErroController();
        $this->requestMethodIndex();
        $this->params = [];
    }

    /**
     * Processa a URL para obter os segmentos
     */
    protected function parseUrl()
    {
        $REQUEST_URI = explode('?', $_SERVER['REQUEST_URI']);
        $REQUEST_URI = explode('/', substr($REQUEST_URI[0], 1));
        // Filtra e preserva as chaves originais para manter a ordem
        $filteredArray = array_filter($REQUEST_URI, function($value) {
            return $value !== '';
        });
        // Reindexar o array para evitar índices não sequenciais
        return array_values($filteredArray);
    }

    /**
     * Trata erros de resposta
     */
    protected function handleError($error)
    {
        if (CONFIG_DISPLAY_ERROR_DETAILS) {
            throw new \ErrorException($error instanceof \Throwable ? $error->getMessage() : $error);
        } else {
            (new ErroController())->database();
        }
    }

    /**
     * Método auxiliar para depuração
     */
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

    /**
     * Função adicional para depuração avançada dos caminhos de controladores legados
     */
    protected function debugLegacyControllerPath($controller) {
        if (defined('DEBUG_ROUTER') && DEBUG_ROUTER === true) {
            $basePath = dirname(__DIR__, 2);
            $controllerPath = $basePath . '/src/Controllers/' . ucfirst($controller) . 'Controller.php';

            $this->debugString .= "<pre style='background:#ffe6e6; padding:10px; border:1px solid #ffcccc; margin:5px;'>";
            $this->debugString .= "<strong>DEBUG LEGACY PATH:</strong>\n";
            $this->debugString .= "Base Path: " . $basePath . "<br>";
            $this->debugString .= "Controller Path: " . $controllerPath . "<br>";
            $this->debugString .= "File Exists: " . (file_exists($controllerPath) ? 'true' : 'false') . "<br>";

            // Verificar se o diretório existe
            $controllerDir = $basePath . '/src/Controllers/';
            $this->debugString .= "Controller Directory: " . $controllerDir . "<br>";
            $this->debugString .= "Directory Exists: " . (is_dir($controllerDir) ? 'true' : 'false') . "<br>";

            // Listar arquivos no diretório
            if (is_dir($controllerDir)) {
                $this->debugString .= "Files in directory:<br>";
                $files = scandir($controllerDir);
                foreach ($files as $file) {
                    $this->debugString .= "- " . $file . "<br>";
                }
            }

            $this->debugString .= "</pre>";
        }
    }

    protected function requestMethodIndex() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET')
            $this->method = 'index';
        else
            $this->method = 'action';
    }
}