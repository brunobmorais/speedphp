<?php
namespace App\Daos;

use BMorais\Database\Crud;
use BMorais\Database\CrudBuilder;

class ServicoDao extends CrudBuilder {


    public function __construct()
    {
        $this->setTableName("SI_SERVICO");
        $this->setClassModel("ServicoModel");
    }

    public function buscaServicos($buscar)
    {
        try {
            $sql = "SELECT S.CODSERVICO, S.CODMODULO, S.CONTROLLER, S.TITULO, S.ICONE, S.DESCRICAO, S.ORDEM, S.SITUACAO, M.TITULO AS TITULOMODULO
                FROM SI_SERVICO as S 
                INNER JOIN SI_MODULO as M ON M.CODMODULO=S.CODMODULO
                WHERE S.SITUACAO!='0' AND S.EXCLUIDO=0";
            if (!empty($buscar))
                $sql .= " AND (S.TITULO LIKE '%{$buscar}%' OR S.DESCRICAO LIKE '%{$buscar}%')";
            $sql .= " ORDER BY M.TITULO, S.TITULO";

            $result = $this->executeSQL($sql);
            return $this->fetchArrayObj($result);
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscaServicosProtocoloUsuario()
    {
        try {
            $obj = $this->query("SELECT DISTINCT(S.CODSERVICO), 
                S.TITULO, S.DESCRICAO, S.ICONE, S.CONTROLLER, S.ORDEM,
                M.TITULO as TITULOMODULO, M.ICONE AS ICONEMODULO, M.CONTROLLER AS CONTROLLERMODULO
                FROM SI_SERVICO AS S
                INNER JOIN SI_MODULO AS M ON M.CODMODULO=S.CODMODULO 
                AND S.SITUACAO='1' 
                AND M.SITUACAO='1' 
                AND M.CONTROLLER=?
                ORDER BY S.ORDEM, S.TITULO", array('organizador'))
                ->executeQuery()
                ->fetchArrayAssoc();

            if (!empty($obj)) {
                return $obj;
            } else {
                return false;
            }

        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }

    }


    public function buscarServicosPrivilegios($codusuario)
    {
        try {
            $sql = "SELECT s.CODMODULO, s.CONTROLLER, s.TITULO, 
                    s.ICONE, s.DESCRICAO, 
                    s.ORDEM,
                    sp.CODPRIVILEGIO, sp.CODSERVICO, 
                    sp.EXCLUIR, sp.LER, sp.SALVAR, sp.ALTERAR, sp.OUTROS, 
                    sp.CODPERFIL
                    FROM SI_SERVICO as s
                    LEFT JOIN SI_PRIVILEGIO sp on s.CODSERVICO = sp.CODSERVICO
                    WHERE sp.CODPERFIL=? AND sp.EXCLUIDO=0 AND s.SITUACAO=1";
            $result = $this->executeSQL($sql, [$codusuario]);
            return $this->fetchArrayObj($result);
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }

    }

    public function buscarServicosPrivilegiosModulo($codperfil, $codmodulo){
        try {
            $sql = "SELECT s.CODSERVICO, s.CODMODULO, s.CONTROLLER, s.TITULO, 
                    s.ICONE, s.DESCRICAO, s.ORDEM,
                    sp.CODPRIVILEGIO, sp.EXCLUIR, sp.LER, sp.SALVAR, sp.ALTERAR, sp.OUTROS
                    FROM SI_SERVICO as s
                    LEFT JOIN SI_PRIVILEGIO sp on s.CODSERVICO = sp.CODSERVICO AND sp.CODPERFIL=?
                    WHERE s.CODMODULO=? AND s.SITUACAO=1 AND s.EXCLUIDO=0";
            $result = $this->executeSQL($sql, [$codperfil, $codmodulo]);
            return $this->fetchArrayObj($result);
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }

    }
}