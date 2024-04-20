<?php
namespace App\Libs;

use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtLib
{
    private $idUser;
    private $tokenJwt;
    private $domain;
    private $permission_domains = [];

    public function __construct($key=CONFIG_SECURITY['token'], $domain=CONFIG_SECURITY['domain'], array $permission_domains = CONFIG_SECURITY['permission_domains'])
    {
        $this->setKey($key);
        $this->setDomain($domain);
        $this->setPermissionDomains($permission_domains);
    }

    public static function verifyTokenJWT()
    {
        $token = new JwtLib();
        $token = $token->decode($token->getBearerToken());
        if (empty($token)) {
            return false;
        } else {
            return $token;
        }
    }

    /**
     * @return array
     */
    public function getPermissionDomains(): array
    {
        return $this->permission_domains;
    }

    /**
     * @param array $permission_domains
     * @return JwtLib
     */
    public function setPermissionDomains($permission_domains): JwtLib
    {
        $this->permission_domains = $permission_domains;
        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getKey()
    {
        return $this->tokenJwt;
    }

    /**
     * @param mixed|string $token
     */
    public function setKey($token)
    {
        $this->tokenJwt = $token;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Função para inser o domínio
     * @param string $domain
     * @return void
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Função para gerar ID ramdomico
     * @param int $length
     * @return false|string
     */
    private function getId($length = 20)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Função para gerar ID ramdomico
     * @param array $data
     * @param int $validateHour
     * @return false|string
     */
    public function encode($minutes=60, $data = [] ):string
    {
        $iat   = new DateTimeImmutable();
        $exp     = $iat->modify("+{$minutes} minutes")->getTimestamp();

        $payload = array(
            "iss" => "{$this->getDomain()}", //O domínio da aplicação geradora do token
            "sub" => $this->getId(), //É o assunto do token, mas é muito utilizado para guarda o ID do usuário
            "jti" => $this->getId(), //O id do token
            "aud" => 0, //Define quem pode usar o token
            "iat" => $iat->getTimestamp(), // Data de criação do Token
            "nbf" => $iat->getTimestamp(), // Data que o token não pode ser aceito antes dela
            "exp" => $exp, // Data e Horario que o token expira
            'data' => $data// userid from the users table

        );

        $jwt = JWT::encode($payload, $this->getKey(),'HS256');

        return $jwt;
    }

    public function decode($tokenJwt)
    {
        $token = $tokenJwt;
        $now = new DateTimeImmutable();

        try {
            $decoded = JWT::decode($token, new Key($this->getKey(), 'HS256'));

            // VERIFICA DOMINIO
            if (!in_array($decoded->iss, $this->permission_domains)) {
                //echo "dominio invalido"; exit();
                return false;
            }

            // VERIFICA VALIDADE
            if (!empty($decoded->exp)) {
                if ($decoded->exp > $now->getTimestamp()) {
                    return $decoded;
                } else {
                    // TOKEN VENCIDO
                    //echo "venceu"; exit();
                    return false;
                }
            } else {
                //echo "invalido"; exit();
                // TOKEN INVALIDO
                return false;
            }
        } catch (\Error $ex) {
            //echo "Erro: ".$ex;
            return false;
        }

    }

    public function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    private function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
}