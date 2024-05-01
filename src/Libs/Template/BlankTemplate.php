<?php

namespace App\Libs\Template;

use App\Libs\CookieLib;
use App\Libs\JwtLib;
use App\Libs\SessionLib;

class BlankTemplate implements TemplateInterface
{

    use TemplateTrait;

    public function build(string $view = "", array $data = [], array $css = [], array $js = [])
    {
        try {
            $data["THEME"] = empty(CookieLib::getValue("theme")) ? '' : (CookieLib::getValue("theme") == "dark" ? 'data-bs-theme="dark" class="dark-mode"' : '');
            $this->setHead($head['TITLE'] ?? "");

            $data['SESSAO'] = SessionLib::getDataSession();
            $data['JWT'] = (new JwtLib())->encode();

            $data['head'] = $this->head();
            $data['main'] = $this->render($view, $data, false);
            $data['main'] .= $this->servicesJS($data, false);
            $data['footer'] = $this->footer();
            $data['javascript'] = $this->javascript($view);
            $data['css'] = $this->addCssJsPage($css, "css");
            $data['js'] = $this->addCssJsPage($js, "js");

            return $this->render("components/theme", $data);

        } catch (\Error $e) {
            return $e;
        }
    }
}