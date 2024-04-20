<?php
namespace App\Daos;
use BMorais\Database\CrudBuilder;

class GpFuncionarioCargoDao extends CrudBuilder
{
    public function __construct()
    {
        $this->setTableName("GP_FUNCIONARIO_CARGO");
        $this->setClassModel("GpFuncionarioCargoModel");
    }

    public function getByCodfuncionario($codfuncionario){
        return $this->select('*', 'FC  LEFT JOIN GP_CARGO C ON C.CODCARGO = FC.CODCARGO WHERE CODFUNCIONARIO = ? AND FC.EXCLUIDO = 0 ORDER BY DATAADMISSAO DESC', [$codfuncionario]); 
    }
}