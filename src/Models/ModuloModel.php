<?php
namespace App\Models;

use App\Libs\FuncoesLib;
use BMorais\Database\ModelAbstract;

class ModuloModel extends ModelAbstract {

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
     * @return ModuloModel
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
     * @return ModuloModel
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
     * @return ModuloModel
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
     * @return ModuloModel
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
     * @return ModuloModel
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
     * @return ModuloModel
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
     * @return ModuloModel
     */
    public function setORDEM($ORDEM)
    {
        $this->ORDEM = $ORDEM;
        return $this;
    }

}
