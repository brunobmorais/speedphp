<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class GpDepartamentoModel extends ModelAbstract
{
    Protected $CODDEPARTAMENTO;
    Protected $CODDEPARTAMENTOPAI;
    Protected $NOME;
    Protected $SIGLA;
    Protected $SITUACAO;
    Protected $EXCLUIDO;
    
    public function getCODDEPARTAMENTO()
    {
        return $this->CODDEPARTAMENTO;
    }
    
    public function getCODDEPARTAMENTOPAI()
    {
        return $this->CODDEPARTAMENTOPAI;
    }
    
    public function getNOME()
    {
        return $this->NOME;
    }
    
    public function getSIGLA()
    {
        return $this->SIGLA;
    }
    
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }
    
    
    public function setCODDEPARTAMENTO($CODDEPARTAMENTO):self
    {
        $this->CODDEPARTAMENTO = $CODDEPARTAMENTO;
        return $this;
    }
    
    public function setCODDEPARTAMENTOPAI($CODDEPARTAMENTOPAI):self
    {
        $this->CODDEPARTAMENTOPAI = $CODDEPARTAMENTOPAI;
        return $this;
    }
    
    public function setNOME($NOME):self
    {
        $this->NOME = $NOME;
        return $this;
    }
    
    public function setSIGLA($SIGLA):self
    {
        $this->SIGLA = $SIGLA;
        return $this;
    }
    
    public function setEXCLUIDO($EXCLUIDO):self
    {
        $this->EXCLUIDO = $EXCLUIDO;
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
     * @return GpDepartamentoModel
     */
    public function setSITUACAO($SITUACAO)
    {
        $this->SITUACAO = $SITUACAO;
        return $this;
    }


    
    
}