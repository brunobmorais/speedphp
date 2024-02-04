<?php
namespace App\Daos;

use BMorais\Database\Crud;
use App\Libs\FuncoesLib;
use App\Models\RecuperaSenhaModel;

class PerfilDao extends Crud{


    public function __construct()
    {
        $this->setTableName("PERFIL");
        $this->setClassModel("PerfilModel");
    }

    public function buscarPerfis($buscar= "")
    {
        try {
            $sql = "SELECT P.CODPERFIL, P.NOME, P.NIVEL, P.EXCLUIDO
                FROM PERFIL AS P 
                WHERE P.EXCLUIDO='0'";
            if (!empty($buscar))
                $sql .= " AND (P.NOME LIKE '%{$buscar}%')";
            $sql .= " ORDER BY P.NIVEL, P.NOME";

            $result = $this->executeSQL($sql);
            return $this->fetchArrayObj($result);
        } catch (\Exception $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscarPerfilId(string $id)
    {
        try {
            $sql = "SELECT P.CODPERFIL, P.NOME, P.NIVEL
                    FROM PERFIL AS P 
                    WHERE P.CODPERFIL=? AND P.EXCLUIDO='0'";
            $params = array($id);
            $result = $this->executeSQL($sql, $params);
            if ($this->count($result) > 0) {
                return $this->fetchArrayObj($result)[0];
            } else {
                return null;
            }
        } catch (\Exception $e) {
            throw new \Error($e->getMessage());
        }
    }
}