<?php

namespace App\Daos;

use App\Libs\EmailLib;
use App\Libs\FuncoesLib;
use App\Libs\SessionLib;
use App\Libs\TemplateEmailLib;
use App\Models\EnderecoModel;
use App\Models\GpFuncionarioModel;
use App\Models\PessoaFisicaModel;
use App\Models\PessoaModel;
use BMorais\Database\CrudBuilder;

class PessoaFisicaDao extends CrudBuilder
{


    public function __construct()
    {
        $this->setTableName("PESSOA_FISICA", "PF");
        $this->setClassModel("PessoaFisicaModel");
    }

    public function buscarPessoa($codpessoa = "")
    {
        try {
            $sql = "SELECT PF.CODPESSOA_FISICA, PF.CODPESSOA, PF.DATANASCIMENTO, PF.CPF, PF.SEXO, PF.EXCLUIDO, 
                    P.CODENDERECO, P.IMAGEM, P.TIPOPESSOA, P.NOME, P.TELEFONE, P.EMAIL, CRIADO_EM, ALTERADO_EM 
                    FROM PESSOA_FISICA AS PF 
                        INNER JOIN PESSOA AS P ON P.CODPESSOA=PF.CODPESSOA
                        WHERE P.EXCLUIDO=0 ";
            $params = [];
            if (!empty($codpessoa)) {
                $sql .= "AND PF.CODPESSOA=?";
                $params[] = $codpessoa;
            }

            $data = $this->executeSQL($sql, $params);
            $data = $this->fetchArrayObj();
            if (!empty($data)) {
                $data[0]->CPF_OFUSCADO = (new FuncoesLib())->ofuscaCampo($data[0]->CPF, 3, 1);
            }

            return $data;

        } catch (\Error $e) {
            return $e;
        }
    }

    public function buscarPessoaCPF($cpf)
    {
        try {
            $sql = "SELECT P.CODPESSOA, CODPESSOA_FISICA, P.CODENDERECO, P.IMAGEM,
            DATE_FORMAT(DATANASCIMENTO,'%Y-%m-%d') AS DATANASCIMENTO, 
            TIMESTAMPDIFF(YEAR, PF.DATANASCIMENTO, CURDATE()) AS IDADE, 
            CPF, SEXO, TIPOPESSOA, P.NOME, TELEFONE, EMAIL, CEP, 
            LOGRADOURO, NUMERO, BAIRRO, COMPLEMENTO, LATITUDE, LONGITUDE, E.CODCIDADE, C.NOME AS NOMECIDADE, C.UF AS ESTADO, C.CODIGO AS CODIGO_CIDADE_IBGE,
                U.CODUSUARIO
                FROM PESSOA_FISICA AS PF
                INNER JOIN PESSOA AS P ON P.CODPESSOA=PF.CODPESSOA
                INNER JOIN ENDERECO AS E ON E.CODENDERECO=P.CODENDERECO
                INNER JOIN CIDADE AS C ON C.CODCIDADE=E.CODCIDADE
                LEFT JOIN SI_USUARIO AS U ON U.CODPESSOA=P.CODPESSOA
                WHERE CPF=? AND PF.EXCLUIDO=0 AND P.EXCLUIDO=0";
            $params = array($cpf);
            $this->executeSQL($sql, $params);
            return $this->fetchArrayObj();

        } catch (\Error $e) {
            return $e;
        }
    }

