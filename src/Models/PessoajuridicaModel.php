<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class PessoajuridicaModel extends ModelAbstract
{
    Protected $CODPESSOA_JURIDICA;
    Protected $CODPESSOA;
    Protected $CNPJ;
    Protected $NOMEFANTASIA;
    Protected $EXCLUIDO;
    
    public function getCODPESSOAJURIDICA()
    {
        return $this->CODPESSOA_JURIDICA;
    }
    
    public function getCODPESSOA()
    {
        return $this->CODPESSOA;
    }
    
    public function getCNPJ()
    {
        return $this->CNPJ;
    }
    
    public function getNOMEFANTASIA()
    {
        return $this->NOMEFANTASIA;
    }
    
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }
    
    
    public function setCODPESSOAJURIDICA($CODPESSOAJURIDICA):self
    {
        $this->CODPESSOA_JURIDICA = $CODPESSOAJURIDICA;
        return $this;
    }
    
    public function setCODPESSOA($CODPESSOA):self
    {
        $this->CODPESSOA = $CODPESSOA;
        return $this;
    }
    
    public function setCNPJ($CNPJ):self
    {
        $this->CNPJ = $CNPJ;
        return $this;
    }
    
    public function setNOMEFANTASIA($NOMEFANTASIA):self
    {
        $this->NOMEFANTASIA = $NOMEFANTASIA;
        return $this;
    }
    
    public function setEXCLUIDO($EXCLUIDO):self
    {
        $this->EXCLUIDO = $EXCLUIDO;
        return $this;
    }
    
    
}