<?php

namespace App\Libs\Template;

use App\Libs\JwtLib;
use App\Libs\SessionLib;

class NotLoggedTemplate implements TemplateInterface
{

    use TemplateTrait;

    public function build(string $view = "", array $data = [], array $css = [], array $js = [])
    {
        try {
            $this->setHead($data['TITLE'] ?? "");

            $data['JWT'] = (new JwtLib())->encode();
            $data['SESSAO'] = SessionLib::getDataSession();

            $data['head'] = $this->head();
            $data['navbar'] = $this->navbar($data);
            $data['title'] = $this->breadcrumb($data);
            $data['main'] = $this->render($view, $data, false);
            $data['main'] .= $this->servicesJS($data, false);
            // $data['menu'] = $this->navigationBottom($data['MENU'] ?? 0, $data); // ADICIONAR MENU NA PARTE DEBAIXO DA TELA NO MOBILE
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