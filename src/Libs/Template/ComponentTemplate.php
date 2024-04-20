<?php

namespace App\Libs\Template;

class ComponentTemplate implements TemplateInterface
{

    use TemplateTrait;

    public function build(string $view = "", array $data = [], array $css = [], array $js = [])
    {
        try {
            return $this->render($view, $data, false);

        } catch (\Error $e) {
            return $e;
        }
    }
}