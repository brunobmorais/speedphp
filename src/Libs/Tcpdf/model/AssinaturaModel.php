<?php

namespace App\Libs\Tcpdf\model;

class AssinaturaModel
{
    private string $quemAssina;
    private string $arquivopdf;
    private string $token;
    private string $dataAssinatura;

    private string $urlValidacao;

    public function __construct()
    {
        $this->setQuemAssina("");
        return $this;
    }

    public function getQuemAssina(): string
    {
        return $this->quemAssina;
    }

    public function setQuemAssina(string $quemAssina): AssinaturaModel
    {
        $this->quemAssina = $quemAssina;
        return $this;
    }

    public function getArquivopdf(): string
    {
        return $this->arquivopdf;
    }

    public function setArquivopdf(string $arquivopdf): AssinaturaModel
    {
        $this->arquivopdf = $arquivopdf;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): AssinaturaModel
    {
        $this->token = $token;
        return $this;
    }

    public function getDataAssinatura(): string
    {
        return $this->dataAssinatura;
    }

    public function setDataAssinatura(string $dataAssinatura): AssinaturaModel
    {
        $this->dataAssinatura = $dataAssinatura;
        return $this;
    }

    public function getUrlValidacao(): string
    {
        return $this->urlValidacao;
    }

    public function setUrlValidacao(string $urlValidacao): AssinaturaModel
    {
        $this->urlValidacao = $urlValidacao;
        return $this;
    }





}