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
    protected const NOME_SESSAO = "SESSION-APP";

    public function __construct()
    {
    }

    public static function start(): void{
        if (session_status() === PHP_SESSION_NONE) {
            session_name(self::NOME_SESSAO);
            session_start();
            ob_start();
        }
    }

    private static function end(): void{
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
            ob_end_flush();
        }
    }

    public static function apagaSessao(): void
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
        self::start();
        $value = $_SESSION[$name] ?? null;
        self::end();
        return $value;
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

    public static function setDataSession(array $dados){

        self::apagaSessao();

        foreach ($dados as $key => $value) {
            self::setValue($key, $value);
        }
    }

    public static function getDataSession(array $keys = []){

        // Se $keys for vazio, busca todos os dados de sessão
        $keys = empty($keys) ? array_keys($_SESSION) : $keys;

        $dados = [];
        foreach ($keys as $key) {
            $dados[$key] = self::getValue($key);
        }

        return $dados;
    }
}