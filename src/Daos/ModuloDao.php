<?php
namespace App\Daos;

use BMorais\Database\Crud;
use App\Libs\FuncoesLib;
use App\Models\RecuperaSenhaModel;

class ModuloDao extends Crud{


    public function __construct()
    {
        $this->setTable("MODULO");
        $this->setClassModel("moduloModel");
    }

    public function buscaModulosUsuario($cpf){
        try {
            $sql = "SELECT DISTINCT(M.CODMODULO), M.TITULO, M.CONTROLLER, M.ICONE, M.DESCRICAO, M.ORDEM FROM MODULO AS M
                    iNNER JOIN SERVICO AS s ON s.CODMODULO=M.CODMODULO
                    INNER JOIN PRIVILEGIO AS p ON p.CODSERVICO=s.CODSERVICO
                    WHERE p.LER='1' AND s.SITUACAO='1' AND M.SITUACAO='1' AND p.CODUSUARIO=? ORDER BY M.ORDEM, M.TITULO";
            $result = $this->executeSQL($sql, array($cpf));
            return $this->fetchArrayAssoc($result);
        } catch (\Exception $e) {
            throw new \Error($e->getMessage());
        }

    }

    public function buscaServicosUsuario($id, $controller){
        try {
            if (!empty($controller)) {
                $sql = "SELECT DISTINCT(s.CODSERVICO), 
                s.TITULO, s.DESCRICAO, s.ICONE, s.CONTROLLER, s.ORDEM,
                M.TITULO as TITULOMODULO, M.ICONE AS ICONEMODULO
                FROM PRIVILEGIO AS p 
                INNER JOIN USUARIO AS pp ON pp.CODUSUARIO=p.CODUSUARIO
                INNER JOIN SERVICO AS s ON s.CODSERVICO=p.CODSERVICO
                INNER JOIN MODULO AS M ON M.CODMODULO=s.CODMODULO 
                AND p.LER='1' AND s.SITUACAO='1' AND M.SITUACAO='1' 
                AND p.CODUSUARIO=? AND M.CONTROLLER=?
                ORDER BY s.ORDEM, s.TITULO";
                $result = $this->executeSQL($sql, array($id, $controller));
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