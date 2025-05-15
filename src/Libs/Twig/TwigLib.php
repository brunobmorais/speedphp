<?php

namespace App\Libs\Twig;

use App\Libs\Twig\TwigExtensionLib as LibsTwigExtension;
use Performing\TwigComponents\Configuration;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extension\StringLoaderExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\Loader\FilesystemLoader;

class TwigLib
{
    private $viewModule = null;
    private $viewService = null;
    private $viewFolder = null;
    private $viewFile = null;
    private $viewUrl = null;
    private $dirName = null;

    public function __construct()
    {
        $this->dirName = dirname(__DIR__, 3);
    }

    public function renderPage(string $view, $data = [], $print = true, $cache = false)
    {
        try {
            $loader = new FilesystemLoader($this->dirName . '/templates');
            if ($cache) {
                $twig = new Environment($loader, [
                    'debug' => CONFIG_DISPLAY_ERROR_DETAILS,
                    'cache' => $this->dirName . '/templates/cache'
                ]);
            } else {
                $twig = new Environment($loader, [
                    'debug' => CONFIG_DISPLAY_ERROR_DETAILS
                ]);
            }

            $twig->addExtension(new LibsTwigExtension());
            $twig->addExtension(new StringLoaderExtension());
            $twig->addExtension(new DebugExtension());
            $twig = TwigFunctionLib::getFunctions($twig);

            //componentes
            Configuration::make($twig)
                ->setTemplatesExtension('html.twig')
                ->useCustomTags()
                ->setup();

            $retorno = '';

            $varsDefault = [
                "URL" => CONFIG_URL,
            ];

            $data = array_merge($varsDefault, $data);

            $this->setView($view);

            // Dividir a URL da view em segmentos
            $pathSegments = explode('/', $this->viewUrl);
            $lastSegment = end($pathSegments); // Último segmento (arquivo)

            // ARQUIVOS CSS - Para cada nível do caminho
            for ($i = 0; $i < count($pathSegments); $i++) {
                // Construir o caminho até este nível
                $pathToLevel = implode('/', array_slice($pathSegments, 0, $i + 1));

                // Verificar se existe CSS para este nível
                // Padrão 1: caminho/até/nível/arquivo.css.twig
                if (file_exists($this->dirName . "/templates/{$pathToLevel}.css.twig")) {
                    $retorno .= "<!--STYLE LEVEL " . ($i + 1) . ": {$pathSegments[$i]}-->\n";
                    $retorno .= "<style>";
                    $retorno .= $twig->render("{$pathToLevel}.css.twig", $data);
                    $retorno .= "</style>";
                }

                // Padrão 2: caminho/até/nível/nível.css.twig
                $segmentName = $pathSegments[$i];
                $pathToFolder = implode('/', array_slice($pathSegments, 0, $i + 1));
                if (file_exists($this->dirName . "/templates/{$pathToFolder}/{$segmentName}.css.twig")) {
                    $retorno .= "<!--STYLE LEVEL " . ($i + 1) . ": {$pathSegments[$i]} (self)-->\n";
                    $retorno .= "<style>";
                    $retorno .= $twig->render("{$pathToFolder}/{$segmentName}.css.twig", $data);
                    $retorno .= "</style>";
                }
            }

            // ARQUIVO TWIG - Apenas o último nível
            $lastIndex = count($pathSegments) - 1;
            $lastSegmentName = $pathSegments[$lastIndex];
            $fullPath = $this->viewUrl;

            // Tenta os diferentes padrões de nome para o arquivo TWIG
            if (count($pathSegments) > 1 && $pathSegments[1] === "index") {
                // Caso especial para index
                if (file_exists($this->dirName . "/templates/{$pathSegments[0]}/{$pathSegments[0]}.html.twig")) {
                    $retorno .= $twig->render("{$pathSegments[0]}/{$pathSegments[0]}.html.twig", $data);
                } else {
                    $retorno .= $twig->render("erro/erro.html.twig", $data);
                }
            } elseif (file_exists($this->dirName . "/templates/{$fullPath}.html.twig")) {
                // Padrão 1: caminho/completo.html.twig
                $retorno .= $twig->render("{$fullPath}.html.twig", $data);
            } elseif (file_exists($this->dirName . "/templates/{$fullPath}/{$lastSegmentName}.html.twig")) {
                // Padrão 2: caminho/completo/último.html.twig
                $retorno .= $twig->render("{$fullPath}/{$lastSegmentName}.html.twig", $data);
            } else {
                // Tenta encontrar um arquivo com o mesmo nome do último segmento no caminho completo
                $pathToLastFolder = implode('/', array_slice($pathSegments, 0, $lastIndex + 1));
                if (file_exists($this->dirName . "/templates/{$pathToLastFolder}/{$lastSegmentName}.html.twig")) {
                    $retorno .= $twig->render("{$pathToLastFolder}/{$lastSegmentName}.html.twig", $data);
                } else {
                    // Fallback para página de erro
                    $retorno .= $twig->render("erro/erro.html.twig", $data);
                }
            }

            // ARQUIVOS JS - Para cada nível do caminho
            for ($i = 0; $i < count($pathSegments); $i++) {
                // Construir o caminho até este nível
                $pathToLevel = implode('/', array_slice($pathSegments, 0, $i + 1));

                // Verificar se existe JS para este nível
                // Padrão 1: caminho/até/nível.js.twig
                if (file_exists($this->dirName . "/templates/{$pathToLevel}.js.twig")) {
                    $retorno .= "<!--SCRIPT LEVEL " . ($i + 1) . ": {$pathSegments[$i]}-->\n";
                    $retorno .= "<script>";
                    $retorno .= $twig->render("{$pathToLevel}.js.twig", $data);
                    $retorno .= "</script>";
                }

                // Padrão 2: caminho/até/nível/nível.js.twig
                $segmentName = $pathSegments[$i];
                $pathToFolder = implode('/', array_slice($pathSegments, 0, $i + 1));
                if (file_exists($this->dirName . "/templates/{$pathToFolder}/{$segmentName}.js.twig")) {
                    $retorno .= "<!--SCRIPT LEVEL " . ($i + 1) . ": {$pathSegments[$i]} (self)-->\n";
                    $retorno .= "<script>";
                    $retorno .= $twig->render("{$pathToFolder}/{$segmentName}.js.twig", $data);
                    $retorno .= "</script>";
                }
            }

            if ($print) {
                echo $retorno;
            } else {
                return $retorno;
            }
        } catch (\Error $e) {
            return $e;
        }
    }

