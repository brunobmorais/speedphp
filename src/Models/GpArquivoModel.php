<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class GpArquivoModel extends ModelAbstract
{
    Protected $CODARQUIVO;
    Protected $CODPESSOA_CADASTRO;
    Protected $CODFUNCIONARIO;
    Protected $CODTIPOARQUIVO;
    Protected $ARQUIVO;
    Protected $EXCLUIDO;
    Protected $CRIADO_EM;
    Protected $ALTERADO_EM;
    
    public function getCODARQUIVO()
    {
        return $this->CODARQUIVO;
    }
    
    public function getCODPESSOACADASTRO()
    {
        return $this->CODPESSOA_CADASTRO;
    }
    
    public function getCODFUNCIONARIO()
    {
        return $this->CODFUNCIONARIO;
    }
    
    public function getCODTIPOARQUIVO()
    {
        return $this->CODTIPOARQUIVO;
    }
    
    public function getARQUIVO()
    {
        return $this->ARQUIVO;
    }
    
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }
    
    public function getCRIADOEM()
    {
        return $this->CRIADO_EM;
    }
    
    public function getALTERADOEM()
    {
        return $this->ALTERADO_EM;
    }
    
    
    public function setCODARQUIVO($CODARQUIVO):self
    {
        $this->CODARQUIVO = $CODARQUIVO;
        return $this;
    }
    
    public function setCODPESSOA_CADASTRO($CODPESSOA_CADASTRO):self
    {
        $this->CODPESSOA_CADASTRO = $CODPESSOA_CADASTRO;
        return $this;
    }
    
    public function setCODFUNCIONARIO($CODFUNCIONARIO):self
    {
        $this->CODFUNCIONARIO = $CODFUNCIONARIO;
        return $this;
    }
    
    public function setCODTIPOARQUIVO($CODTIPOARQUIVO):self
    {
        $this->CODTIPOARQUIVO = $CODTIPOARQUIVO;
        return $this;
    }
    
    public function setARQUIVO($ARQUIVO):self
    {
        $this->ARQUIVO = $ARQUIVO;
        return $this;
    }
    
    public function setEXCLUIDO($EXCLUIDO):self
    {
        $this->EXCLUIDO = $EXCLUIDO;
        return $this;
    }
    
    public function setCRIADOEM($CRIADOEM):self
    {
        $this->CRIADO_EM = $CRIADOEM;
        return $this;
    }
    
    public function setALTERADOEM($ALTERADOEM):self
    {
        $this->ALTERADO_EM = $ALTERADOEM;
        return $this;
    }
    
    
}