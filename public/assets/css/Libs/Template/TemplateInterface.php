<?php

namespace App\Libs\Template;

interface TemplateInterface
{
    public function build(string $view = "", array $data = [], array $css = [], array $js = []);

}