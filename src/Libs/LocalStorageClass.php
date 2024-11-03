<?php
namespace App\Libs;


use Application\models\CookieModel;
use Application\models\DispositivosModel;

/**
 * Created by PhpStorm.
 * User: Bruno Morais
 * Email: brunosm08@gmail.com
 * Date: 13/06/2017
 * Time: 15:17
 */


// ESSA CLASSE SÓ DEVE SER CHAMADA APÓS TODAS HTML

class LocalStorageClass
{

    public function __construct()
    {

    }

    public function pegarCampo($nome){
        $valor = 10;
        if (!empty("<script>localStorage.getItem('{$nome}')</script>"))
            $valor = "<script>localStorage.getItem('{$nome}')</script>";
        echo $valor;
    }

    public function gravarCampo($nome,$valor){
        echo "<script>localStorage.setItem('{$nome}','{$valor}');</script>";
    }

    public function apagarCampo($nome){
        echo "<script>localStorage.removeItem('{$nome}')</script>";
    }


    public function apagarTudo(){
        echo "<script>localStorage.clear()</script>";
    }

}
