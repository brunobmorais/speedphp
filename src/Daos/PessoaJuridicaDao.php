<?php
namespace App\Daos;
use BMorais\Database\CrudBuilder;

class PessoaJuridicaDao extends CrudBuilder
{
    public function __construct()
    {
        $this->setTableName("PESSOA_JURIDICA");
        $this->setClassModel("PessoaJuridicaModel");
    }
}