<?php


namespace App\Api\Lib;


class ResponseClass
{
    /**
     * @var int
     */
    private $httpCode = 200;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var string
     */
    private $contentType = 'text/html';

    /**
     * @var mixed
     */
    private $content;

    public function __construct($httpCode, $content, $contentType = 'application/json'){
        $this->httpCode = $httpCode;
        $this->content = $content;
        $this->setContentType($contentType);
    }

    public function setContentType($contentType){
        $this->contentType = $contentType;
        $this->addHeader('Content-Type',$contentType);

    }

    public function addHeader($key,$value){
        $this->headers[$key] = $value;
    }

    private function sendHeaders(){
        http_response_code($this->httpCode);

        foreach ($this->headers as $key=>$value){
            header($key.': '.$value);
        }
    }

    public function sendResponse(){
        $this->sendHeaders();

        switch ($this->contentType){
            case 'text/html':
                echo $this->content;
                break;
            case 'application/json':
                echo json_encode($this->content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                break;
            default:
                echo "Sem content";
                break;
        }
    }

}