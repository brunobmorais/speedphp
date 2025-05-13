<?php

namespace App\Daos;

use App\Libs\SessionLib;
use BMorais\Database\Crud;

class SiNotificacaoDao extends Crud
{
    public function __construct()
    {

        $this->setTableName("SI_NOTIFICACAO");
        $this->setClassModel("");
    }

    //** ARRAY DE EXEMPLO
    // 'title' => 'Ferias Publicadas em Ficha Individual',
    // 'message' => 'Lembre-se de assinar o Livro de Ferias',
    // 'login' => CPF DO MILITAR Q SERA AVISADO,
   // 'link' => 'mods/admi/cdpe/feri/'

    public function buscarNotificacoesUsuario($limit = '') {
        $codpessoa = SessionLib::getValue("CODPESSOA");

        $sql = "SELECT * FROM SI_NOTIFICACAO WHERE CODPESSOA=? ORDER BY LIDO ASC, DATAHORA DESC";
        if (!empty($limit)) {
            $sql .= " LIMIT {$limit}";
        }

        $this->executeSQL($sql,[$codpessoa]);
        return $this->fetchArrayObj();
    }

    public function buscarNotificacoesNaoLidaUsuario($limit = '') {
        $codpessoa = SessionLib::getValue("CODPESSOA");

        $sql = "SELECT * FROM SI_NOTIFICACAO WHERE CODPESSOA=? AND LIDO=0 ORDER BY LIDO ASC, DATAHORA DESC";
        if (!empty($limit)) {
            $sql .= " LIMIT {$limit}";
        }

        $this->executeSQL($sql,[$codpessoa]);
        return $this->fetchArrayObj();
    }

    public function novaNotificacao(array $array)
    {
        try {
            $codpessoaCadastro = SessionLib::getValue("CODPESSOA")??null;
            $query = "INSERT INTO SI_NOTIFICACAO (CODPESSOA_CADASTRO, TITLE, MESSAGE, CODPESSOA, LINK, DATAHORA, TIPO) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $link = $array['link'];
            $values = [$codpessoaCadastro, $array['title'], $array['message'], $array['codpessoa'], $link, date('Y-m-d H:i:s'), 1];
            $data = $this->executeSQL($query, $values);
            return $data ;
        }catch (\Error $e){
            return $e;
        }

    }

    public function RecursosHumanos(array $array)
    {
        $query = "INSERT INTO SI_NOTIFICACAO (TITLE, MESSAGE, CODPESSOA, LINK, DATAHORA, TIPO) VALUES (?, ?, ?, ?, ?,?)";
        $link = "https://". $_SERVER['HTTP_HOST'] ."/".$array['link'];
        $values = [$array['title'], $array['message'], $array['codpessoa'], $link, date('Y-m-d H:i:s'), 0];
        $data = $this->executeSQL($query, $values);
        return $data ;
    }

}
