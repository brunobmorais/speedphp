<?php
namespace App\Models;

use BMorais\Database\ModelAbstract;

class EnderecoModel extends ModelAbstract {


    protected $CODENDERECO;
    protected $CODCIDADE;
    protected $CEP;
    protected $LOGRADOURO;
    protected $NUMERO;
    protected $BAIRRO;
    protected $COMPLEMENTO;
    protected $LATITUDE;
    protected $LONGITUDE;
    protected $EXCLUIDO;
    protected $CRIADO_EM;
    protected $ALTERADO_EM;



    /**
     * Get the value of CODENDERECO
     */ 
    public function getCODENDERECO()
    {
        return $this->CODENDERECO;
    }

    /**
     * Get the value of CODCIDADE
     */ 
    public function getCODCIDADE()
    {
        return $this->CODCIDADE;
    }

    /**
     * Get the value of LOGRADOURO
     */ 
    public function getLOGRADOURO()
    {
        return $this->LOGRADOURO;
    }

    /**
     * Set the value of LOGRADOURO
     *
     * @return  self
     */ 
    public function setLOGRADOURO($LOGRADOURO)
    {
        $this->LOGRADOURO = $LOGRADOURO;

        return $this;
    }

    /**
     * Set the value of CODCIDADE
     *
     * @return  self
     */ 
    public function setCODCIDADE($CODCIDADE)
    {
        $this->CODCIDADE = $CODCIDADE;

        return $this;
    }

    /**
     * Get the value of NUMERO
     */ 
    public function getNUMERO()
    {
        return $this->NUMERO;
    }

    /**
     * Set the value of NUMERO
     *
     * @return  self
     */ 
    public function setNUMERO($NUMERO)
    {
        $this->NUMERO = $NUMERO;

        return $this;
    }

    /**
     * Get the value of BAIRRO
     */ 
    public function getBAIRRO()
    {
        return $this->BAIRRO;
    }

    /**
     * Set the value of BAIRRO
     *
     * @return  self
     */ 
    public function setBAIRRO($BAIRRO)
    {
        $this->BAIRRO = $BAIRRO;

        return $this;
    }

    /**
     * Get the value of COMPLEMENTO
     */ 
    public function getCOMPLEMENTO()
    {
        return $this->COMPLEMENTO;
    }

    /**
     * Set the value of COMPLEMENTO
     *
     * @return  self
     */ 
    public function setCOMPLEMENTO($COMPLEMENTO)
    {
        $this->COMPLEMENTO = $COMPLEMENTO;

        return $this;
    }

    /**
     * Get the value of LATITUDE
     */ 
    public function getLATITUDE()
    {
        return $this->LATITUDE;
    }

    /**
     * Set the value of LATITUDE
     *
     * @return  self
     */ 
    public function setLATITUDE($LATITUDE)
    {
        $this->LATITUDE = $LATITUDE;

        return $this;
    }

    /**
     * Get the value of LONGITUDE
     */ 
    public function getLONGITUDE()
    {
        return $this->LONGITUDE;
    }

    /**
     * Set the value of LONGITUDE
     *
     * @return  self
     */ 
    public function setLONGITUDE($LONGITUDE)
    {
        $this->LONGITUDE = $LONGITUDE;

        return $this;
    }

    /**
     * Get the value of EXCLUIDO
     */ 
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }

    /**
     * Set the value of EXCLUIDO
     *
     * @return  self
     */ 
    public function setEXCLUIDO($EXCLUIDO)
    {
        $this->EXCLUIDO = $EXCLUIDO;

        return $this;
    }

    /**
     * Get the value of CRIADO_EM
     */ 
    public function getCRIADO_EM()
    {
        return $this->CRIADO_EM;
    }

    /**
     * Get the value of ALTERADO_EM
     */ 
    public function getALTERADO_EM()
    {
        return $this->ALTERADO_EM;
    }

    /**
     * @return mixed
     */
    public function getCEP()
    {
        return $this->CEP;
    }

    /**
     * @param mixed $CEP
     * @return EnderecoModel
     */
    public function setCEP($CEP)
    {
        $this->CEP = $CEP;
        return $this;
    }
}
