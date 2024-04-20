<?php
namespace App\Daos;
use BMorais\Database\CrudBuilder;

class GpFeriasDao extends CrudBuilder
{
    public function __construct()
    {
        $this->setTableName("GP_FERIAS");
        $this->setClassModel("GpFeriasModel");
    }
}