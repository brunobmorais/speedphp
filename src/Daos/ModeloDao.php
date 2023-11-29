<?php
namespace App\Daos;

use BMorais\Database\Crud;
use App\Libs\FuncoesLib;
use App\Models\RecuperaSenhaModel;

class ModeloDao extends Crud{


    public function __construct()
    {
        $this->setTableName("MODELO");
        $this->setClassModel("modeloModel");
    }
}