    public function inserirUsuario(EnderecoModel $endereco, PessoaModel $pessoa, PessoaFisicaModel $pessoaFisica)
    {

        $pessoaFisica->setCPF((new FuncoesLib())->removeCaracteres($pessoaFisica->getCPF()));
        try {
            $this->beginTransaction();

            // BUSCA SE A PESSOA ESTÁ CADASTRADA
            $this->executeSQL('SELECT P.CODPESSOA, CODENDERECO, TIPOPESSOA, NOME, TELEFONE, EMAIL, CRIADO_EM, ALTERADO_EM 
                FROM PESSOA AS P 
                INNER JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                WHERE PF.CPF = ? AND PF.EXCLUIDO = 0 AND P.EXCLUIDO= 0', [$pessoaFisica->getCPF()]);

            // VERIFICA SE RETORNOU ALGUM RESULTADO
            if ($this->rowCount() < 1) {
                // CADASTRAR ENDEREÇO
                $this->executeSQL("INSERT INTO ENDERECO (CODCIDADE, CEP, LOGRADOURO, NUMERO, BAIRRO, COMPLEMENTO)
            VALUES (?, ?, ?, ?, ?, ?) ", [$endereco->getCODCIDADE(), $endereco->getCEP(), $endereco->getLOGRADOURO(), $endereco->getNUMERO(), $endereco->getBAIRRO(), $endereco->getCOMPLEMENTO()]);
                $codEndereco = $this->lastInsertId();
                if (!$codEndereco) {
                    $this->rollBackTransaction();
                    return [
                        "error" => true,
                        "message" => "Erro ao cadastrar endereço!",
                        "codpessoa" => null
                    ];
                }

                // CADASTRAR PESSOA
                $this->executeSQL("INSERT INTO PESSOA (CODENDERECO, TIPOPESSOA, NOME, TELEFONE, EMAIL) VALUES (?, ?, ?, ?, ?) ", [$codEndereco, $pessoa->getTIPOPESSOA(), $pessoa->getNOME(), $pessoa->getTELEFONE(), $pessoa->getEMAIL()]);
                $codpessoa = $this->lastInsertId();
                if (!$codpessoa) {
                    $this->rollBackTransaction();
                    return [
                        "error" => true,
                        "message" => "Erro ao cadastrar pessoa!",
                        "codpessoa" => null
                    ];
                }

                // CADASTRAR PESSOA FISICA
                $result = $this->executeSQL('INSERT INTO PESSOA_FISICA (CODPESSOA, DATANASCIMENTO, CPF, SEXO) VALUES (?, ?, ?, ?)', [$codpessoa, $pessoaFisica->getDATANASCIMENTO(), $pessoaFisica->getCPF(), $pessoaFisica->getSEXO()]);
                if (!$result) {
                    $this->rollBackTransaction();
                    return [
                        "error" => true,
                        "message" => "Erro ao cadastrar pessoa física!",
                        "codpessoa" => null
                    ];
                }
            }

            // SE JA EXISTIR É PARA ATUALIZAR AS INFORMACOES EXISTENTES
            else {
                $pessoaObj = $this->fetchOneObj();

                // ATUALIZAR ENDEREÇO
                $result = $this->executeSQL(
                    "UPDATE ENDERECO SET CODCIDADE=?, CEP=?, LOGRADOURO=?, NUMERO=?, BAIRRO=?, COMPLEMENTO=? WHERE CODENDERECO=?",
                    [$endereco->getCODCIDADE(), $endereco->getCEP(), $endereco->getLOGRADOURO(), $endereco->getNUMERO(), $endereco->getBAIRRO(), $endereco->getCOMPLEMENTO(), $pessoaObj->CODENDERECO]
                );
                if (!$result) {
                    $this->rollBackTransaction();
                    return [
                        "error" => true,
                        "message" => "Erro ao cadastrar endereço!",
                        "codfuncionario" => null
                    ];
                }

                // ATUALIZAR PESSOA
                $result = $this->executeSQL(
                    "UPDATE PESSOA SET NOME=?, TELEFONE=?, EMAIL=? WHERE CODPESSOA=?",
                    [$pessoa->getNOME(), $pessoa->getTELEFONE(), $pessoa->getEMAIL(), $pessoaObj->CODPESSOA]
                );
                if (!$result) {
                    $this->rollBackTransaction();
                    return [
                        "error" => true,
                        "message" => "Erro ao atualizar pessoa!",
                        "codfuncionario" => null
                    ];
                }

                // NAO NECESSITA ATUALIZAR PESSOA FISICA POIS SAO INFORMAÇÕES BÁSICA DE UMA PESSOA FISICA
            }

            // CADASTRAR USUÁRIO
            $senha = substr($pessoaFisica->getCPF(), 0, 2) . "@".CONFIG_SITE["name"];
            $result = $this->executeSQL('INSERT INTO SI_USUARIO (CODPESSOA, SENHA, SITUACAO) VALUES (?,?,?)', [$codpessoa, (new FuncoesLib())->create_password_hash($senha), 1]);
            $codusuario = $this->lastInsertId();
            if (!$result) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao cadastrar endereço!",
                    "codpessoa" => null
                ];
            }

            // INSERE O PERFIL DE USUÁRIO
            $result = $this->executeSQL('INSERT INTO SI_PERFIL_USUARIO (CODUSUARIO, CODPERFIL) VALUES (?,?)', [$codusuario, 1]);
            if (!$result) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao cadastrar perfil",
                    "codpessoa" => null
                ];
            }

