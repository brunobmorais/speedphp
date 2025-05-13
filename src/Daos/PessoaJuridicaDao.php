<?php
namespace App\Daos;
use App\Libs\EmailLib;
use App\Libs\FuncoesLib;
use App\Libs\SessionLib;
use App\Libs\TemplateEmailLib;
use App\Models\EnderecoModel;
use App\Models\FiFornecedorModel;
use App\Models\PessoaFisicaModel;
use App\Models\PessoajuridicaModel;
use App\Models\PessoaModel;
use BMorais\Database\CrudBuilder;

class PessoaJuridicaDao extends CrudBuilder
{
    public function __construct()
    {
        $this->setTableName("PESSOA_JURIDICA");
        $this->setClassModel("PessoaJuridicaModel");
    }

    public function buscarPessoaCNPJ($cnpj)
    {
        try {
            $sql = "SELECT PJ.CODPESSOA_JURIDICA, PJ.CODPESSOA, PJ.CNPJ, PJ.NOMEFANTASIA,
                        P.NOME, P.EMAIL, P.TELEFONE, P.TIPOPESSOA, P.IMAGEM,
                        LOGRADOURO, NUMERO, BAIRRO, COMPLEMENTO, LATITUDE, LONGITUDE, E.CODCIDADE, C.NOME AS NOMECIDADE, C.UF AS ESTADO, C.CODIGO AS CODIGO_CIDADE_IBGE
                        FROM PESSOA_JURIDICA AS PJ
                        INNER JOIN PESSOA AS P ON P.CODPESSOA = PJ.CODPESSOA
                        INNER JOIN ENDERECO AS E ON E.CODENDERECO=P.CODENDERECO
                        INNER JOIN CIDADE AS C ON C.CODCIDADE=E.CODCIDADE
                        WHERE PJ.CNPJ = ? AND P.EXCLUIDO=0 AND PJ.EXCLUIDO=0";

            $this->executeSQL($sql, [$cnpj]);
            return $this->fetchArrayObj();

        } catch (\Error $e) {
            return $e;
        }
    }

    public function buscarPessoa($codpessoa)
    {
        return $this->select('*', 'WHERE CODPESSOA = ?', [$codpessoa]);
    }

