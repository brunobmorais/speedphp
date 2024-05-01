<?php

namespace App\Libs\Template;

use App\Libs\CookieLib;
use App\Libs\JwtLib;
use App\Libs\LocalStorageClass;
use App\Libs\SessionLib;

class LoggedTemplate implements TemplateInterface
{

    use TemplateTrait;

    public function build(string $view = "", array $data = [], array $css = [], array $js = [])
    {
        try {
            $data["THEME"] = empty(CookieLib::getValue("theme")) ? '' : (CookieLib::getValue("theme") == "dark" ? 'data-bs-theme="dark" class="dark-mode"' : '');
            $this->setHead($data['TITLE'] ?? "");
            $data['SESSAO'] = SessionLib::getDataSession();
            $data['JWT'] = (new JwtLib())->encode();

            $data['head'] = $this->head();
            $data['navbar'] = $this->navbar($data);
            $data['title'] = $this->breadcrumb($data);
            $data['sidebar'] = $this->sidebar($data);
            $data['main'] = $this->render($view, $data, false);
            $data['main'] .= $this->servicesJS($data, false);
            //$data['menu'] = $this->navigationBottom($data['MENU'] ?? 0, $data); // ADICIONAR MENU NA PARTE DEBAIXO DA TELA NO MOBILE
            $data['footer'] = $this->footer();
            $data['javascript'] = $this->javascript($view);
            $data['css'] = $this->addCssJsPage($css, "css");
            $data['js'] = $this->addCssJsPage($js, "js");
            $this->render("components/theme", $data);

        } catch (\Error $e) {
            return $e;
        }
    }
}