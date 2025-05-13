<?php
namespace App\Daos;
use App\Enums\LocalAcesso;
use App\Libs\FuncoesLib;
use App\Libs\SessionLib;
use BMorais\Database\CrudBuilder;

class AcessoDao extends CrudBuilder
{
    public function __construct()
    {
        $this->setTableName("ACESSO");
        $this->setClassModel("AcessoModel");
    }


    public function setVisita(LocalAcesso $local, $codevento = null)
    {
        $codpessoa = SessionLib::getValue("CODPESSOA")??null;
        $this->insertArray([
            "CODEVENTO" => $codevento,
            "CODPESSOA" => $codpessoa,
            "LOCAL" => $local->value,
            "IP" => (new FuncoesLib())->pegaIpUsuario()
        ]);
    }

    public function getTotalAcessoEvento()
    {
        $sql = "SELECT 
                    COUNT(EA.CODACESSO) AS TOTAL_ACESSOS_GERAL,
                    COUNT(CASE 
                        WHEN DATE(EA.CRIADO_EM) = CURDATE() THEN EA.CODACESSO 
                    END) AS TOTAL_ACESSOS_HOJE
                FROM ACESSO AS EA
                WHERE EA.CODEVENTO = ? AND EA.LOCAL = ? ";
        $this->executeSQL($sql, [SessionLib::getValue("CODEVENTO"), LocalAcesso::EVENTO->value]);
        return $this->fetchArrayObj()[0]??[];
    }
}