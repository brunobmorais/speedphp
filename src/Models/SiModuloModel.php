<?php
namespace App\Models;

use BMorais\Database\ModelAbstract;

class SiModuloModel extends ModelAbstract {

    protected $CODMODULO;
    protected $TITULO;
    protected $DESCRICAO;
    protected $ICONE;
    protected $CONTROLLER;
    protected $SITUACAO;
    protected $ORDEM;

    /**
     * @return mixed
     */
    public function getCODMODULO()
    {
        return $this->CODMODULO;
    }

    /**
     * @param mixed $CODMODULO
     * @return SiModuloModel
     */
    public function setCODMODULO($CODMODULO)
    {
        $this->CODMODULO = $CODMODULO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTITULO()
    {
        return $this->TITULO;
    }

    /**
     * @param mixed $TITULO
     * @return SiModuloModel
     */
    public function setTITULO($TITULO)
    {
        $this->TITULO = $TITULO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDESCRICAO()
    {
        return $this->DESCRICAO;
    }

    /**
     * @param mixed $DESCRICAO
     * @return SiModuloModel
     */
    public function setDESCRICAO($DESCRICAO)
    {
        $this->DESCRICAO = $DESCRICAO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getICONE()
    {
        return $this->ICONE;
    }

    /**
     * @param mixed $ICONE
     * @return SiModuloModel
     */
    public function setICONE($ICONE)
    {
        $this->ICONE = $ICONE;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCONTROLLER()
    {
        return $this->CONTROLLER;
    }

    /**
     * @param mixed $CONTROLLER
     * @return SiModuloModel
     */
    public function setCONTROLLER($CONTROLLER)
    {
        $this->CONTROLLER = $CONTROLLER;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSITUACAO()
    {
        return $this->SITUACAO;
    }

    /**
     * @param mixed $SITUACAO
     * @return SiModuloModel
     */
    public function setSITUACAO($SITUACAO)
    {
        $this->SITUACAO = $SITUACAO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getORDEM()
    {
        return $this->ORDEM;
    }

    /**
     * @param mixed $ORDEM
     * @return SiModuloModel
     */
    public function setORDEM($ORDEM)
    {
        $this->ORDEM = $ORDEM;
        return $this;
    }

}
