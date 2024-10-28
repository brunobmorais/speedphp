<?php
namespace App\Daos;

use BMorais\Database\Crud;

class PerfilDao extends Crud{


    public function __construct()
    {
        $this->setTableName("SI_PERFIL");
        $this->setClassModel("PerfilModel");
    }

    public function buscarPerfis($buscar= "")
    {
        try {
            $sql = "SELECT P.CODPERFIL, P.NOME, P.NIVEL, P.EXCLUIDO
                FROM SI_PERFIL AS P 
                WHERE P.EXCLUIDO='0'";
            if (!empty($buscar))
                $sql .= " AND (P.NOME LIKE '%{$buscar}%')";
            $sql .= " ORDER BY P.NIVEL, P.NOME";

            $result = $this->executeSQL($sql);
            return $this->fetchArrayObj($result);
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscarPerfisUsuario($codusuario)
    {
        try {
            $sql = "SELECT P.CODPERFIL, P.NOME, P.NIVEL, P.EXCLUIDO, PU.CODPERFIL_USUARIO
                FROM SI_PERFIL AS P
                LEFT JOIN SI_PERFIL_USUARIO AS PU ON PU.CODPERFIL=P.CODPERFIL AND PU.CODUSUARIO=?
                WHERE P.EXCLUIDO='0'";

            $result = $this->executeSQL($sql,[$codusuario]);
            return $this->fetchArrayObj($result);
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscarPerfilId(string $id)
    {
        try {
            $sql = "SELECT P.CODPERFIL, P.NOME, P.NIVEL
                    FROM SI_PERFIL AS P 
                    WHERE P.CODPERFIL=? AND P.EXCLUIDO='0'";
            $params = array($id);
            $result = $this->executeSQL($sql, $params);
            if ($this->rowCount($result) > 0) {
                return $this->fetchArrayObj($result)[0];
            } else {
                return null;
            }
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }
}