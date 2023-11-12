<?php
namespace App\Api\Lib;


class RequestClass
{
    private $httpMethod;

    private $uri;

    private $queryParams = [];

    private $postVars = [];

    private $headers = [];

    private $jsonParams = [];

    /**
     * @var RouterClass
     */
    private $router;

    public function __construct(RouterClass $router){
        $this->router = $router;
        $this->queryParams = $_GET ?? [];
        $this->postVars = $_POST ?? [];
        $this->headers = getallheaders();

        $this->httpMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        $this->uri = $_SERVER['REQUEST_URI'] ?? '';
        $this->setUri();
        $this->jsonParams = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function setUri(){
        $this->uri = $_SERVER['REQUEST_URI'] ?? '';
        $xUri = explode("?",$this->uri);
        $this->uri = $xUri[0];
    }

    public function getRouter():RouterClass{
        return $this->router;
    }

    /**
     * @return mixed|string
     */
    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    public function getJsonParams():array{
        return $this->jsonParams;
    }

    /**
     * @return mixed|string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @return array
     */
    public function getPostVars(): array
    {
        return $this->postVars;
    }

    /**
     * @return array|false
     */
    public function getHeaders()
    {
        return $this->headers;
    }

}