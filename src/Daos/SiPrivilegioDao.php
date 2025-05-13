<?php
namespace App\Daos;

use BMorais\Database\Crud;

class SiPrivilegioDao extends Crud{

    public function __construct()
    {
        $this->setTableName("SI_PRIVILEGIO");
        $this->setClassModel("PrivilegioModel");
    }
}