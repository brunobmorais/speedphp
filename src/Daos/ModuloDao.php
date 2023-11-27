<?php
namespace App\Daos;

use BMorais\Database\Crud;
use App\Libs\FuncoesLib;
use App\Models\RecuperaSenhaModel;
use BMorais\Database\CrudBuilder;

class ModuloDao extends CrudBuilder {


    public function __construct()
    {
        $this->setTable("MODULO","M");
        $this->setClassModel("moduloModel");
    }

    public function buscaModulosUsuario($cpf){
        try {
            $result =  $this
                ->selectBuilder("DISTINCT(M.CODMODULO), M.TITULO, M.CONTROLLER, M.ICONE, M.DESCRICAO, M.ORDEM")
                ->innerJoin("SERVICO", "S", "S.CODMODULO=M.CODMODULO")
                ->innerJoin("PRIVILEGIO", "P", "P.CODSERVICO=S.CODSERVICO")
                ->where("P.LER='1'")
                ->andWhere("S.SITUACAO='1'")
                ->andWhere("M.SITUACAO='1'")
                ->andWhere("P.CODUSUARIO=?", [$cpf])
                ->orderBy("M.ORDEM")
                ->addOrderBy("M.TITULO")
                ->executeQuery()
                ->fetchArrayAssoc();
            return $result;
        } catch (\Exception $e) {
            throw new \Error($e->getMessage());
        }

    }

    public function buscaServicosUsuario($id, $controller){
        try {
            if (!empty($controller)) {
                $obj = $this->query("SELECT DISTINCT(s.CODSERVICO), 
                    s.TITULO, s.DESCRICAO, s.ICONE, s.CONTROLLER, s.ORDEM,
                    M.TITULO as TITULOMODULO, M.ICONE AS ICONEMODULO
                    FROM PRIVILEGIO AS p 
                    INNER JOIN USUARIO AS pp ON pp.CODUSUARIO=p.CODUSUARIO
                    INNER JOIN SERVICO AS s ON s.CODSERVICO=p.CODSERVICO
                    INNER JOIN MODULO AS M ON M.CODMODULO=s.CODMODULO 
                    AND p.LER='1' AND s.SITUACAO='1' AND M.SITUACAO='1' 
                    AND p.CODUSUARIO=? AND M.CONTROLLER=?
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
        } catch (\Exception $e) {
            throw new \Error($e->getMessage());
        }

    }

    public function buscaModulos($buscar){
        try {
            $sql = "SELECT * FROM MODULO WHERE SITUACAO!='0' AND EXCLUIDO=0";
            if (!empty($buscar))
                $sql .= " AND TITULO LIKE '%{$buscar}%' OR DESCRICAO LIKE '%{$buscar}%'";
            $sql .= " ORDER BY TITULO";

            $result = $this->executeSQL($sql);
            return $this->fetchArrayObj($result);
        } catch (\Exception $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscaServicoUsuario($id, $modulo, $servico){
        try {
            if (!empty($modulo) && !empty($servico)) {
                $sql = "SELECT DISTINCT(s.CODSERVICO), 
                s.TITULO, s.DESCRICAO, s.ICONE, s.CONTROLLER, s.ORDEM,
                M.TITULO AS TITULOMODULO, M.ICONE AS ICONEMODULO,
                p.LER, p.ALTERAR, p.EXCLUIR, p.SALVAR, p.OUTROS
                FROM PRIVILEGIO AS p 
                INNER JOIN USUARIO AS pp ON pp.CODUSUARIO=p.CODUSUARIO
                INNER JOIN SERVICO AS s ON s.CODSERVICO=p.CODSERVICO
                INNER JOIN MODULO AS M ON M.CODMODULO=s.CODMODULO 
                AND p.LER='1' AND s.SITUACAO='1' AND M.SITUACAO='1' 
                AND p.CODUSUARIO=? AND M.controller=? AND s.controller=?";
                $result = $this->executeSQL($sql, array($id, $modulo, $servico));
                $obj = $this->fetchArrayAssoc($result);
                if (!empty($obj)) {
                    return $obj;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Error($e->getMessage());
        }

    }
}