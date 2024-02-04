<?php

namespace App\Core\Template;

use App\Libs\JwtLib;
use App\Libs\SessionLib;

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