<?php
namespace App\Daos;

use BMorais\Database\Crud;
use App\Libs\FuncoesLib;
use App\Models\RecuperaSenhaModel;

class ServicoDao extends Crud{


    public function __construct()
    {
        $this->setTable("SERVICO");
        $this->setClassModel("ServicoModel");
    }

    public function buscaServicos($buscar)
    {
        try {
            $sql = "SELECT S.CODSERVICO, S.CODMODULO, S.CONTROLLER, S.TITULO, S.ICONE, S.DESCRICAO, S.ORDEM, S.SITUACAO, M.TITULO AS TITULOMODULO
                FROM SERVICO as S 
                INNER JOIN MODULO as M ON M.CODMODULO=S.CODMODULO
                WHERE S.SITUACAO!='0' AND S.EXCLUIDO=0";
            if (!empty($buscar))
                $sql .= " AND (S.TITULO LIKE '%{$buscar}%' OR S.DESCRICAO LIKE '%{$buscar}%')";
            $sql .= " ORDER BY S.ORDEM, S.TITULO";

            $result = $this->executeSQL($sql);
            return $this->fetchArrayObj($result);
        } catch (\Exception $e) {
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
                    sp.CODUSUARIO
                    FROM SERVICO as s
                    LEFT JOIN PRIVILEGIO sp on s.CODSERVICO = sp.CODSERVICO
                    WHERE sp.CODUSUARIO=? AND sp.EXCLUIDO=0 AND s.SITUACAO=1";
            $result = $this->executeSQL($sql, [$codusuario]);
            return $this->fetchArrayObj($result);
        } catch (\Exception $e) {
            throw new \Error($e->getMessage());
        }

    }

    public function buscarServicosPrivilegiosModulo($codusuario, $codmodulo){
        try {
            $sql = "SELECT s.CODSERVICO, s.CODMODULO, s.CONTROLLER, s.TITULO, 
                    s.ICONE, s.DESCRICAO, s.ORDEM,
                    sp.CODPRIVILEGIO, sp.EXCLUIR, sp.LER, sp.SALVAR, sp.ALTERAR, sp.OUTROS
                    FROM SERVICO as s
                    LEFT JOIN PRIVILEGIO sp on s.CODSERVICO = sp.CODSERVICO AND sp.CODUSUARIO=?
                    WHERE s.CODMODULO=? AND s.SITUACAO=1 AND s.EXCLUIDO=0";
            $result = $this->executeSQL($sql, [$codusuario, $codmodulo]);
            return $this->fetchArrayObj($result);
        } catch (\Exception $e) {
            throw new \Error($e->getMessage());
        }

    }
}