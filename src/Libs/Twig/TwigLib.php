<?php

namespace App\Libs\Twig;

use App\Libs\FuncoesLib;
use App\Libs\Twig\TwigExtensionLib as LibsTwigExtension;
use Error;
use Performing\TwigComponents\Configuration;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\UX\TwigComponent\ComponentRenderer;
use Symfony\UX\TwigComponent\ComponentRendererInterface;
use Symfony\UX\TwigComponent\Twig\ComponentExtension;
use Twig\Environment;
use Twig\Extension\EscaperExtension;
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

            if (file_exists($this->dirName . "/templates/{$this->viewDirectory}/{$this->viewDirectory}.css")) {
                $result = "<!--STYLE CONTROLER-->\n";
                $result .= "<style>";
                $result .= file_get_contents($this->dirName . "/templates/{$this->viewDirectory}/{$this->viewDirectory}.css");
                $result .= "</style>";
                $retorno = $result;
            }

            if (file_exists($this->dirName . "/templates/{$this->viewDirectory}/{$this->viewFile}/{$this->viewFile}.css")) {
                $result = "<!--STYLE VIEW-->\n";
                $result .= "<style>";
                $result .= file_get_contents($this->dirName . "/templates/{$this->viewDirectory}/{$this->viewFile}/{$this->viewFile}.css");
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
                $directory = $this->dirName . "/templates/" . $this->viewUrl . ".html.twig";
                $retorno .= $twig->render($this->viewUrl . ".html.twig", $data);
            } else {
                $retorno .= $twig->render("erro/erro.html.twig", $data);
            }

            if ($print) {
                echo $retorno;
            } else {
                return $retorno;
            }
        } catch (Error $e) {
            return $e;
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
