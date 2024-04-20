<?php
namespace App\Daos;

use BMorais\Database\Crud;

class GpDepartamentoDao extends Crud{


    public function __construct()
    {
        $this->setTableName("GP_DEPARTAMENTO");
        $this->setClassModel("GpDepartamentoModel");
    }

    public function buscarTodos($buscar="")
    {
        try {
           return $this->Select('*'); 
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscarDepartamento($codDepartamento){
        try {
            $data  = $this->select('*', '  WHERE CODDEPARTAMENTO = ? AND EXCLUIDO = 0 ', [$codDepartamento]); 
            return $data[0]?? null; 
        } catch (\Error $e) {
             throw new \Error($e->getMessage());
         }
    }

}