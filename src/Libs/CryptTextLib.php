<?php


namespace App\Libs;


class CryptTextLib
{
    private const CIPHER = 'aes-256-cbc';
    private const KEY_LENGTH = 16; // Define o tamanho da chave para AES-256

    private string $key;

    public function __construct(string $key = '0123456789abcdef')
    {
        $this->key = $this->formatKey($key);
    }

    public function encrypt(string $text): string
    {
        $key = $this->getKey();
        $iv = $this->getIv();

        $encrypted = openssl_encrypt($text, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            throw new RuntimeException("Falha na criptografia do texto.");
        }

        return base64_encode($encrypted);
    }

    public function decrypt(string $code): string
    {
        $key = $this->getKey();
        $iv = $this->getIv();

        $decoded = base64_decode($code, true);
        if ($decoded === false) {
            throw new InvalidArgumentException("Código inválido para decodificação.");
        }

        $decrypted = openssl_decrypt($decoded, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) {
            throw new RuntimeException("Falha na descriptografia do texto.");
        }

        return rtrim($decrypted, "\0");
    }

    private function getIv(): string
    {
        $ivlen = openssl_cipher_iv_length(self::CIPHER);
        return substr(hash('sha256', $this->getKey()), 0, $ivlen);
    }

    private function getKey(): string
    {
        return $this->key;
    }

    private function formatKey(string $key): string
    {
        if (strlen($key) < self::KEY_LENGTH) {
            throw new InvalidArgumentException("A chave deve ter pelo menos " . self::KEY_LENGTH . " caracteres.");
        }
        return substr(hash('sha256', $key), 0, 32);
    }
}