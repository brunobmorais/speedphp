<?php
namespace App\Daos;

use App\Libs\SessionLib;
use App\Models\UsuarioModel;
use BMorais\Database\Crud;

class UsuarioDao extends Crud{

    public function __construct()
    {
        $this->setTableName("SI_USUARIO");
        $this->setClassModel("UsuarioModel");
    }

    public function buscarCpf($cpfcnpj): ?UsuarioModel{

        try {
            $sql = "SELECT P.CODPESSOA, U.CODUSUARIO, P.NOME, P.EMAIL, U.SITUACAO, U.SENHA, P.TELEFONE, PF.DATANASCIMENTO, PF.SEXO, PF.CPF 
                    FROM PESSOA AS P
                    INNER JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                    INNER JOIN SI_USUARIO AS U on U.CODPESSOA=P.CODPESSOA
                    WHERE PF.CPF=? AND U.EXCLUIDO=0 AND P.EXCLUIDO=0 AND PF.EXCLUIDO=0";
            $params = array($cpfcnpj);
            $result = $this->executeSQL($sql, $params);
            if ($this->rowCount($result) > 0) {
                return $this->fetchOneClass($result, $this->getClassModel());
            } else {
                return null;
            }
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }

    }

    public function buscarCodusuario($id): ?UsuarioModel{

        try {
            $sql = "SELECT P.CODPESSOA, U.CODUSUARIO, P.NOME, P.EMAIL, U.SITUACAO, U.SENHA, P.TELEFONE, PF.DATANASCIMENTO, PF.SEXO, PF.CPF 
                    FROM PESSOA AS P
                    INNER JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                    INNER JOIN SI_USUARIO AS U on U.CODPESSOA=P.CODPESSOA
                    WHERE U.CODUSUARIO=? AND U.EXCLUIDO=0 AND P.EXCLUIDO=0 AND PF.EXCLUIDO=0";
            $params = array($id);
            $result = $this->executeSQL($sql, $params);
            if ($this->rowCount($result) > 0) {
                return $this->fetchOneClass($result, $this->getClassModel());
            } else {
                return null;
            }
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }

    }

    public function updateSenha(UsuarioModel $pessoaModel): bool{


        $sql = "UPDATE SI_USUARIO SET SENHA=? WHERE CODUSUARIO=?";
        if ($this->executeSQL($sql,[$pessoaModel->getSENHA(), $pessoaModel->getCODUSUARIO()])){
            return true;
        }

        return false;

    }

    public function isOrganizadorEvento()
    {
        try {
            $codusuario = SessionLib::getValue("CODUSUARIO");
            $sql = "SELECT * FROM SI_PERFIL_USUARIO AS U
                    WHERE U.CODUSUARIO=? AND U.EXCLUIDO=0 AND U.CODPERFIL IN (3,4)";
            $result = $this->executeSQL($sql, [$codusuario]);
            $data = $this->fetchArrayObj();
            if (empty($data))
                return false;
            return true;
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function isAdministrador(): bool
    {
        try {
            $codusuario = SessionLib::getValue("CODUSUARIO");
            $sql = "SELECT * FROM SI_PERFIL_USUARIO AS U
                    WHERE U.CODUSUARIO=? AND U.EXCLUIDO=0 AND U.CODPERFIL IN (1,5)";
            $result = $this->executeSQL($sql, [$codusuario]);
            $data = $this->fetchArrayObj();
            if (empty($data))
                return false;
            return true;
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function insertUsuario(UsuarioModel $usuarioModel, $perfil = 2): bool{

        $sql = "INSERT INTO SI_USUARIO (CODPESSOA, SENHA, SITUACAO, ULTIMOACESSO) VALUES (?,?,?,?)";
        if ($this->executeSQL($sql,[$usuarioModel->getCODPESSOA(), $usuarioModel->getSENHA(), $usuarioModel->getSITUACAO(), date("Y-m-d H:i:s")])){
            $codusuario = $this->lastInsertId();
            $this->executeSQL("INSERT INTO SI_PERFIL_USUARIO (CODUSUARIO, CODPERFIL) VALUES (?,?)",[$codusuario, 2]); // ATLETA
            $this->executeSQL("INSERT INTO SI_PERFIL_USUARIO (CODUSUARIO, CODPERFIL) VALUES (?,?)",[$codusuario, 3]); // EVENTO

            return true;
        }

        return false;

    }
}