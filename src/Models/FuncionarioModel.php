<?php
namespace App\Models;

use App\Libs\FuncoesLib;
use App\Libs\JwtLib;

class FuncionarioModel {

    private $id;
    private $id_pessoa;
    private $id_funcao;
    private $senha;
    private $token;
    private $sts;
    private $dtcad;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return FuncionarioModel
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdPessoa()
    {
        return $this->id_pessoa;
    }

    /**
     * @param mixed $id_pessoa
     * @return FuncionarioModel
     */
    public function setIdPessoa($id_pessoa)
    {
        $this->id_pessoa = $id_pessoa;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdFuncao()
    {
        return $this->id_funcao;
    }

    /**
     * @param mixed $id_funcao
     * @return FuncionarioModel
     */
    public function setIdFuncao($id_funcao)
    {
        $this->id_funcao = $id_funcao;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSenha()
    {
        return $this->senha;
    }

    /**
     * @param mixed $senha
     * @return FuncionarioModel
     */
    public function setSenha($senha)
    {
        $this->senha = $senha;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     * @return FuncionarioModel
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSts()
    {
        return $this->sts;
    }

    /**
     * @param mixed $sts
     * @return FuncionarioModel
     */
    public function setSts($sts)
    {
        $this->sts = $sts;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDtcad()
    {
        return $this->dtcad;
    }

    /**
     * @param mixed $dtcad
     * @return FuncionarioModel
     */
    public function setDtcad($dtcad)
    {
        $this->dtcad = $dtcad;
        return $this;
    }


    public function fromMap($getParams = []){

        $funcoesClass = new FuncoesLib();
        $jwtToken = new JwtLib();

        $this->setid($getParams["id_funcionario"]??"")
            ->setIdPessoa($getParams["id_pessoa"]??"")
            ->setIdFuncao($getParams["id_funcao"]??"")
            ->setSenha($getParams["senha"]??$funcoesClass->geraSenha(10))
            ->setToken($getParams["token"]??$jwtToken->encode())
            ->setSts(empty($getParams["sts"])?"I":"A")
            ->setDtcad($funcoesClass->pegarDataAtualBanco());
    }


}
