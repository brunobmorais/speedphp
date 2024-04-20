<?php
namespace App\Daos;
use BMorais\Database\CrudBuilder;

class GpArquivoDao extends CrudBuilder
{
    public function __construct()
    {
        $this->setTableName("GP_ARQUIVO");
        $this->setClassModel("GpArquivoModel");
    }

    public function buscarCodfuncionario(int $codfuncionario){
        return $this->select('*',  'GP LEFT JOIN GP_TIPO_ARQUIVO AS T ON T.CODTIPOARQUIVO = GP.CODTIPOARQUIVO  WHERE GP.CODFUNCIONARIO = ?  AND GP.EXCLUIDO = 0 ', [ $codfuncionario]); 
    }

    public function buscarArquivoId($codarquivo)
    {
        return $this->select('*',"WHERE CODARQUIVO=?",[$codarquivo]);
    }

    public function buscarNomeArquivo($codarquivo) {
        $sql = "SELECT A.CODARQUIVO, A.CODPESSOA_CADASTRO, A.CODFUNCIONARIO, A.CODTIPOARQUIVO, A.ARQUIVO, A.CRIADO_EM, A.ALTERADO_EM,
                    TA.NOME AS NOMETIPOARQUIVO, TA.SIGLA AS SIGLATIPOARQUIVO
                    FROM GP_ARQUIVO AS A
                    INNER JOIN GP_TIPO_ARQUIVO AS TA ON TA.CODTIPOARQUIVO=A.CODTIPOARQUIVO
                    WHERE A.CODARQUIVO = ?";
        $this->executeSQL($sql, [$codarquivo]);
        $obj = $this->fetchArrayObj();
        if (empty($obj))
            return [];
        return $obj[0];

    }
}