            // ENVIAR EMAIL PARA O USUÁRIO COM A SENHA DE ACESSO
            $msge = (new TemplateEmailLib)->template1(
                "Login de acesso ao sistema",
                "Você foi cadastrado ao sistema " . CONFIG_SITE["nameFull"],
                "Sua senha temporária é: " . $senha,
                CONFIG_SITE['url'],
                "Acessar agora"
            );

            EmailLib::sendEmailPHPMailer("Login de acesso ao sistema", $msge, array($pessoa->getEMAIL()));

            $this->commitTransaction();
            return [
                "error" => false,
                "message" => "Cadastrado com sucesso",
                "codpessoa" => $codpessoa
            ];

        } catch (\Error $th) {
            $this->rollBackTransaction();
            return $th;
        }
    }

    public function updatePessoa(EnderecoModel $endereco, PessoaModel $pessoa, PessoaFisicaModel $pessoaFisica)
    {
        $pessoaFisica->setCPF((new FuncoesLib())->removeCaracteres($pessoaFisica->getCPF()));
        try {
            $this->beginTransaction();

            // BUSCA SE A PESSOA ESTÁ CADASTRADA
            $this->executeSQL('SELECT P.CODPESSOA, CODENDERECO, TIPOPESSOA, NOME, TELEFONE, EMAIL, CRIADO_EM, ALTERADO_EM 
                FROM PESSOA AS P 
                INNER JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                WHERE PF.CPF = ? AND PF.EXCLUIDO = 0 AND P.EXCLUIDO= 0', [$pessoaFisica->getCPF()]);

            $pessoaObj = $this->fetchOneObj();

            // ATUALIZAR ENDEREÇO
            $result = $this->executeSQL(
                "UPDATE ENDERECO SET CODCIDADE=?, CEP=?, LOGRADOURO=?, NUMERO=?, BAIRRO=?, COMPLEMENTO=? WHERE CODENDERECO=?",
                [$endereco->getCODCIDADE(), $endereco->getCEP(), $endereco->getLOGRADOURO(), $endereco->getNUMERO(), $endereco->getBAIRRO(), $endereco->getCOMPLEMENTO(), $pessoaObj->CODENDERECO]
            );
            if (!$result) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao cadastrar endereço!",
                    "codpessoa" => null
                ];
            }

            // ATUALIZAR PESSOA
            $result = $this->executeSQL(
                "UPDATE PESSOA SET NOME=?, TELEFONE=?, EMAIL=? WHERE CODPESSOA=?",
                [$pessoa->getNOME(), $pessoa->getTELEFONE(), $pessoa->getEMAIL(), $pessoaObj->CODPESSOA]
            );
            if (!$result) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao atualizar pessoa!",
                    "codpessoa" => null
                ];
            }

            $result = $this->executeSQL(
                "UPDATE PESSOA_FISICA SET CPF=?, DATANASCIMENTO=?, SEXO=? WHERE CODPESSOA=?",
                [$pessoaFisica->getCPF(), $pessoaFisica->getDATANASCIMENTO(), $pessoaFisica->getSEXO(), $pessoaObj->CODPESSOA]
            );
            if (!$result) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao atualizar pessoa fisica!",
                    "codpessoa" => null
                ];
            }


            $this->commitTransaction();
            return [
                "error" => false,
                "message" => "Cadastrado com sucesso",
                "codpessoa" => $pessoa->getCODPESSOA()
            ];

        } catch (\Error $th) {
            $this->rollBackTransaction();
            return $th;
        }
    }

}
