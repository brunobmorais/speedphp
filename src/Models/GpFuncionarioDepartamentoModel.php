<?php
namespace App\Models;

use BMorais\Database\ModelAbstract;

class GpFuncionarioDepartamentoModel extends ModelAbstract
{
    Protected $CODFUNCIONARIO_DEPARTAMENTO;
    Protected $CODPESSOA_CADASTRO;
    Protected $CODDEPARTAMENTO;
    Protected $CODFUNCIONARIO;
    Protected $DATAINICIO;
    Protected $DATAFIM;
    Protected $EXCLUIDO;
    
    public function getCODFUNCIONARIO_DEPARTAMENTO()
    {
        return $this->CODFUNCIONARIO_DEPARTAMENTO;
    }
    
    public function getCODPESSOA_CADASTRO()
    {
        return $this->CODPESSOA_CADASTRO;
    }
    
    public function getCODDEPARTAMENTO()
    {
        return $this->CODDEPARTAMENTO;
    }
    
    public function getCODFUNCIONARIO()
    {
        return $this->CODFUNCIONARIO;
    }
    
    public function getDATAINICIO()
    {
        return $this->DATAINICIO;
    }
    
    public function getDATAFIM()
    {
        return $this->DATAFIM;
    }
    
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }
    
    
    public function setCODFUNCIONARIO_DEPARTAMENTO($CODFUNCIONARIO_DEPARTAMENTO):self
    {
        $this->CODFUNCIONARIO_DEPARTAMENTO = $CODFUNCIONARIO_DEPARTAMENTO;
        return $this;
    }
    
    public function setCODPESSOA_CADASTRO($CODPESSOA_CADASTRO):self
    {
        $this->CODPESSOA_CADASTRO = $CODPESSOA_CADASTRO;
        return $this;
    }
    
    public function setCODDEPARTAMENTO($CODDEPARTAMENTO):self
    {
        $this->CODDEPARTAMENTO = $CODDEPARTAMENTO;
        return $this;
    }
    
    public function setCODFUNCIONARIO($CODFUNCIONARIO):self
    {
        $this->CODFUNCIONARIO = $CODFUNCIONARIO;
        return $this;
    }
    
    public function setDATAINICIO($DATAINICIO):self
    {
        $this->DATAINICIO = $DATAINICIO;
        return $this;
    }
    
    public function setDATAFIM($DATAFIM):self
    {
        $this->DATAFIM = $DATAFIM;
        return $this;
    }
    
    public function setEXCLUIDO($EXCLUIDO):self
    {
        $this->EXCLUIDO = $EXCLUIDO;
        return $this;
    }
    
    
}
