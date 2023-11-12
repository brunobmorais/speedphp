<?php
namespace App\Libs;

/**
 * Created by PhpStorm.
 * User: Bruno Morais
 * Email: brunomoraisti@gmail.com
 * Date: 13/06/2023
 * Time: 15:17
 */
class CookieLib
{

    public function __construct()
    {

    }

    public static function getValue($nome){
        $valor = null;
        if (!empty($_COOKIE[$nome]))
            $valor = $_COOKIE[$nome];
        return $valor;
    }

    public static function setValue($nome, $valor){

        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        //setcookie('cross-site-cookie', 'name', ['samesite' => 'None', 'secure' => true]);
        setcookie($nome, $valor , (time()+(365 * 24 * 3600)), "/", $domain);
        return true;

        //setcookie($nome, $valor , (time()+(365 * 24 * 3600)), "/; SameSite=None; Secure"); // < 7.3
        //setcookie($nome, $valor , ['samesite' => 'None', 'secure' => true]); // >= 7.3

    }

    public static function deleteValue($nome){
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        setcookie($nome, '' , (time()-(365 * 24 * 3600)), "/", $domain);
    }

    public static function verificaCookieLogin(){

        if (!empty($_COOKIE['HASH'])){
            return true;
        } else {
            header("location: /login/logoff");
            exit;
        }

    }

    public static function gravarDadosNoCookie($dados){

        self::setValue("HASH", $dados[0]->HASH);

    }

    public static function apagarCookies(){
        self::deleteValue('HASH');
    }
}
