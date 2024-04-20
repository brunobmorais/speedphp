<?php

namespace App\Models;
use BMorais\Database\ModelAbstract;

class GpFuncionarioModel extends ModelAbstract
{

    protected $CODFUNCIONARIO;
    protected $CODPESSOA_CADASTRO;

    protected $CODPESSOA;
    protected $CODCATEGORIA;
    protected $NOMESOCIAL;

    protected $SITUACAO;
    protected $EXCLUIDO;
    protected $CRIADOEM;
    protected $ALTERADOEM;

    /**
     * @return mixed
     */
    public function getCODFUNCIONARIO()
    {
        return $this->CODFUNCIONARIO;
    }

    /**
     * @param mixed $CODFUNCIONARIO
     * @return GpFuncionarioModel
     */
    public function setCODFUNCIONARIO($CODFUNCIONARIO)
    {
        $this->CODFUNCIONARIO = $CODFUNCIONARIO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCODPESSOA_CADASTRO()
    {
        return $this->CODPESSOA_CADASTRO;
    }

    /**
     * @param mixed $CODPESSOA_CADASTRO
     * @return GpFuncionarioModel
     */
    public function setCODPESSOA_CADASTRO($CODPESSOA_CADASTRO)
    {
        $this->CODPESSOA_CADASTRO = $CODPESSOA_CADASTRO;
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
     * @return GpFuncionarioModel
     */
    public function setCODPESSOA($CODPESSOA)
    {
        $this->CODPESSOA = $CODPESSOA;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCODCATEGORIA()
    {
        return $this->CODCATEGORIA;
    }

    /**
     * @param mixed $CODCATEGORIA
     * @return GpFuncionarioModel
     */
    public function setCODCATEGORIA($CODCATEGORIA)
    {
        $this->CODCATEGORIA = $CODCATEGORIA;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNOMESOCIAL()
    {
        return $this->NOMESOCIAL;
    }

    /**
     * @param mixed $NOMESOCIAL
     * @return GpFuncionarioModel
     */
    public function setNOMESOCIAL($NOMESOCIAL)
    {
        $this->NOMESOCIAL = $NOMESOCIAL;
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
     * @return GpFuncionarioModel
     */
    public function setSITUACAO($SITUACAO)
    {
        $this->SITUACAO = $SITUACAO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }

    /**
     * @param mixed $EXCLUIDO
     * @return GpFuncionarioModel
     */
    public function setEXCLUIDO($EXCLUIDO)
    {
        $this->EXCLUIDO = $EXCLUIDO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCRIADOEM()
    {
        return $this->CRIADOEM;
    }

    /**
     * @param mixed $CRIADOEM
     * @return GpFuncionarioModel
     */
    public function setCRIADOEM($CRIADOEM)
    {
        $this->CRIADOEM = $CRIADOEM;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getALTERADOEM()
    {
        return $this->ALTERADOEM;
    }

    /**
     * @param mixed $ALTERADOEM
     * @return GpFuncionarioModel
     */
    public function setALTERADOEM($ALTERADOEM)
    {
        $this->ALTERADOEM = $ALTERADOEM;
        return $this;
    }
 



}
