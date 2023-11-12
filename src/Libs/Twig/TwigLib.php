<?php

namespace App\Libs\twig;

use App\Components\NavbarComponents;
use App\Core\View;
use App\Lib\AlertaLib;
use App\Lib\FuncoesLib;
use App\Libs\twig\TwigExtensionLib as LibsTwigExtension;
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
        $this->dirName = dirname(__DIR__, 3);
    }

    public function render(string $view, $data = [], $print = true, $cache = false)
    {
        $loader = new FilesystemLoader($this->dirName.'/templates');
        if ($cache) {
            $twig = new Environment($loader, ['debug' => CONFIG_DISPLAY_ERROR_DETAILS, 'cache' => $this->dirName . '/templates/cache']);
        } else {
            $twig = new Environment($loader, ['debug' => CONFIG_DISPLAY_ERROR_DETAILS]);
        }

        $twig->addExtension(new LibsTwigExtension());

        $twig = TwigFunctionLib::getFunctions($twig);

        $retorno = '';

        $varsDefault = [
            "URL" => CONFIG_URL,
        ];

        $data = array_merge($varsDefault, $data);

        $this->setView($view);

        if (file_exists($this->dirName . "/templates/{$this->viewDirectory}/{$this->viewDirectory}.css")) {
            $result = "<!--STYLE CONTROLER-->\n";
            $result .= "<style>";
            $result .= file_get_contents($this->dirName."/templates/{$this->viewDirectory}/{$this->viewDirectory}.css");
            $result .= "</style>";
            $retorno = $result;
        }

        if (file_exists($this->dirName . "/templates/{$this->viewDirectory}/{$this->viewFile}/{$this->viewFile}.css")) {
            $result = "<!--STYLE VIEW-->\n";
            $result .= "<style>";
            $result .= file_get_contents($this->dirName."/templates/{$this->viewDirectory}/{$this->viewFile}/{$this->viewFile}.css");
            $result .= "</style>";
            $retorno .= $result;
        }

        if ($this->viewFile === "index") {
            if (file_exists($this->dirName . "/templates/{$this->viewDirectory}/{$this->viewDirectory}.html.twig")) {
                $retorno .= $twig->render("{$this->viewDirectory}/{$this->viewDirectory}.html.twig", $data);
            } else {
                $retorno .= $twig->render("erro/erro.html.twig", $data);
            }
        } elseif (file_exists($this->dirName . "/templates/{$view}/{$this->viewFile}.html.twig")) {
            $retorno .= $twig->render("{$view}/{$this->viewFile}.html.twig", $data);
        } elseif (file_exists($this->dirName . "/templates/{$this->viewUrl}.html.twig")) {
            $directory = $this->dirName . "/templates/".$this->viewUrl.".html.twig";
            $retorno .= $twig->render($this->viewUrl.".html.twig", $data);
        } else {
            $retorno .= $twig->render("erro/erro.html.twig", $data);
        }

        if ($print) {
            echo $retorno;
        } else {
            return $retorno;
        }
    }

    protected function setView($url)
    {
        $controller = explode("/", $url);

        $this->viewUrl = $url;
        $this->viewDirectory = $controller[0];
        $this->viewFile = $controller[1];
    }



}
