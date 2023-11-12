<?php

namespace App\Core\Template;

use App\Core\Template\TemplateInterface;
use App\Libs\JwtLib;
use App\Libs\SessionLib;

class DefaultTemplate implements TemplateInterface
{

    use TemplateTrait;

    public function build(string $view = "", array $data = [], array $css = [], array $js = [])
    {
        try {
            $this->setHead($head['TITLE'] ?? "");

            $data['SESSAO'] = SessionLib::getDataSession();
            $data['JWT'] = (new JwtLib())->encode();

            $data['head'] = $this->head();
            $data['main'] = $this->render($view, $data, false);
            $data['footer'] = $this->footer();
            $data['javascript'] = $this->javascript($view);
            $data['css'] = $this->addCssJsPage($css, "css");
            $data['js'] = $this->addCssJsPage($js, "js");

            return $this->build("components/theme", $data);

        } catch (\Error $e) {
            return $e;
        }
    }
}