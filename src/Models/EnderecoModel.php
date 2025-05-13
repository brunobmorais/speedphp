<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class EnderecoModel extends ModelAbstract
{
    Protected $CODENDERECO;
    Protected $CODCIDADE;
    Protected $CEP;
    Protected $LOGRADOURO;
    Protected $NUMERO;
    Protected $BAIRRO;
    Protected $COMPLEMENTO;
    Protected $LATITUDE;
    Protected $LONGITUDE;
    Protected $EXCLUIDO;
    Protected $CRIADO_EM;
    Protected $ALTERADO_EM;
    
    public function getCODENDERECO()
    {
        return $this->CODENDERECO;
    }
    
    public function getCODCIDADE()
    {
        return $this->CODCIDADE;
    }
    
    public function getCEP()
    {
        return $this->CEP;
    }
    
    public function getLOGRADOURO()
    {
        return $this->LOGRADOURO;
    }
    
    public function getNUMERO()
    {
        return $this->NUMERO;
    }
    
    public function getBAIRRO()
    {
        return $this->BAIRRO;
    }
    
    public function getCOMPLEMENTO()
    {
        return $this->COMPLEMENTO;
    }
    
    public function getLATITUDE()
    {
        return $this->LATITUDE;
    }
    
    public function getLONGITUDE()
    {
        return $this->LONGITUDE;
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
    
    
    public function setCODENDERECO($CODENDERECO):self
    {
        $this->CODENDERECO = $CODENDERECO;
        return $this;
    }
    
    public function setCODCIDADE($CODCIDADE):self
    {
        $this->CODCIDADE = $CODCIDADE;
        return $this;
    }
    
    public function setCEP($CEP):self
    {
        $this->CEP = $CEP;
        return $this;
    }
    
    public function setLOGRADOURO($LOGRADOURO):self
    {
        $this->LOGRADOURO = $LOGRADOURO;
        return $this;
    }
    
    public function setNUMERO($NUMERO):self
    {
        $this->NUMERO = $NUMERO;
        return $this;
    }
    
    public function setBAIRRO($BAIRRO):self
    {
        $this->BAIRRO = $BAIRRO;
        return $this;
    }
    
    public function setCOMPLEMENTO($COMPLEMENTO):self
    {
        $this->COMPLEMENTO = $COMPLEMENTO;
        return $this;
    }
    
    public function setLATITUDE($LATITUDE):self
    {
        $this->LATITUDE = $LATITUDE;
        return $this;
    }
    
    public function setLONGITUDE($LONGITUDE):self
    {
        $this->LONGITUDE = $LONGITUDE;
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