<?php

namespace App\Libs\Template;

use App\Libs\FuncoesLib;

class ComponentTemplate implements TemplateInterface
{

    use TemplateTrait;

    public function build(string $view = "", array $data = [], array $css = [], array $js = [])
    {
        try {
            $this->controller->getSession();

            $data["HEAD"]["title"] = !empty($data["HEAD"]['title']) ? CONFIG_HEADER['title']." › ".$data["HEAD"]['title']:CONFIG_HEADER['title'];
            $data["HEAD"]["url"] = (new FuncoesLib())->getCurrentUrlWithoutParameters();
            $data['head'] = $this->head($data);
            $data['main'] = $this->render($view, $data, false);
            $data['main'] .= $this->servicesJS($data, false);
            $data['footer'] = $this->footer();
            $data['javascript'] = $this->javascript($view);
            $data['css'] = $this->addCssJsPage($css, "css");
            $data['js'] = $this->addCssJsPage($js, "js");
            $this->render("components/theme", $data, false);

        } catch (\Error $e) {
            return $e;
        }
    }
}