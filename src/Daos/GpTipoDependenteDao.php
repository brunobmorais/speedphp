<?php
namespace App\Daos;

use BMorais\Database\Crud;

class GpTipoDependenteDao extends Crud{


    public function __construct()
    {
        $this->setTableName("GP_TIPO_DEPENDENTE");
        $this->setClassModel("GpTipoDependenteModel");
    }

    public function buscarTodos($buscar="")
    {
        try {
           return $this->Select('*', 'ORDER BY NOME ASC'); 
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

  
    
}