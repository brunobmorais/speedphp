<?php

namespace App\Libs\Form;

use App\Libs\Twig\TwigLib;

class FormLib
{
    private mixed $print;
    private $formHtml = "";
    private $formId = "";

    public function __construct($print = false)
    {
        $this->print = $print;
        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     * @example $form->formOpen(["id" => "","name" => "","action" => "", "method" => "", "onsubmit" => "", "attribute" => "", "add" => ""])
     * */
    public function formOpen(array $attributes): self
    {
        if (empty($attributes["name"]))
            return $this;
        $this->formId = $attributes["id"] ?? $attributes["name"];
        $id = $this->formId;
        $name = $attributes["name"]; // REQUIRED
        $action = $attributes["action"] ?? "";
        $method = $attributes["method"] ?? "POST";
        $onsubmit = $attributes["onsubmit"] ?? "";
        $add = $attributes["add"] ?? "";

        $this->formHtml .= "<form id='{$id}' name='{$name}' class='needs-validation' novalidate
            action='{$action}' method='{$method}'
          enctype='multipart/form-data' onsubmit='return {$onsubmit}' {$add}>";
        return $this;
    }

    /**
     * @param $class
     * @return $this
     * @example $form->rowOpen()
     */
    public function rowOpen($class = "")
    {
        $this->formHtml .= "<div class='row {$class}'>";
        return $this;
    }

    /**
     * @return $this
     * @example $form->rowClose()
     * */
    public function rowClose()
    {
        $this->formHtml .= "</div>";
        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     * @example $form->inputLabel(["id" => "","name" => "","label" => "", "type" => "", "classInput" => "", "required" => "", "value" => "", "placeholder" => "", "add" => ""])
     * */
    public function inputLabel(array $attributes): self
    {
        if (empty($attributes["name"]))
            return $this;
        $classFormGroup = $attributes["classFormGroup"] ?? "";
        $name = $attributes["name"] ?? "";
        $id = $attributes["id"] ?? $attributes["name"] . "_" . $this->formId;
        $label = $attributes["label"] ?? $attributes["name"];
        $type = $attributes["type"] ?? "text";
        $classInput = $attributes["classInput"] ?? "";
        $required = $attributes["required"] ?? "required";

        $value = $attributes["value"] ?? "";
        $add = $attributes["add"] ?? "";
        $placeholder = $attributes["placeholdeer"] ?? "";

        $this->formHtml .= "<div class='form-group {$classFormGroup}'>
                <label class='form-label' for='{$id}'>{$label}</label>
                <input name='{$name}' id='{$id}' type='{$type}' class='form-control {$classInput}' {$required} placeholder='{$placeholder}' value='{$value}' $add/>
                <div class='invalid-feedback'>
                    Preencha o campo corretamente
                </div>
            </div>";

        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     * @example $form->inputHidden(["id" => "","name" => "", "value" => "", "add" => ""])
     * */
    public function inputHidden(array $attributes): self
    {
        if (empty($attributes["name"]))
            return $this;
        $name = $attributes["name"] ?? "";
        $id = $attributes["id"] ?? $attributes["name"] . "_" . $this->formId;
        $value = $attributes["value"] ?? "";
        $add = $attributes["value"] ?? "";

        $this->formHtml .= "<input type='hidden' name='{$name}' id='{$id}' value='{$value}' {$add}/>";

        return $this;
    }

    public function select(array $attributes, $data = [], $selected = "")
    {
        if (empty($attributes["name"]))
            return $this;
        $classFormGroup = $attributes["classFormGroup"] ?? "col";
        $name = $attributes["name"] ?? "";
        $id = $attributes["id"] ?? $attributes["name"] . "_" . $this->formId;
        $label = $attributes["label"] ?? $attributes["name"];
        $classSelect = $attributes["classSelect"] ?? "";
        $required = $attributes["required"] ?? "required";
        $multiple = $attributes["multiple"] ?? "";
        $add = $attributes["add"] ?? "";

        $this->formHtml .= "<div class='form-group {$classFormGroup}'>
            <label class='form-label' for='{$id}'>{$label}</label>
            <select name='{$name}' id='{$id}' class='form-control select2 {$classSelect}' {$required} {$multiple} {$add}>
		    <option value='' disabled selected>Selecione</option>";
        foreach ($data as $value => $item) {
            $this->formHtml .= "<option value='{$value}' " . (($value) == $selected ? 'selected' : '') . ">{$item}</option>";
        }
        $this->formHtml .= "</select>
            <div class='invalid-feedback'>
                Preencha o campo corretamente
            </div>
        </div>";

        return $this;

    }

    private function formClose(): self
    {
        $this->formHtml .= "</form>";
        return $this;
    }

    public function render()
    {
        $this->formClose();
        if ($this->print) {
            echo $this->formHtml;
            return true;
        }

        return $this->formHtml;

    }

    public function buttonSubmit(array $attributes, array $data = [])
    {
        if (empty($attributes["name"]))
            return $this;
        $name = $attributes["name"] ?? "";
        $id = $attributes["id"] ?? $attributes["name"] . "_" . $this->formId;
        $label = $attributes["label"] ?? $attributes["name"];
        $type = $attributes["type"] ?? "submit";
        $classButton = $attributes["classButton"] ?? "";
        $classDiv = $attributes["classDiv"] ?? "col";
        $idCadastro = $attributes["required"] ?? "required";
        $nameCadastro = $attributes["required"] ?? "required";

        if (($data["SERVICO"]["EXCLUIR"] ?? "1") == "1")
            $buttonExcluir = "<button type='button' onclick='excluirItemTabela(`{$idCadastro}`)' class='btn btn-outline-secondary btn-round btn-lg col-md-3 col-lg-2 col-12 mt-2'><span class='mdi mdi-trash-can-outline'></span> Excluir</button>";
        if (($data["SERVICO"]["ALTERAR"] ?? "1") == "1" or ($data["SERVICO"]["SALVAR"] ?? "1") == "1")
            $buttonSalvar = "<button name='{$name}' type='{$type}' id='{$id}' class='btn btn-lg btn-primary btn-round col-md-3 col-lg-2 col-12 float-end mt-2 {$classButton}'> <span class='mdi mdi-check'></span> {$label}</button>";
        $this->formHtml .= "<div class='row'>
            <div class='{$classDiv}'>
                <input type='hidden' name='pg' value='GETPARAMS.pg'>
                <input type='hidden' name='buscar' value='GETPARAMS.buscar'>
                {$buttonExcluir}
                {$buttonSalvar}
            </div>
        </div>";

        return $this;
    }

    public function checkbox(array $attributes)
    {
        $classFormGroup = $attributes["classFormGroup"] ?? "col";
        $name = $attributes["name"] ?? "";
        $id = $attributes["id"] ?? $attributes["name"] . "_" . $this->formId;
        $label = $attributes["label"] ?? $attributes["name"];
        $checked = $attributes["checked"] ?? "checked";
        $value = $attributes["value"] ?? "";
        $add = $attributes["add"] ?? "";
        $descriptionLeft = !empty($attributes["descriptionLeft"]) ? "<span class='custom-switch-description-left'>{$attributes["descriptionLeft"]}</span>" : "";
        $descriptionRight = !empty($attributes["descriptionRight"]) ? "<span class='custom-switch-description-right'>{$attributes["descriptionRight"]}</span>" : "";

        $this->formHtml .= "{$this->formGroupOpen($classFormGroup)}
                                        <div class='custom-switch-label'>{$label}</div>
                                        <label class='custom-switch mt-2' style='padding-left: 0px' for='{$id}'>
                                            <input type='checkbox' name='{$name}' id='{$id}' class='custom-switch-input' {$checked} value='{$value}' {$add}>
                                            {$descriptionLeft}
                                            <span class='custom-switch-indicator'></span>
                                            {$descriptionRight}
                                        </label>
                                    {$this->formGroupClose()}";
        return $this;
    }

    public function button(array $attributes)
    {
        $name = $attributes["name"] ?? "";
        $id = $attributes["id"] ?? $attributes["name"] . "_" . $this->formId;
        $label = $attributes["label"] ?? $attributes["name"];
        $type = $attributes["type"] ?? "button";
        $classButton = $attributes["classButton"] ?? "btn-primary";
        $add = $attributes["add"] ?? "";

        $this->formHtml .= "<button type='{$type}' id='{$id}' name='{$name}' class='btn btn-lg btn-round {$classButton}' {$add}>{$label}</button>";
        return $this;
    }

    public function divOpen(string $class = "")
    {
        $this->formHtml .= "<div class='{$class}'>";
        return $this;
    }

    public function divClose()
    {
        $this->formHtml .= "</div>";
        return $this;
    }


    private function formGroupOpen(string $class = "")
    {
        return "<div class='form-group {$class}'>";
    }

    private function formGroupClose()
    {
        return "</div>";
    }

    public function include($view, $data)
    {
        $this->formHtml .= (new TwigLib())->renderComponent($view, $data, false);
        return $this;
    }

    public function cardOpen($titleCard = "", $class = "")
    {
        $add = "";
        if (!empty($titleCard))
            $add = "<div class='card-header'><h4>{$titleCard}</h4></div>";

        $this->formHtml .= "<div class='card shadow-1 {$class}'>
            {$add}
            <div class='card-body'>
                <div class='col p-2'>";
        return $this;
    }

    public function cardClose()
    {
        $this->formHtml .= "</div></div></div>";
        return $this;
    }

    public function contatinerOpen($class = "")
    {
        $this->formHtml .= "<main><section class='section'><div class='container my-container {$class}'>";
        return $this;
    }

    public function contatinerClose()
    {
        $this->formHtml .= "</div></section></main>";
        return $this;
    }


}