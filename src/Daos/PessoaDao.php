<?php

namespace App\Daos;

use App\Libs\EmailLib;
use App\Libs\FuncoesLib;
use App\Libs\SessionLib;
use App\Libs\TemplateEmailLib;
use App\Models\EnderecoModel;
use App\Models\FuncionarioModel;
use App\Models\GpFuncionarioModel;
use App\Models\InstituicaoModel;
use App\Models\PessoaFisicaModel;
use App\Models\PessoajuridicaModel;
use App\Models\PessoaModel;
use App\Models\UsuarioModel;
use BMorais\Database\Crud;
use Error;
use PDO;

class PessoaDao extends Crud
{

    public function __construct(?PDO $instance = null)
    {
        if (!empty($instance))
            $this->setInstance($instance);
        $this->setTableName("PESSOA");
        $this->setClassModel("PessoaModel");
    }

    public function buscarPessoaTodos($buscar = null)
    {
        $sql = "SELECT * FROM PESSOA WHERE EXCLUIDO!='0'";
        if (!empty($buscar))
            $sql .= " AND NOME LIKE '%{$buscar}%' OR CPF LIKE '%{$buscar}%'";
        $result = $this->executeSQL($sql);
        return $this->fetchArrayObj($result) ?? null;
    }

    public function buscarTodosUsuarios($buscar = null)
    {
        $sql = "SELECT U.CODUSUARIO, P.CODPESSOA, P.NOME, P.EMAIL, PF.CPF, U.SITUACAO,
                    TIMESTAMPDIFF(YEAR, PF.DATANASCIMENTO, CURDATE()) AS IDADE
                    FROM SI_USUARIO AS U
                    INNER JOIN PESSOA AS P on P.CODPESSOA=U.CODPESSOA
                    INNER JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                    WHERE P.EXCLUIDO='0'";
        if (!empty($buscar))
            $sql .= " AND (P.NOME LIKE '%{$buscar}%' OR PF.CPF LIKE '%{$buscar}%' OR P.EMAIL LIKE '%{$buscar}%' OR P.TELEFONE LIKE '%{$buscar}%')";

        $sql .= "  ORDER BY P.NOME";

        $result = $this->executeSQL($sql);
        return $this->fetchArrayObj($result) ?? null;
    }

    public function buscarPessoasAll($buscar = null)
    {
        $sql = "SELECT P.CODPESSOA, P.NOME, P.EMAIL, P.TELEFONE, P.IMAGEM, P.TIPOPESSOA, P.CODENDERECO,
                    PF.CODPESSOA_FISICA, PF.CPF, PF.DATANASCIMENTO, PF.SEXO,
                    TIMESTAMPDIFF(YEAR, PF.DATANASCIMENTO, CURDATE()) AS IDADE,
                    PJ.CODPESSOA_JURIDICA, PJ.CODPESSOA_JURIDICA, PJ.CNPJ, PJ.NOMEFANTASIA,
                    COALESCE(PF.CPF, PJ.CNPJ) AS CPFCNPJ
                    FROM PESSOA AS P
                    LEFT JOIN PESSOA_JURIDICA AS PJ on PJ.CODPESSOA=P.CODPESSOA
                    LEFT JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                    WHERE P.EXCLUIDO='0'";
        if (!empty($buscar))
            $sql .= " AND (P.NOME LIKE '%{$buscar}%' OR PF.CPF LIKE '%{$buscar}%' OR PJ.CNPJ LIKE '%{$buscar}%' OR P.EMAIL LIKE '%{$buscar}%' OR P.TELEFONE LIKE '%{$buscar}%')";

        $sql .= "  ORDER BY P.NOME";

        $result = $this->executeSQL($sql);
        return $this->fetchArrayObj($result) ?? null;
    }

