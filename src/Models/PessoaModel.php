<?php
namespace App\Models;

use App\Libs\FuncoesLib;
use BMorais\Database\ModelAbstract;

class PessoaModel extends ModelAbstract {


    protected $CODUSUARIO;
    protected $CODPESSOA;
    protected $CPF;
    protected $NOME;
    protected $SENHA;
    protected $EMAIL;
    protected $SITUACAO;

    /**
     * @return mixed
     */
    public function getCODUSUARIO()
    {
        return $this->CODUSUARIO;
    }

    /**
     * @param mixed $CODUSUARIO
     * @return PessoaModel
     */
    public function setCODUSUARIO($CODUSUARIO)
    {
        $this->CODUSUARIO = $CODUSUARIO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCODPESSOA()
    {
        return $this->CODPESSOA;
    }

    /**
     * @param mixed $CODPESSOA
     * @return PessoaModel
     */
    public function setCODPESSOA($CODPESSOA)
    {
        $this->CODPESSOA = $CODPESSOA;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCPF()
    {
        return $this->CPF;
    }

    /**
     * @param mixed $CPF
     * @return PessoaModel
     */
    public function setCPF($CPF)
    {
        $this->CPF = $CPF;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNOME()
    {
        return $this->NOME;
    }

    /**
     * @param mixed $NOME
     * @return PessoaModel
     */
    public function setNOME($NOME)
    {
        $this->NOME = $NOME;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSENHA()
    {
        return $this->SENHA;
    }

    /**
     * @param mixed $SENHA
     * @return PessoaModel
     */
    public function setSENHA($SENHA)
    {
        $this->SENHA = $SENHA;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEMAIL()
    {
        return $this->EMAIL;
    }

    /**
     * @param mixed $EMAIL
     * @return PessoaModel
     */
    public function setEMAIL($EMAIL)
    {
        $this->EMAIL = $EMAIL;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSITUACAO()
    {
        return $this->SITUACAO;
    }

    /**
     * @param mixed $SITUACAO
     * @return PessoaModel
     */
    public function setSITUACAO($SITUACAO)
    {
        $this->SITUACAO = $SITUACAO;
        return $this;
    }


}