    public function inserirFornecedor(EnderecoModel $endereco, PessoaModel $pessoa, PessoajuridicaModel $pessoaJuridica)
    {

        try {
            $this->beginTransaction();

            // VERIFICA SE O FORNECEDOR JÁ ESTÁ CADASTRADO
            $this->executeSQL('SELECT PJ.CNPJ FROM FI_FORNECEDOR AS F
                INNER JOIN PESSOA AS P ON P.CODPESSOA=F.CODPESSOA
                INNER JOIN PESSOA_JURIDICA PJ ON PJ.CODPESSOA=P.CODPESSOA
                WHERE PJ.CNPJ = ? AND F.EXCLUIDO = 0 AND P.EXCLUIDO=0 AND PJ.EXCLUIDO=0', [(new FuncoesLib)->formatCpfBanco($pessoaJuridica->getCNPJ())]);
            if ($this->rowCount() > 0) {
                return [
                    "error" => true,
                    "message" => "Fornecedor já cadastrado",
                    "codpessoa" => null
                ];
            }

            // BUSCA SE A PESSOA ESTÁ CADASTRADA
            $this->executeSQL('SELECT P.CODPESSOA, CODENDERECO, TIPOPESSOA, NOME, TELEFONE, EMAIL, CRIADO_EM, ALTERADO_EM 
                FROM PESSOA AS P 
                INNER JOIN PESSOA_JURIDICA PJ ON PJ.CODPESSOA=P.CODPESSOA
                WHERE PJ.CNPJ = ? AND PJ.EXCLUIDO = 0 AND P.EXCLUIDO= 0', [(new FuncoesLib)->formatCpfBanco($pessoaJuridica->getCNPJ())]);

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
                $this->executeSQL("INSERT INTO PESSOA (CODENDERECO, TIPOPESSOA, NOME, TELEFONE, EMAIL) VALUES (?, ?, ?, ?, ?) ", [$codEndereco, $pessoa->getTIPOPESSOA(), (new FuncoesLib())->textoPrimeiraLetraMaiusculoCadaPalavra($pessoa->getNOME()), $pessoa->getTELEFONE(), $pessoa->getEMAIL()]);
                $codPessoa = $this->lastInsertId();
                if (!$codPessoa) {
                    $this->rollBackTransaction();
                    return [
                        "error" => true,
                        "message" => "Erro ao cadastrar pessoa!",
                        "codpessoa" => null
                    ];
                }

                // CADASTRAR PESSOA JURIDICA
                $result = $this->executeSQL('INSERT INTO PESSOA_JURIDICA (CODPESSOA, CNPJ, NOMEFANTASIA) VALUES (?, ?, ?)', [$codPessoa, preg_replace("/[^0-9]/", "", $pessoaJuridica->getCNPJ()), (new FuncoesLib())->textoPrimeiraLetraMaiusculoCadaPalavra($pessoaJuridica->getNOMEFANTASIA())]);
                if (!$result) {
                    $this->rollBackTransaction();
                    return [
                        "error" => true,
                        "message" => "Erro ao cadastrar pessoa jurídica!",
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

            }

            // CADASTRAR FORNECEDOR
            $result = $this->executeSQL('INSERT INTO FI_FORNECEDOR (CODPESSOA, SITUACAO, CODPESSOA_CADASTRO) VALUES (?,?, ?)', [$codPessoa ?? $pessoaObj->CODPESSOA , 1, SessionLib::getValue('CODPESSOA')]);
            $codFornecedor = $this->lastInsertId();
            if (!$result) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao cadastrar endereço!",
                    "codpessoa" => null
                ];
            }


            $this->commitTransaction();
            return [
                "error" => false,
                "message" => "Cadastrado com sucesso",
                "codfornecedor" => $codFornecedor,
                "codpessoa" => $codPessoa ?? $pessoaObj->CODPESSOA
            ];

        } catch (\Error $th) {
            $this->rollBackTransaction();
            return $th;
        }
    }
    public function editarFornecedor(EnderecoModel $endereco, PessoaModel $pessoa, PessoajuridicaModel $pessoaJuridica, FiFornecedorModel $fornecedor)
    {
        if (!$pessoaJuridica->getCODPESSOAJURIDICA()) {
            return [
                "error" => true,
                "message" => "Pessoa Jurídica não encontrada",
                "codpessoa" => null
            ];
        }
        $objeto = new \stdClass; 
        $objeto->CNPJ = preg_replace("/[^0-9]/", "", $pessoaJuridica->getCNPJ()) ; 
        $objeto->NOMEFANTASIA = $pessoaJuridica->getNOMEFANTASIA(); 
        if(!$this->updateObject($objeto, 'CODPESSOA_JURIDICA = '.$pessoaJuridica->getCODPESSOAJURIDICA())){
            return [
                "error" => true,
                "message" => "Erro ao alterar Pessoa Jurídica",
                "codpessoa" => $pessoa->getCODPESSOA() ?? $pessoaJuridica->getCODPESSOAJURIDICA() 
            ];
        }
        if(!$this->executeSQL('UPDATE ENDERECO SET CODCIDADE = ? , CEP = ?, LOGRADOURO = ?, BAIRRO = ? , NUMERO = ? , COMPLEMENTO = ? WHERE CODENDERECO = '.$endereco->getCODENDERECO(),
        [$endereco->getCODCIDADE(),$endereco->getCEP(), $endereco->getLOGRADOURO(), $endereco->getBAIRRO(), $endereco->getNUMERO(), $endereco->getCOMPLEMENTO()])){
            return [
                "error" => true,
                "message" => "Erro ao alterar Endereço",
                "codpessoa" => $pessoa->getCODPESSOA() ?? $pessoaJuridica->getCODPESSOAJURIDICA() 
            ];
        }

        if(!$this->executeSQL('UPDATE PESSOA SET NOME = ? , TELEFONE = ? , EMAIL = ? WHERE CODPESSOA = '.$pessoa->getCODPESSOA(), [$pessoa->getNOME(), $pessoa->getTELEFONE(), $pessoa->getEMAIL()])){
            return [
                "error" => true,
                "message" => "Erro ao alterar dados",
                "codpessoa" => $pessoa->getCODPESSOA() ?? $pessoaJuridica->getCODPESSOAJURIDICA() 
            ];
        }

        if(!$this->executeSQL('UPDATE FI_FORNECEDOR SET SITUACAO = ?, CODPESSOA_CADASTRO = ? WHERE CODFORNECEDOR = '.$fornecedor->getCODFORNECEDOR(), [$fornecedor->getSITUACAO(), SessionLib::getValue('CODPESSOA')])){
            return [
                "error" => true,
                "message" => "Erro ao alterar situação do fornecedor",
                "codpessoa" => $pessoa->getCODPESSOA() ?? $pessoaJuridica->getCODPESSOAJURIDICA() 
            ];
        }
        return [
            "error" => false,
            "message" => "Dados alterados com sucesso",
            "codpessoa" => $pessoa->getCODPESSOA() ?? $pessoaJuridica->getCODPESSOAJURIDICA() 
        ];
    }
}