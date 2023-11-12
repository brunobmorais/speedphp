<?php


namespace App\Libs;


class CryptTextClass
{
    const SESS_CIPHER = 'aes-256-cbc';
    private $key = '0123456789abcdef'; #Same as in JAVA

    function __construct()
    {
    }

    function encrypt($text) {

        $key = $this->_getSalt();
        $iv = $this->_getIv();

        $encrypted = openssl_encrypt($text, self::SESS_CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        $encryptedSessionId = base64_encode($encrypted);
        return $encryptedSessionId;
    }

    function decrypt($code) {
        $key = $this->_getSalt();
        $iv = $this->_getIv();

        $decoded = base64_decode($code, TRUE);
        $decrypted = openssl_decrypt($decoded, self::SESS_CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        return rtrim($decrypted, '\0');
    }
    public function _getIv() {
        $ivlen = openssl_cipher_iv_length(self::SESS_CIPHER);
        return substr(md5($this->_getSalt()), 0, $ivlen);
    }

    public function _getSalt() {
        return $this->key;
    }

}