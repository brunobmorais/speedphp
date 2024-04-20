<?php
namespace App\Daos;
use BMorais\Database\CrudBuilder;

class GpBancoDao extends CrudBuilder
{
    public function __construct()
    {
        $this->setTableName("GP_BANCO");
        $this->setClassModel("GpBancoModel");
    }

    public function buscarTodos(){
        return $this->select('*', 'WHERE EXCLUIDO = 0'); 
    }
}