<?php
namespace App\Daos;

use BMorais\Database\Crud;

class SICodigoValidadorDao extends Crud{


    public function __construct()
    {
        $this->setTableName("SI_CODIGOVALIDADOR");
        $this->setClassModel("SICodigoValidadorModel");
    }

    public function validarToken($token)
    {
        $sql = "SELECT * FROM SI_CODIGOVALIDADOR WHERE TOKEN LIKE ? AND SITUACAO=1 AND EXCLUIDO=0";
        $this->executeSQL($sql, [$token]);
        $data = $this->fetchArrayObj();
        if (empty($data))
            return false;
        return $data;
    }
}