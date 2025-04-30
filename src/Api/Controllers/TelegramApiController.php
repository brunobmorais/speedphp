<?php

namespace App\Api\Controllers;

use App\Api\Lib\RequestClass;
use App\Daos\InscricaoDao;
use App\Daos\InscricaoPagamentoDao;
use App\Daos\PagamentoDao;
use App\Libs\FuncoesLib;
use App\Libs\HttpLib;
use App\Libs\SessionLib;
use App\Libs\Twig\TwigLib;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\Payment;
use stdClass;

class TelegramApiController
{


    public function sendMessage(RequestClass $request)
    {
        $tituloJson = $request->getJsonParams()['title'] ?? "";
        $messageJson = "\n\n".$request->getJsonParams()['message'] ?? "";
        $token = '';
        $chat_id = ''; // ID do chat ou do grupo
        //$chat_id = '130511391'; // ID do chat ou do grupo
        $message_thread_id = $request->getJsonParams()['title'] ?? "1";
        // 1 = GERAL

        $message = $tituloJson.$messageJson;

        $url = "https://api.telegram.org/bot$token/sendMessage";

        $data = [
            'chat_id' => $chat_id,
            'text' => $message,
            'message_thread_id' => $message_thread_id,

        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            $error = !$result;
        } else {
            $error = !$result;
        }

        return [
            "error" => $error,
            "message" => "",
        ];
    }

}