    public function buscarAtletasPedido($codPedido)
    {
        $sql = "SELECT 
    PE.*,
    I.*, 
    I.SITUACAO AS SITUACAO_INSCRICAO,
    M.*,
    PE.NOME AS NOMEATLETA,
    M.NOME AS NOMEMODALIDADE,
    GROUP_CONCAT(
        DISTINCT CONCAT(K.NOME, ' - ', KI.NOME, ' - ', KIM.NOME)
        ORDER BY K.NOME, KI.NOME, KIM.NOME ASC 
        SEPARATOR ' | '
    ) AS DESCRICAO_KITS,
    IPE.CODINSCRICAO_PESSOA,
    IPE.APELIDO,
    IPE.UUID AS UUID_INSCRICAO_PESSOA,
    C.NOME AS NOMECATEGORIA,
    P.CODPAGAMENTO,
    C.NOME AS NOMECATEGORIA
FROM 
    PAGAMENTO AS P
    INNER JOIN INSCRICAO_PAGAMENTO AS IP ON IP.CODPAGAMENTO = P.CODPAGAMENTO
    INNER JOIN INSCRICAO AS I ON I.CODINSCRICAO = IP.CODINSCRICAO
    INNER JOIN INSCRICAO_PESSOA AS IPE ON I.CODINSCRICAO = IPE.CODINSCRICAO
    INNER JOIN PESSOA AS PE ON PE.CODPESSOA = IPE.CODPESSOA_INSCRICAO
    INNER JOIN MODALIDADE AS M ON M.CODMODALIDADE = I.CODMODALIDADE
    INNER JOIN CATEGORIA C ON C.CODCATEGORIA = I.CODCATEGORIA
    LEFT JOIN INSCRICAO_KIT_ITEM_MODELO AS IKIM ON IPE.CODINSCRICAO = IKIM.CODINSCRICAO_PESSOA
    LEFT JOIN KIT_ITEM_MODELO AS KIM ON IKIM.CODKIT_ITEM_MODELO = KIM.CODKIT_ITEM_MODELO
    LEFT JOIN KIT_ITEM AS KI ON KIM.CODKIT_ITEM = KI.CODKIT_ITEM
    LEFT JOIN KIT AS K ON KI.CODKIT = K.CODKIT
    INNER JOIN EVENTO AS E ON E.CODEVENTO = M.CODEVENTO
WHERE 
    P.CODPAGAMENTO = ? AND P.EXCLUIDO=0
GROUP BY 
    PE.CODPESSOA, I.CODINSCRICAO, M.CODMODALIDADE
ORDER BY 
    PE.NOME";
        $this->executeSQL($sql, [$codPedido]);
        return $this->fetchArrayObj();
    }

    public function buscarInscricaoPessoa($codInscricaoPessoa)
    {
        $sql = "SELECT 
    PE.*,
    I.*, 
    I.SITUACAO AS SITUACAO_INSCRICAO,
    M.*,
    PE.NOME AS NOMEATLETA,
    M.NOME AS NOMEMODALIDADE,
    IPE.CODINSCRICAO_PESSOA,
    IPE.APELIDO,
    IPE.UUID AS UUID_INSCRICAO_PESSOA,
    I.UUID AS UUID_INSCRICAO,
    P.UUID AS UUID_PAGAMENTO,
    E.IMAGEM_BANNER_MOBILE,
    E.IMAGEM_BANNER,
    E.URL,
    E.NOME AS NOMEEVENTO,
    E.DATA_EVENTO,
    C.NOME AS NOMECIDADE,
    C.UF
FROM 
    PAGAMENTO AS P
    INNER JOIN INSCRICAO_PAGAMENTO AS IP ON IP.CODPAGAMENTO = P.CODPAGAMENTO
    INNER JOIN INSCRICAO AS I ON I.CODINSCRICAO = IP.CODINSCRICAO
    INNER JOIN INSCRICAO_PESSOA AS IPE ON I.CODINSCRICAO = IPE.CODINSCRICAO
    INNER JOIN PESSOA AS PE ON PE.CODPESSOA = IPE.CODPESSOA_INSCRICAO
    INNER JOIN MODALIDADE AS M ON M.CODMODALIDADE = I.CODMODALIDADE
    INNER JOIN EVENTO AS E ON E.CODEVENTO = M.CODEVENTO
    INNER JOIN ENDERECO EN ON E.CODENDERECO_EVENTO = EN.CODENDERECO
    INNER JOIN CIDADE C ON EN.CODCIDADE = C.CODCIDADE
WHERE 
    IPE.UUID = ? AND P.EXCLUIDO=0
GROUP BY 
    PE.CODPESSOA, I.CODINSCRICAO, M.CODMODALIDADE
ORDER BY 
    PE.NOME";
        $this->executeSQL($sql, [$codInscricaoPessoa]);
        return $this->fetchArrayObj()[0] ?? [];
    }

