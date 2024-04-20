<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class GpFuncionarioDependenteModel extends ModelAbstract
{
    Protected $CODPESSOA_DEPENDENTE;
    Protected $CODDEPENDENTE_TIPO;
    Protected $CODFUNCIONARIO;
    Protected $NOME;
    Protected $DATANASCIMENTO;
    Protected $CPF;
    Protected $CERTIDAO;
    Protected $IR;
    Protected $SF;
    Protected $PENSAO;
    Protected $EXCLUIDO;
    Protected $CRIADO_EM;
    Protected $ALTERADO_EM;
    
    public function getCODPESSOA_DEPENDENTE()
    {
        return $this->CODPESSOA_DEPENDENTE;
    }
    
    public function getCODDEPENDENTE_TIPO()
    {
        return $this->CODDEPENDENTE_TIPO;
    }
    
    public function getCODFUNCIONARIO()
    {
        return $this->CODFUNCIONARIO;
    }
    
    public function getNOME()
    {
        return $this->NOME;
    }
    
    public function getDATANASCIMENTO()
    {
        return $this->DATANASCIMENTO;
    }
    
    public function getCPF()
    {
        return $this->CPF;
    }
    
    public function getCERTIDAO()
    {
        return $this->CERTIDAO;
    }
    
    public function getIR()
    {
        return $this->IR;
    }
    
    public function getSF()
    {
        return $this->SF;
    }
    
    public function getPENSAO()
    {
        return $this->PENSAO;
    }
    
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }
    
    public function getCRIADO_EM()
    {
        return $this->CRIADO_EM;
    }
    
    public function getALTERADO_EM()
    {
        return $this->ALTERADO_EM;
    }
    
    
    public function setCODPESSOA_DEPENDENTE($CODPESSOA_DEPENDENTE):self
    {
        $this->CODPESSOA_DEPENDENTE = $CODPESSOA_DEPENDENTE;
        return $this;
    }
    
    public function setCODDEPENDENTE_TIPO($CODDEPENDENTE_TIPO):self
    {
        $this->CODDEPENDENTE_TIPO = $CODDEPENDENTE_TIPO;
        return $this;
    }
    
    public function setCODFUNCIONARIO($CODFUNCIONARIO):self
    {
        $this->CODFUNCIONARIO = $CODFUNCIONARIO;
        return $this;
    }
    
    public function setNOME($NOME):self
    {
        $this->NOME = $NOME;
        return $this;
    }
    
    public function setDATANASCIMENTO($DATANASCIMENTO):self
    {
        $this->DATANASCIMENTO = $DATANASCIMENTO;
        return $this;
    }
    
    public function setCPF($CPF):self
    {
        $this->CPF = $CPF;
        return $this;
    }
    
    public function setCERTIDAO($CERTIDAO):self
    {
        $this->CERTIDAO = $CERTIDAO;
        return $this;
    }
    
    public function setIR($IR):self
    {
        $this->IR = $IR;
        return $this;
    }
    
    public function setSF($SF):self
    {
        $this->SF = $SF;
        return $this;
    }
    
    public function setPENSAO($PENSAO):self
    {
        $this->PENSAO = $PENSAO;
        return $this;
    }
    
    public function setEXCLUIDO($EXCLUIDO):self
    {
        $this->EXCLUIDO = $EXCLUIDO;
        return $this;
    }
    
    public function setCRIADO_EM($CRIADO_EM):self
    {
        $this->CRIADO_EM = $CRIADO_EM;
        return $this;
    }
    
    public function setALTERADO_EM($ALTERADO_EM):self
    {
        $this->ALTERADO_EM = $ALTERADO_EM;
        return $this;
    }
    
    
}