<?php
namespace App\Daos;

use App\Libs\FuncoesLib;
use App\Models\EnderecoModel;
use App\Models\FuncionarioModel;
use App\Models\PessoaModel;
use App\Models\UsuarioModel;
use BMorais\Database\Crud;
use Error;

class PessoaDao extends Crud{


    public function __construct()
    {
        $this->setTableName("PESSOA");
        $this->setClassModel("PessoaModel");
    }


    public function buscarPessoaTodos($buscar = null)
    {
        $sql = "SELECT * FROM PESSOA WHERE EXCLUIDO!='0'";
        if (!empty($buscar))
            $sql .= " AND NOME LIKE '%{$buscar}%' OR CPF LIKE '%{$buscar}%'";
        $result = $this->executeSQL($sql);
        return $this->fetchArrayObj($result)??null;
    }

    public function buscarTodosUsuarios($buscar = null)
    {
        $sql = "SELECT U.CODUSUARIO, P.CODPESSOA, P.NOME, P.EMAIL, PF.CPF, U.SITUACAO
                    FROM SI_USUARIO AS U
                    INNER JOIN PESSOA AS P on P.CODPESSOA=U.CODPESSOA
                    INNER JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                    WHERE P.EXCLUIDO='0' ORDER BY P.NOME";
        if (!empty($buscar))
            $sql .= " AND P.NOME LIKE '%{$buscar}%' OR PF.CPF LIKE '%{$buscar}%'";
        $result = $this->executeSQL($sql);
        return $this->fetchArrayObj($result)??null;
    }

    public function buscarFuncionarioCpfCnpj($cpfcnpj): ?PessoaModel{

        try {
            $sql = "SELECT P.CODPESSOA, U.CODUSUARIO, P.NOME, P.EMAIL, U.SITUACAO, U.SENHA, P.TELEFONE, PF.DATANASCIMENTO, PF.SEXO, PF.CPF 
                    FROM PESSOA AS P
                    INNER JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                    INNER JOIN SI_USUARIO AS U on U.CODPESSOA=P.CODPESSOA
                    WHERE PF.CPF=? AND U.EXCLUIDO=0 AND P.EXCLUIDO=0 AND PF.EXCLUIDO=0";
            $params = array($cpfcnpj);
            $result = $this->executeSQL($sql, $params);
            if ($this->count($result) > 0) {
                return $this->fetchOneClass($result, $this->getClassModel());
            } else {
                return null;
            }
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }

    }

