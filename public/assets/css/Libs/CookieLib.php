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
        $day = time() + (86400 * $durationDays);

        // Melhoria 1: Verificação mais robusta para desenvolvimento local
        $isLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) ||
            strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0;

        // Melhoria 2: Domain mais específico para produção
        $domain = $isLocal ? false : $_SERVER['HTTP_HOST'];

        // Melhoria 3: Secure apenas em HTTPS (exceto localhost)
        $secure = $isLocal ? false : true;

        $arr_cookie_options = array(
            'expires' => $day,
            'path' => '/',
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => 'Lax'
        );

        return setcookie($nome, $valor, $arr_cookie_options);
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