    public function buscarPessoaCodpessoa($cpfcnpj)
    {

        try {
            $sql = "SELECT P.CODPESSOA, P.CODENDERECO, P.IMAGEM, P.TIPOPESSOA, P.NOME, P.TELEFONE, P.EMAIL,
                        TIMESTAMPDIFF(YEAR, PF.DATANASCIMENTO, CURDATE()) AS IDADE, 
                        PF.CODPESSOA_FISICA, PF.DATANASCIMENTO, PF.CPF, PF.SEXO
                    FROM PESSOA AS P
                    INNER JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                    INNER JOIN SI_USUARIO AS U on U.CODPESSOA=P.CODPESSOA
                    WHERE P.CODPESSOA=? AND U.EXCLUIDO=0 AND P.EXCLUIDO=0 AND PF.EXCLUIDO=0";
            $params = array($cpfcnpj);
            $result = $this->executeSQL($sql, $params);
            if ($this->rowCount($result) > 0) {
                return $this->fetchOneObj();
            } else {
                return null;
            }
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscarPessoaFisicaCodPessoa($id)
    {

        try {
            $sql = "SELECT P.CODPESSOA, P.CODENDERECO, P.IMAGEM, P.TIPOPESSOA, P.NOME, P.TELEFONE, P.EMAIL,
                        TIMESTAMPDIFF(YEAR, PF.DATANASCIMENTO, CURDATE()) AS IDADE, 
                        PF.CODPESSOA_FISICA, PF.DATANASCIMENTO, PF.CPF, PF.SEXO
                    FROM PESSOA AS P
                    INNER JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                    WHERE P.CODPESSOA=? AND P.EXCLUIDO=0 AND PF.EXCLUIDO=0";
            $params = array($id);
            $result = $this->executeSQL($sql, $params);
            if ($this->rowCount($result) > 0) {
                return $this->fetchOneObj();
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
            $sql = "SELECT P.CODPESSOA, P.NOME, PF.CPF, P.EMAIL, P.EXCLUIDO, U.CODUSUARIO,
                    TIMESTAMPDIFF(YEAR, PF.DATANASCIMENTO, CURDATE()) AS IDADE
                    FROM PESSOA AS P 
                    INNER JOIN PESSOA_FISICA AS PF ON PF.CODPESSOA=P.CODPESSOA
                    INNER JOIN SI_USUARIO AS U on P.CODPESSOA=U.CODPESSOA
                    WHERE U.CODUSUARIO=? AND U.EXCLUIDO='0' AND P.EXCLUIDO=0";
            $params = array($id);
            $result = $this->executeSQL($sql, $params);
            if ($this->rowCount($result) > 0) {
                return $this->fetchArrayObj($result);
            } else {
                return null;
            }
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function buscarUsuarioModelId($codusuario): ?UsuarioModel
    {
        try {
            $sql = "SELECT * FROM PESSOA AS P
                    INNER JOIN SI_USUARIO U on U.CODPESSOA = P.CODPESSOA
                    WHERE U.CODUSUARIO=? AND P.EXCLUIDO!='1'";
            $params = array($codusuario);
            $result = $this->executeSQL($sql, $params);
            if ($this->rowCount($result) > 0) {
                return $this->fetchOneClass($result, "UsuarioModel");
            } else {
                return null;
            }
        } catch (\Error $e) {
            throw new \Error($e->getMessage());
        }
    }

    public function updateSenha(UsuarioModel $usuarioModel): bool
    {


        $sql = "UPDATE SI_USUARIO SET senha='" . $usuarioModel->getSENHA() . "' WHERE CODUSUARIO='" . $usuarioModel->getCODUSUARIO() . "'";;
        if ($this->executeSQL($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public function buscarPessoa($codpessoa)
    {
        try {
            $sql = "SELECT P.CODPESSOA, P.CODENDERECO, P.IMAGEM, P.TIPOPESSOA, P.NOME, P.TELEFONE, P.EMAIL, P.CRIADO_EM, P.ALTERADO_EM,
                    COALESCE(PF.CPF, PJ.CNPJ) AS CPFCNPJ
                    FROM PESSOA AS P
                    LEFT JOIN PESSOA_JURIDICA AS PJ on PJ.CODPESSOA=P.CODPESSOA
                    LEFT JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                    WHERE P.EXCLUIDO='0' AND P.CODPESSOA=?";

            $this->executeSQL($sql, [$codpessoa]);
            return $this->fetchArrayObj();
        } catch (Error $e) {
            return $e;
        }
    }

    public function buscarPessoaInscricao($uuid, $codpessoa)
    {
        try {
            $sql = "SELECT P.CODPESSOA, P.CODENDERECO, P.IMAGEM, P.TIPOPESSOA, P.NOME, P.TELEFONE, P.EMAIL, P.CRIADO_EM, P.ALTERADO_EM,
                    COALESCE(PF.CPF, PJ.CNPJ) AS CPFCNPJ,
                    U.CODUSUARIO
                    FROM PESSOA AS P
                    INNER JOIN INSCRICAO_PESSOA AS IP ON IP.CODPESSOA_INSCRICAO=P.CODPESSOA
                    INNER JOIN INSCRICAO AS I ON I.CODINSCRICAO=IP.CODINSCRICAO AND I.UUID=?
                    LEFT JOIN PESSOA_JURIDICA AS PJ on PJ.CODPESSOA=P.CODPESSOA
                    LEFT JOIN PESSOA_FISICA PF ON PF.CODPESSOA=P.CODPESSOA
                    LEFT JOIN SI_USUARIO AS U ON U.CODPESSOA=P.CODPESSOA
                    WHERE P.EXCLUIDO='0' AND P.CODPESSOA=?";

            $this->executeSQL($sql, [$uuid, $codpessoa]);
            return $this->fetchArrayObj();
        } catch (Error $e) {
            return $e;
        }
    }


    public function inserirPessoaFisica(EnderecoModel $endereco, PessoaModel $pessoa, PessoaFisicaModel $pessoaFisica)
    {

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
            } // SE JA EXISTIR É PARA ATUALIZAR AS INFORMACOES EXISTENTES
            else {
                $pessoaObj = $this->fetchOneObj();
                $codpessoa = $pessoaObj->CODPESSOA;

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
            }

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

    public function atualizarPessoaFisica(EnderecoModel $endereco, PessoaModel $pessoa, PessoaFisicaModel $pessoaFisica)
    {

        try {
            $this->beginTransaction();

            $pessoa->setNOME((new FuncoesLib())->textoPrimeiraLetraMaiusculoCadaPalavra($pessoa->getNOME()));
            $pessoa->setEMAIL((new FuncoesLib())->textoPrimeiraLetraMaiusculoCadaPalavra($pessoa->getEMAIL()));


            $resultPessoa = $this->executeSQL("UPDATE PESSOA  SET NOME = ?, TELEFONE = ?, EMAIL = ?, IMAGEM=? WHERE CODPESSOA = ? ", [$pessoa->getNOME(), $pessoa->getTELEFONE(), $pessoa->getEMAIL(), $pessoa->getIMAGEM() ?? "default.png", $pessoa->getCODPESSOA()]);
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

            $resultEndereco = $this->executeSQL(
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

            $this->commitTransaction();
            return [
                "error" => false,
                "message" => "Atualizado com sucesso",
                "codpessoa" => $pessoa->getCODPESSOA()
            ];
        } catch (\Error $th) {
            $this->rollBackTransaction();
            return $th;
        }
    }

    public function inserirPessoaJuridica(EnderecoModel $endereco, PessoaModel $pessoa, PessoaJuridicaModel $pessoaJuridica)
    {

        try {
            $this->beginTransaction();

            // BUSCA SE A PESSOA JURÍDICA ESTÁ CADASTRADA
            $this->executeSQL('SELECT P.CODPESSOA, CODENDERECO, TIPOPESSOA, NOME, TELEFONE, EMAIL, CRIADO_EM, ALTERADO_EM 
                FROM PESSOA AS P 
                INNER JOIN PESSOA_JURIDICA PF ON PF.CODPESSOA=P.CODPESSOA
                WHERE PF.CNPJ = ? AND PF.EXCLUIDO = 0 AND P.EXCLUIDO= 0', [$pessoaJuridica->getCNPJ()]);
            $objPessoa = $this->fetchArrayObj();

            // SE NAO TIVER CADASTRADO
            if (empty($objPessoa)) {
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
                $result = $this->executeSQL('INSERT INTO PESSOA_JURIDICA (CODPESSOA, NOMEFANTASIA, CNPJ) VALUES (?, ?, ?)', [$codpessoa, $pessoaJuridica->getNOMEFANTASIA(), $pessoaJuridica->getCNPJ()]);
                if (!$result) {
                    $this->rollBackTransaction();
                    return [
                        "error" => true,
                        "message" => "Erro ao cadastrar pessoa jurídica!",
                        "codpessoa" => null
                    ];
                }
            } else {
                $codpessoa = $objPessoa[0]->CODPESSOA;
            }

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

    public function atualizarPessoaJuridica(EnderecoModel $endereco, PessoaModel $pessoa, PessoaJuridicaModel $pessoaJuridica)
    {

        try {
            $this->beginTransaction();

            // CADASTRAR ENDEREÇO
            $result = $this->executeSQL("UPDATE ENDERECO SET CODCIDADE=?, CEP=?, LOGRADOURO=?, NUMERO=?, BAIRRO=?, COMPLEMENTO=? WHERE CODENDERECO=?", [$endereco->getCODCIDADE(), $endereco->getCEP(), $endereco->getLOGRADOURO(), $endereco->getNUMERO(), $endereco->getBAIRRO(), $endereco->getCOMPLEMENTO(), $endereco->getCODENDERECO()]);
            if (!$result) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao atualizar endereço!",
                    "codpessoa" => $pessoa->getCODPESSOA()
                ];
            }

            // CADASTRAR PESSOA
            $result = $this->executeSQL("UPDATE PESSOA SET TIPOPESSOA=?, NOME=?, TELEFONE=?, EMAIL=? WHERE CODPESSOA=?", [$pessoa->getTIPOPESSOA(), $pessoa->getNOME(), $pessoa->getTELEFONE(), $pessoa->getEMAIL(), $pessoa->getCODPESSOA()]);
            if (!$result) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao atualizar pessoa!",
                    "codpessoa" => $pessoa->getCODPESSOA()
                ];
            }

            // PESSOA JURIDICA
            $result = $this->executeSQL('UPDATE PESSOA_JURIDICA SET NOMEFANTASIA=?, CNPJ=? WHERE CODPESSOA_JURIDICA=?', [$pessoaJuridica->getNOMEFANTASIA(), $pessoaJuridica->getCNPJ(), $pessoaJuridica->getCODPESSOAJURIDICA()]);
            if (!$result) {
                $this->rollBackTransaction();
                return [
                    "error" => true,
                    "message" => "Erro ao cadastrar pessoa jurídica!",
                    "codpessoa" => $pessoa->getCODPESSOA()
                ];
            }

            $this->commitTransaction();
            return [
                "error" => false,
                "message" => "Atualizado com sucesso",
                "codpessoa" => $pessoa->getCODPESSOA()
            ];
        } catch (\Error $th) {
            $this->rollBackTransaction();
            return $th;
        }
    }

    public function buscarOnePessoaId($codpessoa)
    {
        $sql = "SELECT P.CODPESSOA, P.NOME, P.EMAIL
                    FROM PESSOA AS P
                    WHERE P.CODPESSOA = ? AND P.EXCLUIDO=0";

        $params = array($codpessoa);
        $result = $this->executeSQL($sql, $params);
        if (!empty($result)) {
            return $this->fetchOneObj();
        } else {
            return null;
        }
    }

    public function buscarAll()
    {
        $sql = "SELECT P.CODPESSOA, P.NOME, P.EMAIL
                    FROM PESSOA AS P
                    WHERE P.EXCLUIDO=0";

        $result = $this->executeSQL($sql);
        if (!empty($result)) {
            return $this->fetchArrayObj();
        } else {
            return null;
        }
    }

    public function inserirPessoaLote(mixed $data)
    {
        try {
            $this->beginTransaction();
            $pessoaFisicaDao = new PessoaFisicaDao();
            $enderecoDao = new EnderecoDao();

            $pessoaFisicaDao->setInstance($this->getInstance());
            $enderecoFisica = $enderecoDao->setInstance($this->getInstance());

            $pessoaArray = [];
            foreach ($data as $key => $item) {
                // Array para guardar erros de validação
                $errors = [];

                // Validação do NOME
                if (empty($item["NOME"])) {
                    $errors[] = "Nome é obrigatório";
                }

                // Validação do EMAIL
                if (empty($item["EMAIL"])) {
                    $errors[] = "Email é obrigatório";
                } elseif (!filter_var($item["EMAIL"], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Email inválido";
                }

                // Validação do TELEFONE
                if (empty($item["TELEFONE"])) {
                    $errors[] = "Telefone é obrigatório";
                } else {
                    $telefoneNumerico = (new FuncoesLib())->removeCaracteres($item["TELEFONE"]);
                    if (strlen($telefoneNumerico) < 10 || strlen($telefoneNumerico) > 11) {
                        $errors[] = "Telefone inválido";
                    }
                }

                // Validação da DATA DE NASCIMENTO
                if (empty($item["DATANASCIMENTO"])) {
                    $errors[] = "Data de nascimento é obrigatória";
                } else {
                    $dataNascimentoFormatada = (new FuncoesLib())->formatDataBanco(trim($item["DATANASCIMENTO"]));
                    if (!$dataNascimentoFormatada || $dataNascimentoFormatada > date('Y-m-d')) {
                        $errors[] = $dataNascimentoFormatada." Data de nascimento inválida";
                    }
                }

                // Validação do CPF
                if (empty($item["CPF"])) {
                    $errors[] = "CPF é obrigatório";
                } else {
                    $cpf = (new FuncoesLib())->removeCaracteres($item["CPF"]);
                    if (strlen($cpf) != 11) {
                        $errors[] = "CPF inválido";
                    }
                }

                // Validação do SEXO
                if (empty($item["SEXO"])) {
                    $errors[] = "Sexo é obrigatório";
                } else {
                    $sexo = strtoupper(substr($item["SEXO"], 0, 1));
                    if ($sexo != "M" && $sexo != "F") {
                        $errors[] = "Sexo inválido (deve ser M ou F)";
                    }
                }

                // Se encontrou erros, adiciona ao array de resposta e continua para o próximo item
                if (!empty($errors)) {
                    $pessoaArray[] = [
                        "error" => true,
                        "message" => implode(", ", $errors),
                        "data" => $item
                    ];
                    continue;
                }

                // Verifica se a pessoa já existe pelo CPF
                $resultPessoa = (new PessoaFisicaDao())->buscarPessoaCPF((new FuncoesLib())->removeCaracteres($item["CPF"]));
                if (!empty($resultPessoa)) {
                    $pessoaArray[] = [
                        "error" => true,
                        "message" => "CPF já cadastrado no sistema",
                        "data" => $item
                    ];
                    continue;
                }

                $resultCidade = (new CidadeDao())->buscar(["NOME" => empty($item["CIDADE"]) ? "Palmas" : $item["CIDADE"], "UF" => empty($item["ESTADO"]) ? "TO" : $item["ESTADO"]]);
                if (empty($resultCidade)) {
                    $pessoaArray[] = [
                        "error" => true,
                        "message" => "Cidade não encontrada",
                        "data" => $item
                    ];
                    continue;
                }

                $cidadeObj = $resultCidade[0];
                $endereco = [
                    "CODCIDADE" => $cidadeObj->CODCIDADE,
                ];
                $enderecoDao->insertArray($endereco);
                $codendereco = $this->lastInsertId();

                $telefone = (new FuncoesLib())->removeCaracteres($item["TELEFONE"]);
                $telefone = (new FuncoesLib())->formatTextoMask($telefone, "(##) #####-####");
                $nome = (new FuncoesLib())->textoPrimeiraLetraMaiusculoCadaPalavra($item["NOME"]);
                $pessoa = [
                    "NOME" => $nome,
                    "TELEFONE" => $telefone,
                    "EMAIL" => $item["EMAIL"],
                    "TIPOPESSOA" => "F",
                    "CODENDERECO" => $codendereco,
                ];
                $this->insertArray($pessoa);
                $codpessoa = $this->lastInsertId();

                $pessoaFisica = [
                    "DATANASCIMENTO" => $dataNascimentoFormatada,
                    "CPF" => $cpf,
                    "SEXO" => $sexo,
                    "CODPESSOA" => $codpessoa
                ];
                $pessoaFisicaDao->insertArray($pessoaFisica);

                $pessoaArray[] = [
                    "error" => false,
                    "message" => "Pessoa cadastrada com sucesso",
                    "data" => [
                        "CODPESSOA" => $codpessoa,
                        "NOME" => $nome,
                        "EMAIL" => $item["EMAIL"],
                        "TELEFONE" => $telefone,
                        "DATANASCIMENTO" => $dataNascimentoFormatada,
                        "CPF" => $cpf,
                        "SEXO" => $sexo,
                        "CIDADE" => empty($item["CIDADE"]) ? "Palmas" : $item["CIDADE"],
                        "ESTADO" => empty($item["ESTADO"]) ? "TO" : $item["ESTADO"]
                    ]
                ];
            }

            $this->commitTransaction();
            return [
                "error" => false,
                "message" => "Processamento concluído",
                "data" => $pessoaArray
            ];
        } catch (\Exception $th) {
            $this->rollBackTransaction();
            return [
                "error" => true,
                "message" => $th->getMessage(),
                "data" => null
            ];
        }
    }
}
