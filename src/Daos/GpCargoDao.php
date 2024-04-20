<?php
namespace App\Daos;

use BMorais\Database\Crud;

class GpCargoDao extends Crud{


    public function __construct()
    {
        $this->setTableName("GP_CARGO");
        $this->setClassModel("GpCargoModel");
    }

    public function buscarTodos($buscar="")
    {
        try {
           return $this->select('*', 'ORDER BY NOME ASC'); 
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscarAtivos()
    {
        try {
           return $this->select('*', '  WHERE EXCLUIDO = 0 ORDER BY NOME ASC '); 
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    

  
    
}