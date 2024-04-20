<?php
namespace App\Libs;

use App\Models\UsuarioModel;

/**
 * Created by PhpStorm.
 * User: Bruno Morais
 * Email: brunomoraisti@gmail.com
 * Date: 13/06/2023
 * Time: 15:17
 */
class SessionLib
{
    protected static $nomeSessao = "SESSION-APP";

    public function __construct()
    {
    }

    public static function start(){
        @ob_start();
        session_name(self::$nomeSessao);
        session_start();
    }

    private static function end(){
        session_write_close();
        ob_end_flush();
    }

    public static function apagaSessao()
    {
        self::start();

        session_unset(); // Eliminar todas as vari�veis da sess�o
        session_destroy(); // Destruir a sess�o

        self::end();
    }

    public static function setValue(string $name, $value)
    {
        self::start();
        $_SESSION[$name]= $value;
        self::end();
    }

    public static function getValue($name)
    {
        $valorRetorno = null;
        self::start();
        if (isset($_SESSION[$name]))
            $valorRetorno = $_SESSION[$name];
        self::end();

        return $valorRetorno;
    }

    public static function apagaCampo($nomeCampo)
    {
        self::start();
        unset($_SESSION[$nomeCampo]);
        self::end();
    }

    /*public function verificaLoginSessao()
    {
        $email = $this->pegarCampo("EMAIL");

        if ((!isset($email)) || empty($email)) {
            header("location: /login/logoff");
            exit;
        } else {
            return true;
        }
    }*/

    public static function setDataSession(UsuarioModel $dados){

        self::apagaSessao();

        self::setValue("CODUSUARIO", $dados->getCODUSUARIO());
        self::setValue("CODPESSOA", $dados->getCODPESSOA());
        self::setValue("CPF", $dados->getCPF());
        self::setValue("NOME", $dados->getNOME());
        self::setValue("EMAIL", $dados->getEMAIL());
        self::setValue("TELEFONE", $dados->getEMAIL());
        self::setValue("SEXO", $dados->getSEXO());
        self::setValue("DATANASCIMENTO", $dados->getDATANASCIMENTO());
        self::setValue("PRIMEIRONOME", explode(" ", $dados->getNOME())[0]);

    }

    public static function getDataSession(){

        $dados['CODPESSOA'] = self::getValue("CODPESSOA");
        $dados['CODUSUARIO'] = self::getValue("CODUSUARIO");
        $dados['CPF'] = self::getValue("CPF");
        $dados['NOME'] = self::getValue("NOME");
        $dados['EMAIL'] = self::getValue("EMAIL");
        $dados['TELEFONE'] = self::getValue("TELEFONE");
        $dados['DATANASCIMENTO'] = self::getValue("DATANASCIMENTO");
        $dados['PRIMEIRONOME'] = self::getValue("PRIMEIRONOME");
        $dados['REDIRECIONA'] = self::getValue("REDIRECIONA");

        return $dados;
    }
}
