<?php
namespace App\Daos;

use BMorais\Database\Crud;

class ModeloDao extends Crud{


    public function __construct()
    {
        $this->setTableName("SI_MODELO");
        $this->setClassModel("modeloModel");
    }
}