    public function buscarFuncionarioId($id)
    {
        try {
            $sql = "SELECT P.CODPESSOA, P.NOME, PF.CPF, P.EMAIL, P.EXCLUIDO, U.CODUSUARIO
                    FROM PESSOA AS P 
                    INNER JOIN PESSOA_FISICA AS PF ON PF.CODPESSOA=P.CODPESSOA
                    INNER JOIN SI_USUARIO AS U on P.CODPESSOA=U.CODPESSOA
                    WHERE U.CODUSUARIO=? AND U.EXCLUIDO='0' AND P.EXCLUIDO=0";
            $params = array($id);
            $result = $this->executeSQL($sql, $params);
            if ($this->count($result) > 0) {
                return $this->fetchArrayObj($result);
            } else {
                return null;
            }
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscarUsuarioModelId($codusuario):?UsuarioModel
    {
        try {
            $sql = "SELECT * FROM PESSOA AS P
                    INNER JOIN SI_USUARIO U on U.CODPESSOA = P.CODPESSOA
                    WHERE U.CODUSUARIO=? AND P.EXCLUIDO!='1'";
            $params = array($codusuario);
            $result = $this->executeSQL($sql, $params);
            if ($this->count($result) > 0) {
                return $this->fetchOneClass($result, $this->getClassModel());
            } else {
                return null;
            }
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }


    public function inserirFuncionario(PessoaModel $pessoaModel, EnderecoModel $enderecoModel, FuncionarioModel $funcionarioModel){

        $funcoesClass = new FuncoesLib();
        $funcionarioDao = new FuncionarioDao();

        $this->beginTrasaction();

        // ENDEREÇO
        $sql = "INSERT INTO endereco (logradouro, numero, bairro, cep, latitude, longitude, complemento, id_municipio, sts, dtcad) VALUES (?,?,?,?,?,?,?,?,?,?)";
        $parametros = array($enderecoModel->getLogradouro(), $enderecoModel->getNumero(), $enderecoModel->getBairro(), $enderecoModel->getCep(), 0, 0, $enderecoModel->getComplemento(), $enderecoModel->getIdMunicipio(), $enderecoModel->getSts(), $enderecoModel->getDtcad());
        if ($this->executeSQL($sql,$parametros)) {
            $id_endereco = $this->lastInsertId();

            // PESSOA
            $sql = "INSERT INTO pessoa (nome_rs, cpf_cnpj, fone1, fone2, email, sts, id_endereco) VALUES (?,?,?,?,?,?,?)";
            $parametros = array($pessoaModel->getNomeRs(), $pessoaModel->getCpfCnpj(), $pessoaModel->getFone1(), $pessoaModel->getFone2(), $pessoaModel->getEmail(), $pessoaModel->getSts(), $id_endereco);
            if ($this->executeSQL($sql,$parametros)) {
                $id_pessoa = $this->lastInsertId();
                $funcionarioDao->insert("id_pessoa, id_funcao, senha, token",array($id_pessoa,$funcionarioModel->getIdFuncao(), $funcionarioModel->getToken(), $funcoesClass->create_password_hash($funcionarioModel->getSENHA())));
                $this->commitTransaction();
                return true;
            } else{
                $this->rollBackTransaction();
                return false;
            }
        } else {
            $this->rollBackTransaction();
            return false;
        }
    }

    public function atualizarFuncionario(PessoaModel $pessoaModel, EnderecoModel $enderecoModel, FuncionarioModel $funcionarioModel){

        $funcoesClass = new FuncoesLib();
        $funcionarioDao = new FuncionarioDao();

        $this->beginTrasaction();

        $sql = "UPDATE endereco SET logradouro=?, numero=?, bairro=?, cep=?, latitude=?, longitude=?, complemento=?, id_municipio=? WHERE id=?";
        $values = array($enderecoModel->getLogradouro(),
            $enderecoModel->getNumero(),
            $enderecoModel->getBairro(),
            $enderecoModel->getCep(),
            $enderecoModel->getLatitude(),
            $enderecoModel->getLongitude(),
            $enderecoModel->getComplemento(),
            $enderecoModel->getIdMunicipio(),
            $enderecoModel->getId(),
        );


        if ($this->executeSQL($sql,$values)) {

            $sql = "UPDATE pessoa SET nome_rs=?, id_endereco=?, cpf_cnpj=?, fone1=?, fone2=?, email=?, sts=? WHERE id=?";
            $values = array($pessoaModel->getNomeRs(),
                $enderecoModel->getId(),
                $pessoaModel->getCpfCnpj(),
                $pessoaModel->getFone1(),
                $pessoaModel->getFone2(),
                $pessoaModel->getEmail(),
                $pessoaModel->getSts(),
                $pessoaModel->getId()
            );
            if ($this->executeSQL($sql, $values)) {
                $funcionarioDao->update("id_funcao", array($funcionarioModel->getIdFuncao(),  $pessoaModel->getId()), "id_pessoa=?");
                $this->commitTransaction();
                return true;
            } else {
                $this->rollBackTransaction();
                return false;
            }
        } else {
            $this->rollBackTransaction();
            return false;
        }
    }

    public function updateSenha(PessoaModel $pessoaModel): bool{


        $sql = "UPDATE USUARIO SET senha='" . $pessoaModel->getSENHA() . "' WHERE CODUSUARIO='" . $pessoaModel->getCODUSUARIO() . "'";;
        if ($this->executeSQL($sql)){
            return true;
        } else {
            return false;
        }

    }

    public function buscarPessoaEmail($email): ?PessoaModel{

        $sql = "SELECT * FROM pessoa AS U WHERE U.email=? AND U.sts!='X'";
        $params = array($email);
        $result = $this->executeSQL($sql,$params);
        if ($this->count($result)>0){
            return $this->fetchOneClass($result,$this->getClassModel());
        } else {
            return null;
        }

    }

    public function buscarPessoaCpfCnpjModel($cpfcnpj): ?PessoaModel{

        $sql = "SELECT * FROM pessoa AS U WHERE U.cpf_cnpj LIKE ? AND U.sts!='X'";
        $params = array($cpfcnpj);
        $result = $this->executeSQL($sql,$params);
        if ($this->count($result)>0){
            return $this->fetchOneClass($result,$this->getClassModel());
        } else {
            return null;
        }

    }

    public function buscarPessoaModelId($codusuario):?PessoaModel
    {
        $sql = "SELECT * FROM pessoa AS U WHERE U.id=? AND U.sts!='X'";
        $params = array($codusuario);
        $result = $this->executeSQL($sql,$params);
        if ($this->count($result) > 0) {
            return $this->fetchOneClass($result,$this->classModel);
        } else {
            return null;
        }
    }

    public function buscarPessoa($codpessoa)
    {
      try{
        return $this->select('*', 'WHERE CODPESSOA = ?', [$codpessoa]);

      }catch(Error $e){
        return $e; 
      }
    }

    public function buscarTokenPessoa($token): ?array{

        $sql = "SELECT P.id, cpf_cnpj, nome_rs, fone1, fone2, email, origem_cad, rg, inscr_estadual,  P.sts, U.id as id_funcionario, U.token, U.senha,f2.id as id_funcao, f2.descr AS Uuncao 
                    FROM pessoa AS P
                    INNER JOIN funcionario AS U on U.id_pessoa=P.id
                    INNER JOIN funcao f2 on U.id_funcao = f2.id
                    WHERE U.token LIKE ?";
        $params = array($token);
        $result = $this->executeSQL($sql,$params);
        $obj = $this->fetchArrayAssoc($result);
        if (!empty($obj)){
            return $obj[0];
        } else {
            return null;
        }

    }

    public function buscarIdToken(PessoaModel $pessoaModel){


        $sql = "SELECT * FROM funcionario WHERE token = '" . $pessoaModel->getToken() . "' AND id ='" . $pessoaModel->getIdFuncionario() . "'";
        $result = $this->executeSQL($sql);
        if ($this->count($result)>0){
            return $this->fetchArrayObj($result);
        } else {
            return false;
        }

    }

    public function updateToken($token, $id){
        $sql = "UPDATE funcionario SET token=? WHERE id=?";
        $result = $this->executeSQL($sql,array($token, $id));
        if ($result)
            return true;
        else
            return false;
    }

}