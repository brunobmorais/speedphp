<?php
namespace App\Daos;
use App\Libs\SessionLib;
use BMorais\Database\CrudBuilder;

class GpAndamentoDao extends CrudBuilder
{
    public function __construct()
    {
        $this->setTableName("GP_ANDAMENTO");
        $this->setClassModel("GpAndamentoModel");
    }

    public function andamentoByCodFuncionario(int $codfuncionario = null): ?array
    {

        $data = $this->select("*", "WHERE CODFUNCIONARIO=? AND EXCLUIDO=0 ORDER BY NUMERO DESC", [$codfuncionario]);
        if (empty($data)) {
            return null;
        }
        foreach ($data as $index => $item) {
            $data[$index]->ARQUIVOS = $this->andamentoArquivos($item->CODANDAMENTO);
        }

        return $data;

    }

    public function andamentoArquivos($codandamento)
    {
        $sql = "SELECT AA.CODARQUIVO, 
                    A.ARQUIVO, A.CODPESSOA_CADASTRO, A.CRIADO_EM,
                    T.NOME AS NOMETIPOARQUIVO 
                    FROM GP_ANDAMENTO_ARQUIVO AS AA
                    INNER JOIN GP_ARQUIVO A on AA.CODARQUIVO = A.CODARQUIVO
                    INNER JOIN GP_TIPO_ARQUIVO T on A.CODTIPOARQUIVO = T.CODTIPOARQUIVO
                    WHERE AA.CODANDAMENTO=? AND A.EXCLUIDO=0";
        $this->executeSQL($sql, [$codandamento]);
        $data = $this->fetchArrayObj();
        if (empty($data)) {
            return null;
        }
        return $data;
    }

    /**
     * @param $codfiscalizacao
     * @return string
     */
    public function numeroAndamento($codfiscalizacao): string
    {
        $sql = "SELECT MAX(NUMERO) AS NUMERO FROM GP_ANDAMENTO WHERE CODFUNCIONARIO=? AND EXCLUIDO=0";
        $this->executeSQL($sql, [$codfiscalizacao]);
        $data = $this->fetchArrayObj();
        if (empty($data)) {
            return 1;
        }
        return $data[0]->NUMERO+1;

    }

    /**
     * @param $codfuncionario
     * @param $titulo
     * @param $descricao
     * @param $responsavel
     * @param $tipo
     * @param $dataBanco
     * @return bool|int
     */
    public function insertAndamento($codfuncionario, $titulo,  $descricao= "Sem observações", $responsavel="", $tipo=1, $dataBanco = "")
    {
        $descricao = empty($descricao)?"Sem observações":$descricao;
        $dataBanco = empty($dataBanco)?Date("Y-m-d H:i"):$dataBanco;
        $numero = $this->numeroAndamento($codfuncionario);
        $responsavel = empty($responsavel)?SessionLib::getValue("NOME"):$responsavel;

        $sql = "INSERT INTO GP_ANDAMENTO (CODFUNCIONARIO, RESPONSAVEL, TITULO, DESCRICAO, NUMERO, TIPO, DATAANDAMENTO, EXCLUIDO) VALUES (?,?,?,?,?,?,?,?)";
        $result = $this->executeSQL($sql,[
            $codfuncionario,
            $responsavel,
            $titulo,
            $descricao,
            $numero,
            $tipo,
            $dataBanco,
            0
        ]);

        if (empty($result))
            return false;

        $codandamento = $this->lastInsertId();
        return $codandamento;
    }

    public function insertAndamentoArquivo($codandamento, $codarquivo): bool
    {
        $sql = "INSERT INTO GP_ANDAMENTO_ARQUIVO (CODANDAMENTO, CODARQUIVO) VALUES (?,?)";
        $result = $this->executeSQL($sql,[
            $codandamento,
            $codarquivo
        ]);

        if (empty($result))
            return false;

        return $this->lastInsertId();
    }


}