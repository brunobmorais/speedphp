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

    public function buscarPessoa($codpessoa)
    {
        try {
            return $this->select('*', 'WHERE CODPESSOA = ?', [$codpessoa]);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function buscarPessoaCPF($cpf)
    {
        try {
            return $this->selectBuilder("P.CODPESSOA, CODPESSOA_FISICA, P.CODENDERECO, P.IMAGEM,
            DATE_FORMAT(DATANASCIMENTO,'%Y-%m-%d') AS DATANASCIMENTO, CPF, SEXO, TIPOPESSOA, P.NOME, TELEFONE, EMAIL, CEP, 
            LOGRADOURO, NUMERO, BAIRRO, COMPLEMENTO, LATITUDE, LONGITUDE, E.CODCIDADE, C.NOME AS NOMECIDADE, C.UF AS ESTADO, C.CODIGO AS CODIGO_CIDADE_IBGE",)
                ->innerJoin("PESSOA", "P", "P.CODPESSOA=PF.CODPESSOA")
                ->innerJoin("ENDERECO","E","E.CODENDERECO=P.CODENDERECO")
                ->innerJoin("CIDADE", "C", "C.CODCIDADE=E.CODCIDADE")
                ->where("CPF=?",[$cpf])
                ->andWhere("P.EXCLUIDO=0")
                ->executeQuery()
                ->fetchArrayObj();

        } catch (\Error $e) {
            return $e;
        }
    }

    public function inserirFuncionario(EnderecoModel $endereco, PessoaModel $pessoa, PessoaFisicaModel $pessoaFisica, GpFuncionarioModel $funcionario)
    {

        try {
            $this->beginTrasaction();

            // VERIFICA SE O FUNCIONÁRIO JÁ ESTÁ CADASTRADO
            $this->executeSQL('SELECT PF.CPF FROM GP_FUNCIONARIO AS F
                INNER JOIN PESSOA AS P ON P.CODPESSOA=F.CODPESSOA
                INNER JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                WHERE PF.CPF = ? AND F.EXCLUIDO = 0 AND P.EXCLUIDO=0 AND PF.EXCLUIDO=0', [$pessoaFisica->getCPF()]);
            if ($this->rowCount() > 0) {
                return [
                    "error" => true,
                    "message" => "Já existe funcionário com esse CPF!",
                    "codpessoa" => null
                ];
            }

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
                $codPessoa = $this->lastInsertId();
                if (!$codPessoa) {
                    $this->rollBackTransaction();
                    return [
                        "error" => true,
                        "message" => "Erro ao cadastrar pessoa!",
                        "codpessoa" => null
                    ];
                }

                // CADASTRAR PESSOA FISICA
                $result = $this->executeSQL('INSERT INTO PESSOA_FISICA (CODPESSOA, DATANASCIMENTO, CPF, SEXO) VALUES (?, ?, ?, ?)', [$codPessoa, $pessoaFisica->getDATANASCIMENTO(), $pessoaFisica->getCPF(), $pessoaFisica->getSEXO()]);
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
                    [$endereco->getCODCIDADE(), $pessoa->getNOME(), $pessoa->getTELEFONE(), $pessoa->getEMAIL(), $pessoaObj->CODPESSOA]
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

            // CADASTRAR FUNCIONARIO
            $result = $this->executeSQL('INSERT INTO GP_FUNCIONARIO (CODPESSOA, CODPESSOA_CADASTRO, CODCATEGORIA, NOMESOCIAL) VALUES (?,?,?,?)', [$codPessoa, SessionLib::getValue("CODPESSOA"), $funcionario->getCODCATEGORIA(), $funcionario->getNOMESOCIAL()]);
            $codFuncionario = $this->lastInsertId();
            if (!$result) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao cadastrar endereço!",
                    "codpessoa" => null
                ];
            }

            // CADASTRAR USUÁRIO
            $senha = substr($pessoaFisica->getCPF(), 0, 3) . "@".CONFIG_SITE['name'] ;
            $result = $this->executeSQL('INSERT INTO SI_USUARIO (CODPESSOA, SENHA, SITUACAO) VALUES (?,?,?)', [$codPessoa, (new FuncoesLib())->create_password_hash($senha), 1]);
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

            EmailLib::sendEmail("Login de acesso ao sistema", $msge, array($pessoa->getEMAIL()));

            $this->commitTransaction();
            return [
                "error" => false,
                "message" => "Cadastrado com sucesso",
                "codfuncionario" => $codFuncionario,
                "codpessoa" => $codPessoa
            ];

        } catch (\Error $th) {
            $this->rollBackTransaction();
            return $th;
        }
    }

    public function inserirUsuario(EnderecoModel $endereco, PessoaModel $pessoa, PessoaFisicaModel $pessoaFisica)
    {

        try {
            $this->beginTrasaction();

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
                $codPessoa = $this->lastInsertId();
                if (!$codPessoa) {
                    $this->rollBackTransaction();
                    return [
                        "error" => true,
                        "message" => "Erro ao cadastrar pessoa!",
                        "codpessoa" => null
                    ];
                }

                // CADASTRAR PESSOA FISICA
                $result = $this->executeSQL('INSERT INTO PESSOA_FISICA (CODPESSOA, DATANASCIMENTO, CPF, SEXO) VALUES (?, ?, ?, ?)', [$codPessoa, $pessoaFisica->getDATANASCIMENTO(), $pessoaFisica->getCPF(), $pessoaFisica->getSEXO()]);
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
                    [$endereco->getCODCIDADE(), $pessoa->getNOME(), $pessoa->getTELEFONE(), $pessoa->getEMAIL(), $pessoaObj->CODPESSOA]
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
            $result = $this->executeSQL('INSERT INTO SI_USUARIO (CODPESSOA, SENHA, SITUACAO) VALUES (?,?,?)', [$codPessoa, (new FuncoesLib())->create_password_hash($senha), 1]);
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

            EmailLib::sendEmail("Login de acesso ao sistema", $msge, array($pessoa->getEMAIL()));

            $this->commitTransaction();
            return [
                "error" => false,
                "message" => "Cadastrado com sucesso",
                "codpessoa" => $codPessoa
            ];

        } catch (\Error $th) {
            $this->rollBackTransaction();
            return $th;
        }
    }

    public function editarFuncionario(EnderecoModel $endereco, PessoaModel $pessoa, PessoaFisicaModel $pessoaFisica, GpFuncionarioModel $funcionario)
    {

        try {
            $this->beginTrasaction();

            $resultPessoa = $this->executeSQL("UPDATE PESSOA  SET NOME = ?, TELEFONE = ?, EMAIL = ?, IMAGEM=? WHERE CODPESSOA = ? ", [$pessoa->getNOME(), $pessoa->getTELEFONE(), $pessoa->getEMAIL(), $pessoa->getIMAGEM(), $pessoa->getCODPESSOA()]);
            if (!$resultPessoa) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao atualizar pessoa",
                ];
            }

            $resultPessoaFisica = $this->executeSQL('UPDATE PESSOA_FISICA SET DATANASCIMENTO = ? , CPF = ? , SEXO = ? WHERE CODPESSOA = ?', [$pessoaFisica->getDATANASCIMENTO(), $pessoaFisica->getCPF(), $pessoaFisica->getSEXO(), $pessoaFisica->getCODPESSOA()]);
            if (!$resultPessoaFisica) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao atualizar pessoa física!",
                ];
            }

            $resultEndereco =  $this->executeSQL(
                "UPDATE ENDERECO SET CODCIDADE=?, CEP=?, LOGRADOURO=?, NUMERO=?, BAIRRO=?, COMPLEMENTO=? WHERE CODENDERECO=?",
                [$endereco->getCODCIDADE(), $endereco->getCEP(), $endereco->getLOGRADOURO(), $endereco->getNUMERO(), $endereco->getBAIRRO(), $endereco->getCOMPLEMENTO(), $endereco->getCODENDERECO()]
            );
            if (!$resultEndereco) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao atualizar endereço!",
                ];
            }

            $resultGpFuncionario =  $this->executeSQL('UPDATE GP_FUNCIONARIO SET CODPESSOA_CADASTRO = ?, CODCATEGORIA = ?, NOMESOCIAL = ?, SITUACAO = ?  WHERE CODFUNCIONARIO = ?', [SessionLib::getValue("CODPESSOA"), $funcionario->getCODCATEGORIA(), $funcionario->getNOMESOCIAL(), $funcionario->getSITUACAO(), $funcionario->getCODFUNCIONARIO()]);
            if (!$resultGpFuncionario) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao atualizar funcionário!",
                ];
            }

            $this->commitTransaction();
            return [
                "error" => false,
                "message" => "Atualizado com sucesso",
                "codfuncionario" => $funcionario->getCODFUNCIONARIO()
            ];

        } catch (\Error $th) {
            $this->rollBackTransaction();
            return $th;
        }
    }
}
