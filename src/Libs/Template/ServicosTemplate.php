<?php

namespace App\Libs\Template;

use App\Libs\JwtLib;
use App\Libs\SessionLib;

class ServicosTemplate implements TemplateInterface
{

    use TemplateTrait;

    public function build(string $view = "", array $data = [], array $css = [], array $js = [])
    {
        try {
            $this->controller->isLogged();
            $data = $this->controller->getServicosFromModulo();
            $view = 'components/page_servicos';

            $this->setHead($data['TITLE'] ?? "");

            $data['SESSAO'] = SessionLib::getDataSession();
            $data['JWT'] = (new JwtLib())->encode();

            $data['head'] = $this->head();
            $data['navbar'] = $this->navbar($data);
            $data['title'] = $this->breadcrumb($data);
            $data['sidebar'] = $this->sidebar($data);
            $data['main'] = $this->render($view, $data, false);
            $data['main'] .= $this->servicesJS($data, false);
            // $data['menu'] = $this->navigationBottom($data['MENU'] ?? 0, $data); // ADICIONAR MENU NA PARTE DEBAIXO DA TELA NO MOBILE
            $data['footer'] = $this->footer();
            $data['javascript'] = $this->javascript($view);
            $this->render("components/theme", $data);

        } catch (\Error $e) {
            return $e;
        }
    }
}