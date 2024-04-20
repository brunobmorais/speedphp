<?php
namespace App\Daos;


use BMorais\Database\Crud;
use Error;

class CidadeDao extends Crud{


    public function __construct()
    {
        $this->setTableName("CIDADE");
    }

    public function buscar(array $buscar = [], $orderClause = null, $operator = [])
    {   
        try {
            $addSql = null ; 
            if(count($buscar)> 0 ){
               
                $arrayParams = [];  
                $addSql =  implode(',', array_map(
                    function ($v, $k) use (&$arrayParams, &$operator, &$buscar) {


                        return array_push($arrayParams, $k.' '. ($operator[array_search($v, array_values($buscar))]?? ' LIKE ' ).' ? ');
                    } ,
                    $buscar,
                    array_keys($buscar)
                ));

                $addSql = implode(' AND ', $arrayParams) ; 
              
            }
            if(!$orderClause and count($buscar) > 0 ){
                $orderClause = array_keys($buscar)[0].' ASC'; 
            }


           return  $this->select('*', (count($buscar) > 0  ? 'WHERE '.$addSql : '').( $orderClause  ? ' ORDER BY '.( $orderClause) : ''),  array_values($buscar)); 
             
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }


     public function getEstados(){
        try{

            return $this->select('DISTINCT(UF)', 'ORDER BY UF ASC') ; 

        }
        catch(Error $e){
            throw new \Error($e->getMessage());

        }
     }

   
  
}