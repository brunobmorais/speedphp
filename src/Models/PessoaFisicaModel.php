<?php
namespace App\Models;

use BMorais\Database\ModelAbstract;

class PessoaFisicaModel extends ModelAbstract {


    protected $CODPESSOAFISICA;
    protected $CODPESSOA;
    protected $DATANASCIMENTO;
    protected $CPF;
    protected $SEXO;
    protected $EXCLUIDO;


    /**
     * Get the value of CODPESSOAFISICA
     */ 
    public function getCODPESSOAFISICA()
    {
        return $this->CODPESSOAFISICA;
    }

    /**
     * Set the value of CODPESSOAFISICA
     *
     * @return  self
     */ 
    public function setCODPESSOAFISICA($CODPESSOAFISICA)
    {
        $this->CODPESSOAFISICA = $CODPESSOAFISICA;

        return $this;
    }

    /**
     * Get the value of CODPESSOA
     */ 
    public function getCODPESSOA()
    {
        return $this->CODPESSOA;
    }

    /**
     * Set the value of CODPESSOA
     *
     * @return  self
     */ 
    public function setCODPESSOA($CODPESSOA)
    {
        $this->CODPESSOA = $CODPESSOA;

        return $this;
    }

    /**
     * Get the value of DATANASCIMENTO
     */ 
    public function getDATANASCIMENTO()
    {
        return $this->DATANASCIMENTO;
    }

    /**
     * Set the value of DATANASCIMENTO
     *
     * @return  self
     */ 
    public function setDATANASCIMENTO($DATANASCIMENTO)
    {
        $this->DATANASCIMENTO = $DATANASCIMENTO;

        return $this;
    }

    /**
     * Get the value of CPF
     */ 
    public function getCPF()
    {
        return $this->CPF;
    }

    /**
     * Set the value of CPF
     *
     * @return  self
     */ 
    public function setCPF($CPF)
    {
        $this->CPF = $CPF;

        return $this;
    }

    /**
     * Get the value of SEXO
     */ 
    public function getSEXO()
    {
        return $this->SEXO;
    }

    /**
     * Set the value of SEXO
     *
     * @return  self
     */ 
    public function setSEXO($SEXO)
    {
        $this->SEXO = $SEXO;

        return $this;
    }

    /**
     * Get the value of EXCLUIDO
     */ 
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }

    /**
     * Set the value of EXCLUIDO
     *
     * @return  self
     */ 
    public function setEXCLUIDO($EXCLUIDO)
    {
        $this->EXCLUIDO = $EXCLUIDO;

        return $this;
    }
}
