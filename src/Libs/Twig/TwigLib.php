<?php

namespace App\Libs\Twig;

use App\Libs\Twig\TwigExtensionLib as LibsTwigExtension;
use Performing\TwigComponents\Configuration;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigLib
{
    private $viewDirectory = null;
    private $viewFile = null;
    private $viewUrl = null;
    private $dirName = null;

    public function __construct()
    {
        $this->dirName = dirname(__DIR__, 4);
    }

    public function render(string $view, $data = [], $print = true, $cache = false)
    {
        try {
            $loader = new FilesystemLoader($this->dirName . '/src/templates');
            if ($cache) {
                $twig = new Environment($loader, ['debug' => CONFIG_DISPLAY_ERROR_DETAILS, 'cache' => $this->dirName . '/templates/cache']);
            } else {
                $twig = new Environment($loader, ['debug' => CONFIG_DISPLAY_ERROR_DETAILS]);
            }

            $twig->addExtension(new LibsTwigExtension());

            $twig = TwigFunctionLib::getFunctions($twig);

            //componentes
            Configuration::make($twig)
                ->setTemplatesExtension('twig')
                ->useCustomTags()
                ->setup();

            $retorno = '';

            $varsDefault = [
                "URL" => CONFIG_URL,
            ];

            $data = array_merge($varsDefault, $data);

            $this->setView($view);

            $retorno .= $this->style();

            if ($this->viewFile === "index") {
                if (file_exists($this->dirName . "/src/templates/{$this->viewDirectory}/{$this->viewDirectory}.html.twig")) {
                    $retorno .= $twig->render("{$this->viewDirectory}/{$this->viewDirectory}.html.twig", $data);
                } else {
                    $retorno .= $twig->render("erro/erro.html.twig", $data);
                }
            } elseif (file_exists($this->dirName . "/src/templates/{$view}/{$this->viewFile}.html.twig")) {
                $retorno .= $twig->render("{$view}/{$this->viewFile}.html.twig", $data);
            } elseif (file_exists($this->dirName . "/src/templates/{$this->viewUrl}.html.twig")) {
                $directory = $this->dirName . "/src/templates/" . $this->viewUrl . ".html.twig";
                $retorno .= $twig->render($this->viewUrl . ".html.twig", $data);
            } else {
                $retorno .= $twig->render("erro/erro.html.twig", $data);
            }


            $retorno .= $this->javascript();

            if ($print) {
                echo $retorno;
                return true;
            } else {
                return $retorno;
            }
        } catch (\Error $e) {
            return $e;
        }
    }

    /**
     * @return string
     */
    protected function javascript()
    {

        $result = '';

        // CONTROLLER
        if (file_exists($this->dirName . "/src/templates/{$this->viewDirectory}/{$this->viewDirectory}.js")) {
            $result .= "<!--SCRIPT CONTROLLER-->\n";
            $result .= "<script>";
            $result .= file_get_contents($this->dirName."/src/templates/{$this->viewDirectory}/{$this->viewDirectory}.js");
            $result .= "</script>";
        }

        // VIEW
        if (file_exists($this->dirName . "/src/templates/{$this->viewUrl}/{$this->viewFile}.js")) {
            $result .= "<!--SCRIPT VIEW-->\n";
            $result .= "<script>";
            $result .= file_get_contents($this->dirName."/src/templates/{$this->viewUrl}/{$this->viewFile}.js");
            $result .= "</script>";
        }

        // SERVICE
        if (file_exists($this->dirName . "/src/templates/{$this->viewUrl}/{$this->viewFile}_service.js")) {
            $result .= "<!--SCRIPT SERVICE-->\n";
            $result .= "<script>";
            $result .= file_get_contents($this->dirName."/src/templates/{{$this->viewUrl}}/{$this->viewFile}_service.js");
            $result .= "</script>";
        }

        return $result;

    }





    private function addCss(string $url)
    {
        return "<link href='{$url}?v=" . CONFIG_VERSION_CODE . "' rel='stylesheet'>";
    }

    private function addJs(string $url)
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

    protected function style()
    {
        $retorno = "";

        if (file_exists($this->dirName . "/src/templates/{$this->viewDirectory}/{$this->viewDirectory}.css")) {
            $result = "<!--STYLE CONTROLER-->\n";
            $result .= "<style>";
            $result .= file_get_contents($this->dirName . "/src/templates/{$this->viewDirectory}/{$this->viewDirectory}.css");
            $result .= "</style>";
            $retorno = $result;
        }

        if (file_exists($this->dirName . "/src/templates/{$this->viewDirectory}/{$this->viewFile}/{$this->viewFile}.css")) {
            $result = "<!--STYLE VIEW-->\n";
            $result .= "<style>";
            $result .= file_get_contents($this->dirName . "/src/templates/{$this->viewDirectory}/{$this->viewFile}/{$this->viewFile}.css");
            $result .= "</style>";
            $retorno .= $result;
        }

        return $retorno;
    }

}
