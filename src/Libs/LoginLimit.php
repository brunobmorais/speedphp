<?php

namespace App\Libs;

class LoginLimit
{
    public string $name = 'LOGIN-ACCESS';
    public string $ip;

    public function __construct() {
        $this->ip = $_SERVER['REMOTE_ADDR'];
    }

    private function cookieExist(
        int $secondsToExpire
    ) {
        if (isset($_COOKIE[$this->name])) {
            SessionLib::apagaCampo('LOGIN_LIMIT_'.$this->ip);
            return true;
        } else {
            SessionLib::apagaCampo($this->name);
            return false;
        }
    }

    private function incrementsAttempts()
    {
        $session = SessionLib::getValue('LOGIN_LIMIT_'.$this->ip);
        if (isset($session)) {
            SessionLib::setValue('LOGIN_LIMIT_'.$this->ip, SessionLib::getValue('LOGIN_LIMIT_'.$this->ip)+1);
        } else {
            SessionLib::setValue('LOGIN_LIMIT_'.$this->ip,"1");
        }
    }

    private function createCookie(
        int $maxAttempts,
        int $secondsToExpire
    ) {
        if (SessionLib::getValue('LOGIN_LIMIT_'.$this->ip) >= $maxAttempts) {
            setcookie($this->name, true, strtotime("+{$secondsToExpire} seconds"));
        }
    }

    public function check(
        int $maxAttempts = 4,
        int $secondsToExpire = 20
    ) {
        //echo SessionLib::getValue($this->name . '_limit'[$this->ip]);
        if ($this->cookieExist($secondsToExpire)) {
            return false;
        }

        $this->incrementsAttempts();
        $this->createCookie($maxAttempts, $secondsToExpire);
        return true;
    }
}