    public function renderPageChatgpt(string $view, $data = [], $print = true, $cache = false)
    {
        try {
            $loader = new FilesystemLoader($this->dirName . '/templates');
            if ($cache) {
                $twig = new Environment($loader, [
                    'debug' => CONFIG_DISPLAY_ERROR_DETAILS,
                    'cache' => $this->dirName . '/templates/cache'
                ]);
            } else {
                $twig = new Environment($loader, [
                    'debug' => CONFIG_DISPLAY_ERROR_DETAILS
                ]);
            }

            $twig->addExtension(new LibsTwigExtension());
            $twig->addExtension(new StringLoaderExtension());
            $twig->addExtension(new DebugExtension());
            $twig = TwigFunctionLib::getFunctions($twig);

            //componentes
            Configuration::make($twig)
                ->setTemplatesExtension('html.twig')
                ->useCustomTags()
                ->setup();

            $retorno = '';

            $varsDefault = [
                "URL" => CONFIG_URL,
            ];

            $data = array_merge($varsDefault, $data);

            $this->setView($view);

            // Dividir a URL da view em segmentos
            $pathSegments = explode('/', $this->viewUrl);
            $lastSegment = end($pathSegments); // Último segmento (arquivo)

            // ARQUIVOS CSS - Percorrer todos os níveis e carregar CSS
            $currentPath = '';
            $cssBasePath = '';

            // Primeiro nível (equivalente ao MODULE)
            if (!empty($pathSegments[0])) {
                $currentPath = $pathSegments[0];
                $cssBasePath = $currentPath;
                if (file_exists($this->dirName . "/templates/{$currentPath}/{$pathSegments[0]}.css.twig")) {
                    $retorno .= "<!--STYLE LEVEL 1: {$pathSegments[0]}-->\n";
                    $retorno .= "<style>";
                    $retorno .= $twig->render("{$currentPath}/{$pathSegments[0]}.css.twig", $data);
                    $retorno .= "</style>";
                }
            }

            // Níveis subsequentes
            for ($i = 1; $i < count($pathSegments); $i++) {
                $currentPath .= '/' . $pathSegments[$i];
                $parentPath = $cssBasePath;

                for ($j = 1; $j <= $i; $j++) {
                    $parentPath .= '/' . $pathSegments[$j];
                }

                // Verificar se o arquivo CSS existe neste nível
                $cssPath = "{$parentPath}.css.twig";
                if (file_exists($this->dirName . "/templates/{$cssPath}")) {
                    $retorno .= "<!--STYLE LEVEL " . ($i + 1) . ": {$pathSegments[$i]}-->\n";
                    $retorno .= "<style>";
                    $retorno .= $twig->render($cssPath, $data);
                    $retorno .= "</style>";
                }
            }

            // ARQUIVO TWIG - Apenas o último nível
            $lastItemPath = $this->viewUrl;

            if ($pathSegments[1] === "index") {
                // Caso especial para index
                if (file_exists($this->dirName . "/templates/{$pathSegments[0]}/{$pathSegments[0]}.html.twig")) {
                    $retorno .= $twig->render("{$pathSegments[0]}/{$pathSegments[0]}.html.twig", $data);
                } else {
                    $retorno .= $twig->render("erro/erro.html.twig", $data);
                }
            } elseif (file_exists($this->dirName . "/templates/{$lastItemPath}.html.twig")) {
                // Template com caminho completo
                $retorno .= $twig->render("{$lastItemPath}.html.twig", $data);
            } elseif (file_exists($this->dirName . "/templates/{$lastItemPath}/{$lastSegment}.html.twig")) {
                // Template na pasta com nome do arquivo
                $retorno .= $twig->render("{$lastItemPath}/{$lastSegment}.html.twig", $data);
            } else {
                // Fallback para página de erro
                $retorno .= $twig->render("erro/erro.html.twig", $data);
            }

            // ARQUIVOS JS - Percorrer todos os níveis e carregar JS
            $currentPath = '';
            $jsBasePath = '';

            // Primeiro nível (equivalente ao MODULE)
            if (!empty($pathSegments[0])) {
                $currentPath = $pathSegments[0];
                $jsBasePath = $currentPath;
                if (file_exists($this->dirName . "/templates/{$currentPath}/{$pathSegments[0]}.js.twig")) {
                    $retorno .= "<!--SCRIPT LEVEL 1: {$pathSegments[0]}-->\n";
                    $retorno .= "<script>";
                    $retorno .= $twig->render("{$currentPath}/{$pathSegments[0]}.js.twig", $data);
                    $retorno .= "</script>";
                }
            }

            // Níveis subsequentes
            for ($i = 1; $i < count($pathSegments); $i++) {
                $currentPath .= '/' . $pathSegments[$i];
                $parentPath = $jsBasePath;

                for ($j = 1; $j <= $i; $j++) {
                    $parentPath .= '/' . $pathSegments[$j];
                }

                // Verificar se o arquivo JS existe neste nível
                $jsPath = "{$parentPath}.js.twig";
                if (file_exists($this->dirName . "/templates/{$jsPath}")) {
                    $retorno .= "<!--SCRIPT LEVEL " . ($i + 1) . ": {$pathSegments[$i]}-->\n";
                    $retorno .= "<script>";
                    $retorno .= $twig->render($jsPath, $data);
                    $retorno .= "</script>";
                }
            }

            if ($print) {
                echo $retorno;
            } else {
                return $retorno;
            }
        } catch (\Error $e) {
            return $e;
        }
    }

