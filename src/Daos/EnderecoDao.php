<?php
namespace App\Daos;

use App\Models\EnderecoModel;
use BMorais\Database\Crud;


class EnderecoDao extends Crud{
    

    public function __construct()
    {
        $this->setTableName("ENDERECO");
        $this->setClassModel("EnderecoModel");
    }

    public function buscar(array $buscar = [])
    {
        try {

            $addSql = null ; 
            if(count($buscar)> 0 ){
                 
                $addSql =  implode(',', array_map(
                    function ($v, $k) {
                        return $k.' = ? ';
                    },
                    $buscar,
                    array_keys($buscar)
                ));
                
            }
           return $this->select('*', count($buscar) > 0 ? 'WHERE '.$addSql : '',  array_values($buscar) ); 
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function cadastrar(EnderecoModel $endereco){
        try {
            $this->insert('CODCIDADE, CEP, LOGRADOURO, NUMERO, BAIRRO, COMPLEMENTO',
            [$endereco->getCODCIDADE(), $endereco->getCEP(), $endereco->getLOGRADOURO(), $endereco->getNUMERO(), $endereco->getBAIRRO(), $endereco->getCOMPLEMENTO()]);
            return $this->lastInsertId(); 
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }

    }
  
}