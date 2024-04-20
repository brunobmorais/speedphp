<?php

namespace App\Daos;

use BMorais\Database\CrudBuilder;

class ModuloDao extends CrudBuilder
{


    public function __construct()
    {
        $this->setTableName("SI_MODULO", "M");
        $this->setClassModel("moduloModel");
    }

    public function buscaModulosUsuario($codusuario)
    {
        try {
            $result = $this
                ->selectBuilder("DISTINCT(M.CODMODULO), M.TITULO, M.CONTROLLER, M.ICONE, M.DESCRICAO, M.ORDEM")
                ->innerJoin("SI_SERVICO", "S", "S.CODMODULO=M.CODMODULO")
                ->innerJoin("SI_PRIVILEGIO", "P", "P.CODSERVICO=S.CODSERVICO")
                ->innerJoin("SI_PERFIL_USUARIO", "PU", "PU.CODPERFIL=P.CODPERFIL")
                ->where("P.LER='1'")
                ->andWhere("S.SITUACAO='1'")
                ->andWhere("M.SITUACAO='1'")
                ->andWhere("PU.CODUSUARIO=?", [$codusuario])
                ->orderBy("M.ORDEM")
                ->addOrderBy("M.TITULO")
                ->executeQuery()
                ->fetchArrayAssoc();
            return $result;
        } catch (\Error $e) {
            return $e;
        }


    }

    public function buscaServicosUsuario($id, $controller)
    {
        try {
            if (!empty($controller)) {
                $obj = $this->query("SELECT DISTINCT(s.CODSERVICO), 
                    s.TITULO, s.DESCRICAO, s.ICONE, s.CONTROLLER, s.ORDEM,
                    M.TITULO as TITULOMODULO, M.ICONE AS ICONEMODULO, M.CONTROLLER AS CONTROLLERMODULO
                    FROM SI_PRIVILEGIO AS p
                    INNER JOIN SI_PERFIL_USUARIO AS pu ON pu.CODPERFIL=p.CODPERFIL
                    INNER JOIN SI_SERVICO AS s ON s.CODSERVICO=p.CODSERVICO
                    INNER JOIN SI_MODULO AS M ON M.CODMODULO=s.CODMODULO 
                    AND p.LER='1' AND s.SITUACAO='1' AND M.SITUACAO='1' 
                    AND pu.CODUSUARIO=? AND M.CONTROLLER=?
                    ORDER BY s.ORDEM, s.TITULO", array($id, $controller))
                    ->executeQuery()
                    ->fetchArrayAssoc();

                if (!empty($obj)) {
                    return $obj;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }

    }

    public function buscaModulos($buscar)
    {
        try {
            $sql = "SELECT * FROM SI_MODULO WHERE EXCLUIDO=0";
            if (!empty($buscar))
                $sql .= " AND TITULO LIKE '%{$buscar}%' OR DESCRICAO LIKE '%{$buscar}%'";
            $sql .= " ORDER BY TITULO";

            $result = $this->executeSQL($sql);
            return $this->fetchArrayObj($result);
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscaServicoUsuario($id, $modulo, $servico)
    {
        try {
            if (!empty($modulo) && !empty($servico)) {
                $sql = "SELECT s.CODSERVICO, 
                  s.TITULO, s.DESCRICAO, s.ICONE, s.CONTROLLER, s.ORDEM,
                  M.TITULO AS TITULOMODULO, M.ICONE AS ICONEMODULO, M.CONTROLLER AS CONTROLLERMODULO,
                  p.LER, MAX(p.ALTERAR) AS ALTERAR, MAX(p.EXCLUIR) AS EXCLUIR, MAX(p.SALVAR) AS SALVAR, MAX(p.OUTROS) AS OUTROS
                  FROM SI_PRIVILEGIO AS p 
                  INNER JOIN SI_PERFIL_USUARIO AS pu ON pu.CODPERFIL=p.CODPERFIL
                  INNER JOIN SI_SERVICO AS s ON s.CODSERVICO=p.CODSERVICO
                  INNER JOIN SI_MODULO AS M ON M.CODMODULO=s.CODMODULO 
                  WHERE p.LER='1' 
                  AND s.SITUACAO='1' AND M.SITUACAO='1' 
                  AND pu.CODUSUARIO=? AND M.controller=? AND s.controller=?
                  GROUP BY s.CODSERVICO";
                $result = $this->executeSQL($sql, array($id, $modulo, $servico));
                $obj = $this->fetchArrayAssoc($result);
                if (!empty($obj)) {
                    return $obj[0];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }

    }
}