    public function renderPageOriginal(string $view, $data = [], $print = true, $cache = false)
    {
        try {
            $loader = new FilesystemLoader($this->dirName . '/templates');
            if ($cache) {
                $twig = new Environment($loader, [
                    'debug' => CONFIG_DISPLAY_ERROR_DETAILS,
                    'cache' => $this->dirName . '/templates/cache'
                ]);
            } else {
                $twig = new Environment($loader, [
                    'debug' => CONFIG_DISPLAY_ERROR_DETAILS
                ]);
            }

            $twig->addExtension(new LibsTwigExtension());
            $twig->addExtension(new StringLoaderExtension());
            $twig->addExtension(new DebugExtension());
            $twig = TwigFunctionLib::getFunctions($twig);

            //componentes
            Configuration::make($twig)
                ->setTemplatesExtension('html.twig')
                ->useCustomTags()
                ->setup();

            $retorno = '';

            $varsDefault = [
                "URL" => CONFIG_URL,
            ];

            $data = array_merge($varsDefault, $data);

            $this->setView($view);

            // ARQUIVO CSS
            if (file_exists($this->dirName . "/templates/{$this->viewModule}/{$this->viewModule}.css.twig")) {
                $retorno .= "<!--STYLE MODULE-->\n";
                $retorno .= "<style>";
                $retorno .= $twig->render("{$this->viewModule}/{$this->viewModule}.css.twig", $data);
                $retorno .= "</style>";
            }
            if (file_exists($this->dirName . "/templates/{$this->viewModule}/{$this->viewService}/{$this->viewFile}.css.twig")) {
                $retorno .= "<!--STYLE SERVICE-->\n";
                $retorno .= "<style>";
                $retorno .= $twig->render("{$this->viewModule}/{$this->viewService}/{$this->viewFile}.css.twig", $data);
                $retorno .= "</style>";
            }
            if (file_exists($this->dirName . "/templates/{$this->viewModule}/{$this->viewService}/{$this->viewFolder}/{$this->viewFile}.css.twig")) {
                $retorno .= "<!--STYLE FOLDER-->\n";
                $retorno .= "<style>";
                $retorno .= $twig->render("{$this->viewModule}/{$this->viewService}/{$this->viewFolder}/{$this->viewFile}.css.twig", $data);
                $retorno .= "</style>";
            }

            // ARQUIVO TWIG
            if ($this->viewService === "index") {
                if (file_exists($this->dirName . "/templates/{$this->viewModule}/{$this->viewModule}.html.twig")) {
                    $retorno .= $twig->render("{$this->viewModule}/{$this->viewModule}.html.twig", $data);
                } else {
                    $retorno .= $twig->render("erro/erro.html.twig", $data);
                }
            } elseif (file_exists($this->dirName . "/templates/{$this->viewUrl}/{$this->viewFile}.html.twig")) {
                $retorno .= $twig->render("{$this->viewUrl}/{$this->viewFile}.html.twig", $data);
            } elseif (file_exists($this->dirName . "/templates/{$this->viewUrl}.html.twig")) {
                $retorno .= $twig->render($this->viewUrl . ".html.twig", $data);
            } else {
                $retorno .= $twig->render("erro/erro.html.twig", $data);
            }

            // ARQUIVO JS
            if (file_exists($this->dirName . "/templates/{$this->viewModule}/{$this->viewModule}.js.twig")) {
                $retorno .= "<!--SCRIPT MODULE-->\n";
                $retorno .= "<script>";
                $retorno .= $twig->render("{$this->viewModule}/{$this->viewModule}.js.twig", $data);
                $retorno .= "</script>";

            }
            if (file_exists($this->dirName . "/templates/{$this->viewModule}/{$this->viewService}/{$this->viewFile}.js.twig")) {
                $retorno .= "<!--SCRIPT SERVICE-->\n";
                $retorno .= "<script>";
                $retorno .= $twig->render("{$this->viewModule}/{$this->viewService}/{$this->viewFile}.js.twig", $data);
                $retorno .= "</script>";
            }
            if (file_exists($this->dirName . "/templates/{$this->viewModule}/{$this->viewService}/{$this->viewFolder}/{$this->viewFile}.js.twig")) {
                $retorno .= "<!--SCRIPT FOLDER -->\n";
                $retorno .= "<script>";
                $retorno .= $twig->render("{$this->viewModule}/{$this->viewService}/{$this->viewFolder}/{$this->viewFile}.js.twig", $data);
                $retorno .= "</script>";
            }


            if ($print) {
                echo $retorno;
            } else {
                return $retorno;
            }
        } catch (\Error $e) {
            return $e;
        }
    }

