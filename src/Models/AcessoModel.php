<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class AcessoModel extends ModelAbstract
{
    Protected $CODACESSO;
    Protected $CODEVENTO;
    Protected $CODPESSOA;
    Protected $LOCAL;
    Protected $IP;
    Protected $CRIADO_EM;
    
    public function getCODACESSO()
    {
        return $this->CODACESSO;
    }
    
    public function getCODEVENTO()
    {
        return $this->CODEVENTO;
    }
    
    public function getCODPESSOA()
    {
        return $this->CODPESSOA;
    }
    
    public function getLOCAL()
    {
        return $this->LOCAL;
    }
    
    public function getIP()
    {
        return $this->IP;
    }
    
    public function getCRIADO_EM()
    {
        return $this->CRIADO_EM;
    }
    
    
    public function setCODACESSO($CODACESSO):self
    {
        $this->CODACESSO = $CODACESSO;
        return $this;
    }
    
    public function setCODEVENTO($CODEVENTO):self
    {
        $this->CODEVENTO = $CODEVENTO;
        return $this;
    }
    
    public function setCODPESSOA($CODPESSOA):self
    {
        $this->CODPESSOA = $CODPESSOA;
        return $this;
    }
    
    public function setLOCAL($LOCAL):self
    {
        $this->LOCAL = $LOCAL;
        return $this;
    }
    
    public function setIP($IP):self
    {
        $this->IP = $IP;
        return $this;
    }
    
    public function setCRIADO_EM($CRIADO_EM):self
    {
        $this->CRIADO_EM = $CRIADO_EM;
        return $this;
    }
    
    
}