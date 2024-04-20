<?php
namespace App\Daos;

use BMorais\Database\Crud;

class GpDocumentoDao extends Crud{


    public function __construct()
    {
        $this->setTableName("GP_DOCUMENTO");
        $this->setClassModel("GpDocumentoModel");
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
            $data  = $this->select('*', '  WHERE CODFUNCIONARIO = ? AND EXCLUIDO = 0 ', [$codFuncionario]); 
            return $data[0]?? null; 
        } catch (\Error $e) {
             throw new \Error($e->getMessage());
         }
    }

}