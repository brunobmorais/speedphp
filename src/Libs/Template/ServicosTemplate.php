<?php

namespace App\Libs\Template;

use App\Libs\CookieLib;
use App\Libs\FuncoesLib;
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
            $view = 'components/pages/servicos';

            $data["THEME"] = empty(CookieLib::getValue("theme")) ? '' : (CookieLib::getValue("theme") == "dark" ? 'data-bs-theme="dark" class="dark-mode"' : '');
            SessionLib::setValue("TOKEN_JWT", (new JwtLib())->encode());

            $data["HEAD"]["title"] = !empty($data["HEAD"]['title']) ? CONFIG_HEADER['title']." › ".$data["HEAD"]['title']:CONFIG_HEADER['title'];
            $data["HEAD"]["url"] = (new FuncoesLib())->getCurrentUrlWithoutParameters();
            $data['head'] = $this->head($data);
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