    public function servicesJS($data = [], $print = false)
    {
        $retorno = "";
        $retorno .= "<!--SERVICES-->\n";
        $types = array( 'twig');
        if ( $handle = opendir($this->dirName . "/templates/services") ) {
            while ( $entry = readdir( $handle )) {
                $ext = strtolower( pathinfo( $entry, PATHINFO_EXTENSION) );
                if( in_array( $ext, $types )) {
                    $retorno .= "<script>";
                    $retorno .= $this->renderComponent("services/{$entry}", $data, $print);
                    $retorno .= "</script>";
                }
            }
            closedir($handle);
        }

        if ($print) {
            echo $retorno;
        } else {
            return $retorno;
        }
    }

    public function renderComponent(string $view, $data = [], $print = true, $cache = false)
    {
        try {
            $loader = new FilesystemLoader($this->dirName . '/templates');
            if ($cache) {
                $twig = new Environment($loader, [
                    'debug' => CONFIG_DISPLAY_ERROR_DETAILS,
                    'cache' => $this->dirName . '/templates/cache'
                ]);
            } else {
                $twig = new Environment($loader, [
                    'debug' => CONFIG_DISPLAY_ERROR_DETAILS
                ]);
            }

            $twig->addExtension(new LibsTwigExtension());
            $twig->addExtension(new StringLoaderExtension());
            $twig = TwigFunctionLib::getFunctions($twig);

            //componentes
            Configuration::make($twig)
                ->setTemplatesExtension('html.twig')
                ->useCustomTags()
                ->setup();

            $retorno = '';

            $varsDefault = [
                "URL" => CONFIG_URL,
            ];

            $data = array_merge($varsDefault, $data);

            $this->setView($view);

            $retorno .= $twig->render($view, $data);

            if ($print) {
                echo $retorno;
            } else {
                return $retorno;
            }
        } catch (\Error $e) {
            return $e;
        }
    }


    protected function setView($url)
    {
        $controller = explode("/", $url);

        $this->viewUrl = $url;
        $this->viewModule = $controller[0];
        $this->viewService = $controller[1]??"";
        $this->viewFolder = $controller[2]??"";

        if ($this->viewService === "index") {
            $this->viewFile = $this->viewModule;
            return;
        }

        if (!empty($this->viewFolder)) {
            $this->viewFile = $this->viewFolder;
            return;
        }

        $this->viewFile = $this->viewService;

    }


}
