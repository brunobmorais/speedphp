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

    public static function setValue($nome, $valor, $durationDays = 1, $httponly = false) {
        // Corrige o cálculo de expires
        $day = time() + (86400 * $durationDays);

        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        $arr_cookie_options = array (
            'expires' => $day,
            'path' => '/',
            'domain' => $domain,
            'secure' => true,
            'httponly' => $httponly,
            'samesite' => 'None'
        );

        setcookie($nome, $valor, $arr_cookie_options);
        return true;
    }

    public static function deleteValue($nome) {
        $domain = ($_SERVER['HTTP_HOST'] !== 'localhost') ? $_SERVER['HTTP_HOST'] : false;

        setcookie($nome, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => $domain,
            'secure' => true,
            'httponly' => false,
            'samesite' => 'None'
        ]);
    }
}
