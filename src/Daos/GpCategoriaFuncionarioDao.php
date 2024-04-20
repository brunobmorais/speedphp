<?php
namespace App\Daos;

use BMorais\Database\Crud;

class GpCategoriaFuncionarioDao extends Crud{


    public function __construct()
    {
        $this->setTableName("GP_CATEGORIA_FUNCIONARIO");
        $this->setClassModel("GpCategoriaFuncionarioModel");
    }

    public function buscar(array $buscar = [], $orderClause = null)
    {
        try {
            $addSql = null ; 
            if(count($buscar)> 0 ){
               
                $arrayParams = [];  
                $addSql =  implode(',', array_map(
                    function ($v, $k) use (&$arrayParams) {
                        return array_push($arrayParams, $k.' LIKE ? ');
                    } ,
                    $buscar,
                    array_keys($buscar)
                ));

                $addSql = implode(' AND ', $arrayParams) ; 
            }
           return $this->select('*', (count($buscar) > 0  ? 'WHERE '.$addSql : ''). ' ORDER BY '.( $orderClause ?? 'NOME ASC'),  array_values($buscar) ); 
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscarTodos(){
        return $this->Select('*', 'ORDER BY NOME ASC') ; 
    }

    
}