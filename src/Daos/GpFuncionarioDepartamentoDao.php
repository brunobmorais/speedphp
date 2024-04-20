<?php
namespace App\Daos;

use BMorais\Database\Crud;

class GpFuncionarioDepartamentoDao extends Crud{


    public function __construct()
    {
        $this->setTableName("GP_FUNCIONARIO_DEPARTAMENTO");
        $this->setClassModel("GpFuncionarioDepartamentoModel");
    }

    public function buscarTodos($buscar="")
    {
        try {
           return $this->Select('*'); 
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscarFuncionario($codFuncionario){
        try {
            $data  = $this->select('*', ' FD LEFT JOIN GP_DEPARTAMENTO GD ON GD.CODDEPARTAMENTO = FD.CODDEPARTAMENTO WHERE FD.CODFUNCIONARIO = ? AND FD.EXCLUIDO = 0 ORDER BY FD.DATAINICIO DESC ', [$codFuncionario]); 
            return $data?? null; 
        } catch (\Error $e) {
             throw new \Error($e->getMessage());
         }
    }

}