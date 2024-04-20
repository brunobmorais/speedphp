<?php
namespace App\Daos;

use BMorais\Database\Crud;

class GpTipoArquivoDao extends Crud{


    public function __construct()
    {
        $this->setTableName("GP_TIPO_ARQUIVO");
        $this->setClassModel("GpTipoArquivoModel");
    }

    public function buscarTodos($buscar="")
    {
        try {
           return $this->Select('*', 'ORDER BY NOME ASC'); 
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    

  
    
}