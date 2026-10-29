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
        if (empty($token))
            return false;

        $sql = "SELECT * FROM SI_CODIGOVALIDADOR WHERE TOKEN = ? AND SITUACAO=1 AND EXCLUIDO=0 AND VALIDADE >= ?";
        $this->executeSQL($sql, [$token, date('Y-m-d H:i:s')]);
        $data = $this->fetchArrayObj();
        if (empty($data))
            return false;
        return $data;
    }
}