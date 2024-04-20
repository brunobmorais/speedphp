<?php
namespace App\Daos;

use BMorais\Database\Crud;

class GpFuncionarioBancoDao extends Crud{


    public function __construct()
    {
        $this->setTableName("GP_FUNCIONARIO_BANCO");
        $this->setClassModel("GpFuncionarioBancoModel");
    }

    public function buscarTodos($buscar="")
    {
        try {
           return $this->Select('*', 'WHERE EXCLUIDO = 0'); 
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscarFuncionario($codFuncionario){
        try {
            $data  = $this->select('*', 'B LEFT JOIN GP_BANCO AS GB ON GB.CODBANCO = B.CODBANCO WHERE B.CODFUNCIONARIO = ? AND B.EXCLUIDO = 0 ', [$codFuncionario]); 
            return $data; 
        } catch (\Error $e) {
             throw new \Error($e->getMessage());
         }
    }

}