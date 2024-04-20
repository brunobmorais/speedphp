<?php
namespace App\Daos;

use BMorais\Database\Crud;

class GpFuncionarioDao extends Crud{


    public function __construct()
    {
        $this->setTableName("GP_FUNCIONARIO");
        $this->setClassModel("GpFuncionarioModel");
    }

    public function buscarTodos($buscar="")
    {
        try {
            $sql = "SELECT F.CODFUNCIONARIO, F.NOMESOCIAL, F.CODCATEGORIA, F.SITUACAO,
                        P.CODPESSOA, P.NOME, P.IMAGEM, P.TELEFONE, P.EMAIL,
                        PF.CODPESSOA_FISICA, PF.CPF, PF.SEXO
                        FROM GP_FUNCIONARIO AS F
                        INNER JOIN PESSOA AS P ON P.CODPESSOA=F.CODPESSOA
                        INNER JOIN PESSOA_FISICA AS PF ON PF.CODPESSOA=P.CODPESSOA
                        WHERE F.EXCLUIDO=0 AND P.EXCLUIDO=0 ";
            if (!empty($buscar))
                $sql .= "AND (P.NOME LIKE '%{$buscar}%' OR PF.CPF LIKE '%{$buscar}%') ";
            $sql .= "ORDER BY P.NOME";
           $this->executeSQL($sql);
           return $this->fetchArrayObj();
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscarPessoa($codPessoa){
        try {
            $data  = $this->select('*', 'WHERE CODPESSOA = ?', [$codPessoa]); 
            return $data[0]??[];
        } catch (\Error $e) {
             throw new \Error($e->getMessage());
         }
    }

}