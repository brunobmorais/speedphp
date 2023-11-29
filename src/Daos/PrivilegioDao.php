<?php
namespace App\Daos;

use BMorais\Database\Crud;

class PrivilegioDao extends Crud{

    public function __construct()
    {
        $this->setTableName("PRIVILEGIO");
        $this->setClassModel("PrivilegioModel");
    }
}