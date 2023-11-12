<?php
namespace App\Daos;

use BMorais\Database\Crud;

class PrivilegioDao extends Crud{

    public function __construct()
    {
        $this->setTable("PRIVILEGIO");
        $this->setClassModel("PrivilegioModel");
    }
}