<?php
namespace App\Models;


class RecuperaSenhaModel {
    private $CODRECUPERASENHA;
    private $CODUSUARIO;
    private $DATASOLICITACAO;
    private $TOKEN;
    private $EXPIRADO;


    /**
     * @return mixed
     */
    public function getCODRECUPERASENHA()
    {
        return $this->CODRECUPERASENHA;
    }

    /**
     * @param mixed $CODRECUPERASENHA
     * @return RecuperaSenhaModel
     */
    public function setCODRECUPERASENHA($CODRECUPERASENHA):self
    {
        $this->CODRECUPERASENHA = $CODRECUPERASENHA;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCODUSUARIO()
    {
        return $this->CODUSUARIO;
    }

    /**
     * @param mixed $CODUSUARIO
     * @return RecuperaSenhaModel
     */
    public function setCODUSUARIO($CODUSUARIO):self
    {
        $this->CODUSUARIO = $CODUSUARIO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDATASOLICITACAO()
    {
        return $this->DATASOLICITACAO;
    }

    /**
     * @param mixed $DATASOLICITACAO
     * @return RecuperaSenhaModel
     */
    public function setDATASOLICITACAO($DATASOLICITACAO):self
    {
        $this->DATASOLICITACAO = $DATASOLICITACAO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTOKEN()
    {
        return $this->TOKEN;
    }

    /**
     * @param mixed $TOKEN
     * @return RecuperaSenhaModel
     */
    public function setTOKEN($TOKEN):self
    {
        $this->TOKEN = $TOKEN;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEXPIRADO()
    {
        return $this->EXPIRADO;
    }

    /**
     * @param mixed $EXPIRADO
     * @return RecuperaSenhaModel
     */
    public function setEXPIRADO($EXPIRADO):self
    {
        $this->EXPIRADO = $EXPIRADO;
        return $this;
    }


}
