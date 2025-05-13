<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class PessoaModel extends ModelAbstract
{
    Protected $CODPESSOA;
    Protected $CODENDERECO;
    Protected $IMAGEM;
    Protected $TIPOPESSOA;
    Protected $NOME;
    Protected $TELEFONE;
    Protected $EMAIL;
    Protected $EXCLUIDO;
    Protected $CRIADO_EM;
    Protected $ALTERADO_EM;
    
    public function getCODPESSOA()
    {
        return $this->CODPESSOA;
    }
    
    public function getCODENDERECO()
    {
        return $this->CODENDERECO;
    }
    
    public function getIMAGEM()
    {
        return $this->IMAGEM;
    }
    
    public function getTIPOPESSOA()
    {
        return $this->TIPOPESSOA;
    }
    
    public function getNOME()
    {
        return $this->NOME;
    }
    
    public function getTELEFONE()
    {
        return $this->TELEFONE;
    }
    
    public function getEMAIL()
    {
        return $this->EMAIL;
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
    
    
    public function setCODPESSOA($CODPESSOA):self
    {
        $this->CODPESSOA = $CODPESSOA;
        return $this;
    }
    
    public function setCODENDERECO($CODENDERECO):self
    {
        $this->CODENDERECO = $CODENDERECO;
        return $this;
    }
    
    public function setIMAGEM($IMAGEM):self
    {
        $this->IMAGEM = $IMAGEM;
        return $this;
    }
    
    public function setTIPOPESSOA($TIPOPESSOA):self
    {
        $this->TIPOPESSOA = $TIPOPESSOA;
        return $this;
    }
    
    public function setNOME($NOME):self
    {
        $this->NOME = $NOME;
        return $this;
    }
    
    public function setTELEFONE($TELEFONE):self
    {
        $this->TELEFONE = $TELEFONE;
        return $this;
    }
    
    public function setEMAIL($EMAIL):self
    {
        $this->EMAIL = $EMAIL;
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