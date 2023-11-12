<?php

namespace App\Core\Template;

interface TemplateInterface
{
    public function render(string $view = "", array $data = [], array $css = [], array $js = []);

}