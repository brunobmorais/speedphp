<?php

namespace App\Controllers;

use App\Core\Controller\ControllerCore;
use App\Libs\Form\FormLib;
use App\Libs\Template\TemplateAbstract;

class ExampleController extends ControllerCore
{
    public function index($args = [])
    {
        if (!$this->isModeDeveloper()) {
            $this->redirect("/");
        }

        $data["formComponent"] = (new FormLib())
            ->contatinerOpen()
            ->cardOpen()
            ->formOpen(["name"=>"formTeste", "method"=>"get", "action"=>"./"])
            ->rowOpen()
            ->inputLabel(["name"=>"NOME", "label"=>"Nome Completo", "classFormGroup"=>"col-12 col-md-6"])
            ->inputLabel(["name"=>"EMAIL", "label" => "Email", "type"=>"email", "classFormGroup"=>"col-12 col-md-6"])
            ->select(["name"=>"ESTADO", "label" => "Estado"], [], "")
            ->checkbox(["name"=>"SITUACAO", "label" => "Situacao", "classFormGroup" => "","descriptionRight"=>"Ativo"])
            ->divOpen("d-grid gap-2 col-6 mx-auto")
            ->button(["name"=>"SITUACAO", "label" => "Situacao","classButton"=>"btn-outline-secondary"])
            ->divClose()
            ->rowClose()
            ->include("components/div/endereco.html.twig",[])
            ->inputHidden(["name"=>"CODPESSOA", "value" => ""])
            ->buttonSubmit(["name"=>"buttonSalvar", "label" => "Salvar"])
            ->cardClose()
            ->contatinerClose()
            ->render();

        return $this->render(
            TemplateAbstract::LOGGED,
            "components/page",
            $data,
        );
    }
}