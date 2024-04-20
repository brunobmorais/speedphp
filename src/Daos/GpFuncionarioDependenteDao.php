<?php
namespace App\Daos;

use BMorais\Database\Crud;

class GpFuncionarioDependenteDao extends Crud{


    public function __construct()
    {
        $this->setTableName("GP_FUNCIONARIO_DEPENDENTE");
        $this->setClassModel("GpFuncionarioDependenteModel");
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
            $data  = $this->select('D.*, TP.NOME AS VINCULO', ' D LEFT JOIN GP_TIPO_DEPENDENTE TP ON  TP.CODDEPENDENTE_TIPO = D.CODDEPENDENTE_TIPO  WHERE CODFUNCIONARIO = ? AND D.EXCLUIDO = 0', [$codFuncionario]); 
            return $data; 
        } catch (\Error $e) {
             throw new \Error($e->getMessage());
         }
    }

}