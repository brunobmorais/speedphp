<?php
namespace App\Daos;
use BMorais\Database\CrudBuilder;

class SiPerfilUsuarioDao extends CrudBuilder
{
    public function __construct()
    {
        $this->setTableName("SI_PERFIL_USUARIO");
        $this->setClassModel("SiPerfilUsuarioModel");
    }
}