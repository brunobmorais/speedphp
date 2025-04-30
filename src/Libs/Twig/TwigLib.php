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
