<?php
namespace App\Daos;

use App\Libs\FuncoesLib;
use App\Libs\SessionLib;
use BMorais\Database\Crud;
use BMorais\Database\CrudBuilder;

class LogDao extends CrudBuilder {

    public function __construct()
    {
        $this->setTableName("SI_LOG");
        $this->setClassModel("LogModel");
    }

    public function salvaLog($acao, $codservico = null, $code = null){
        $ip             = (new FuncoesLib())->pegaIpUsuario();
        $acao           = str_replace("'","",$acao);
        $datalog        = date("Y-m-d H:i:s");
        $codpessoa      = SessionLib::getValue('CODPESSOA');
        $url            = $_SERVER['REQUEST_URI'];;

        $sql = "INSERT INTO SI_LOG (CODSERVICO, CODPESSOA, ACAO, DESCRICAO, IP, URL, DATALOG) VALUES (?,?,?,?,?,?,?)";
        $this->executeSQL($sql,[$codservico, $codpessoa, $acao, $code, $ip, $url, $datalog]);
    }
}