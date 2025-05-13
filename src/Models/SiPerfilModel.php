<?php
namespace App\Models;

use BMorais\Database\ModelAbstract;

class SiPerfilModel extends ModelAbstract {

    protected $CODPERFIL;
    protected $NOME;
    protected $NIVEL;

    /**
     * @return mixed
     */
    public function getCODPERFIL()
    {
        return $this->CODPERFIL;
    }

    /**
     * @param mixed $CODPERFIL
     * @return SiPerfilModel
     */
    public function setCODPERFIL($CODPERFIL)
    {
        $this->CODPERFIL = $CODPERFIL;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNOME()
    {
        return $this->NOME;
    }

    /**
     * @param mixed $NOME
     * @return SiPerfilModel
     */
    public function setNOME($NOME)
    {
        $this->NOME = $NOME;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNIVEL()
    {
        return $this->NIVEL;
    }

    /**
     * @param mixed $NIVEL
     * @return SiPerfilModel
     */
    public function setNIVEL($NIVEL)
    {
        $this->NIVEL = $NIVEL;
        return $this;
    }



}
