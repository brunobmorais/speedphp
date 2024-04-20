<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class GpFeriasModel extends ModelAbstract
{
    Protected $CODFERIAS;
    Protected $CODPESSOA_CADASTRO;
    Protected $CODFUNCIONARIO_CARGO;
    Protected $DATAINICIO;
    Protected $DATAFIM;
    Protected $ANOREFERENCIA;
    Protected $EXCLUIDO;
    
    public function getCODFERIAS()
    {
        return $this->CODFERIAS;
    }
    
    public function getCODPESSOACADASTRO()
    {
        return $this->CODPESSOA_CADASTRO;
    }
    
    public function getCODFUNCIONARIOCARGO()
    {
        return $this->CODFUNCIONARIO_CARGO;
    }
    
    public function getDATAINICIO()
    {
        return $this->DATAINICIO;
    }
    
    public function getDATAFIM()
    {
        return $this->DATAFIM;
    }
    
    public function getANOREFERENCIA()
    {
        return $this->ANOREFERENCIA;
    }
    
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }
    
    
    public function setCODFERIAS($CODFERIAS):self
    {
        $this->CODFERIAS = $CODFERIAS;
        return $this;
    }
    
    public function setCODPESSOA_CADASTRO($CODPESSOA_CADASTRO):self
    {
        $this->CODPESSOA_CADASTRO = $CODPESSOA_CADASTRO;
        return $this;
    }
    
    public function setCODFUNCIONARIOCARGO($CODFUNCIONARIOCARGO):self
    {
        $this->CODFUNCIONARIO_CARGO = $CODFUNCIONARIOCARGO;
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
    
    public function setANOREFERENCIA($ANOREFERENCIA):self
    {
        $this->ANOREFERENCIA = $ANOREFERENCIA;
        return $this;
    }
    
    public function setEXCLUIDO($EXCLUIDO):self
    {
        $this->EXCLUIDO = $EXCLUIDO;
        return $this;
    }
    
    
}