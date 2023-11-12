<?php

namespace App\Libs;

use App\Core\PageCore;

class TemplateEmailLib
{

    private $titleEmail;
    private $subTitleEmail;
    private $descriptionEmail;
    private $buttonLinkEmail;
    private $buttonNameEmail;
    private $colorPrimaryEmail;
    private $urlEmail;
    private $nameFullEmail;

    public function __construct(){
    }


    public function basic($title=null, $subTitle=null, $description=null, $buttonLink=null, $buttonName=null):string
    {
        $this->titleEmail = $title;
        $this->subTitleEmail = $subTitle;
        $this->descriptionEmail = $description;
        $this->buttonLinkEmail = $buttonLink;
        $this->buttonNameEmail = $buttonName;

        $this->colorPrimaryEmail = CONFIG_SITE['color-primary'];
        $this->urlEmail = CONFIG_SITE['url'];
        $this->nameFullEmail = CONFIG_SITE['nameFull'];

        $body = "<p>{$this->subTitleEmail}</p>";
        $body .= $description;

        $link = "";
        if (!empty($buttonLink)) {
            $link = "<a href={$buttonLink}>{$buttonName}</a>";
        }

        $message = "<div style='font-family:Verdana'>".$body."<br><br>".$link."<br><br><br>Atenciosamente,<br>{$this->nameFullEmail}</div>";

        return $message;
    }

    public function template2($title=null, $subTitle=null, $description=null, $buttonLink=null, $buttonName=null):string
    {
        $this->titleEmail = $title;
        $this->subTitleEmail = $subTitle;
        $this->descriptionEmail = $description;
        $this->buttonLinkEmail = $buttonLink;
        $this->buttonNameEmail = $buttonName;

        $this->colorPrimaryEmail = CONFIG_SITE['color-primary'];
        $this->urlEmail = CONFIG_SITE['url'];
        $this->nameFullEmail = CONFIG_SITE['nameFull'];

        return (new PageCore())->render("components/email-2",[
            "titleEmail" => $title,
            "subTitleEmail" => $subTitle,
            "descriptionEmail" => $description,
            "nameFullEmail" => CONFIG_SITE['nameFull'],
            "nameEmail" => CONFIG_SITE['name'],
            "buttonEmailName" => $buttonName,
            "buttonEmailLink" => $buttonLink,
            "buttonEmailColor" => CONFIG_SITE['color-primary'],
            "urlEmail" => CONFIG_SITE['url']
        ],false);
    }

    public function template1($title=null, $subTitle=null, $description=null, $buttonLink=null, $buttonName=null):string
    {
        $this->titleEmail = $title;
        $this->subTitleEmail = $subTitle;
        $this->descriptionEmail = $description;
        $this->buttonLinkEmail = $buttonLink;
        $this->buttonNameEmail = $buttonName;

        $this->colorPrimaryEmail = CONFIG_SITE['color-primary'];
        $this->urlEmail = CONFIG_SITE['url'];
        $this->nameFullEmail = CONFIG_SITE['nameFull'];

        return (new PageCore())->render("components/email-1",[
            "titleEmail" => $title,
            "subTitleEmail" => $subTitle,
            "descriptionEmail" => $description,
            "nameFullEmail" => CONFIG_SITE['nameFull'],
            "nameEmail" => CONFIG_SITE['name'],
            "buttonEmailName" => $buttonName,
            "buttonEmailLink" => $buttonLink,
            "buttonEmailColor" => CONFIG_SITE['color-primary'],
            "urlEmail" => CONFIG_SITE['url']
        ],false);
    }
}