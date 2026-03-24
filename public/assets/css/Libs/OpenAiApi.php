<?php

namespace App\Libs;

use GuzzleHttp\Client;
use OpenAI;
use Psr\Http\Message\RequestInterface;

class OpenAiApi{

    public $client ; 

    public function __construct()
    {
        try {
            $httpClient = new Client();
            $this->client = OpenAI::factory()
                ->withApiKey(CONFIG_KEY_API['chatgpt'])
                ->withHttpClient($httpClient)
                ->withStreamHandler(fn (RequestInterface $request) => $httpClient->send($request, ['stream' => true]))
                ->make();
        
          
        } catch (\Throwable $th) {
        return $th; 
        }
    }

    public function response($payload){
        $response = $this->client->chat()->create($payload); 
        return $response;
    }

    public function streamedResponse($payload){
        $response = $this->client->chat()->createStreamed($payload); 
        return $response;
    }



}
