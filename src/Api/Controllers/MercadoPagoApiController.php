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

class MercadoPagoApiController
{
    private function getBody($itens, $payer, $id)
    {
        // Nome
        $nomeCompleto = trim($payer["name"] ?? "");
        // Evita erro de índice se for vazio
        if (empty($nomeCompleto)) {
            $nomeCompleto = "Sem Nome";
        }

        $partesNome = explode(" ", $nomeCompleto, 2);
        $primeiroNome = $partesNome[0];
        $restanteNome = $partesNome[1] ?? "";

        // Telefone
        $telefoneOriginal = (string) ($payer["telefone"] ?? "");
        $telefoneLimpo = preg_replace("/\\D/", "", $telefoneOriginal);

        // Evita problemas se houver menos de 3 dígitos
        if (strlen($telefoneLimpo) < 3) {
            $ddd = "00";
            $numeroSemDDD = $telefoneLimpo;
        } else {
            $ddd = substr($telefoneLimpo, 0, 2);
            $numeroSemDDD = substr($telefoneLimpo, 2);
        }

        // Email, CPF
        $email = $payer["email"] ?? "email@naoinformado.com";
        $cpf = $payer["cpf"] ?? "00000000000";

        return [
            "auto_return" => "approved",
            "back_urls" => [
                "success" => CONFIG_URL . "/checkout/result/?back=success",
                "failure" => CONFIG_URL . "/checkout/result/?back=failure",
                "pending" => CONFIG_URL . "/checkout/result/?back=pending"
            ],
            "statement_descriptor" => "VIAESPORTE",
            "binary_mode" => false,
            "external_reference" => $id,
            "items" => $itens,
            "payer" => [
                "email" => $email,
                "name" => $primeiroNome,
                "surname" => $restanteNome,
                "phone" => [
                    "area_code" => $ddd,
                    "number" => $numeroSemDDD
                ],
                "identification" => [
                    "type" => "CPF",
                    "number" => $cpf
                ]
            ],
            "payment_methods" => [
                "excluded_payment_types" => [],
                "excluded_payment_methods" => [
                    ["id" => "bolbradesco"], // Exclui pagamento via boleto
                    ["id" => "prepaid_card"],   // Exclui pagamento via cartão pré-pago
                    ["id" => "consumer_credits"] // Exclui pagamento via linha de crédito
                ],
                "installments" => 12
                // "default_payment_method_id" => "pix"
            ],
            "notification_url" => CONFIG_URL . "/api/mercadopago/webhook",
            "expires" => true,
            // "expiration_date_from" => (new FuncoesLib())->getCurrentDateIso(),
            "date_of_expiration" => (new FuncoesLib())->getCurrentDateIso(intval(CONFIG_SITE["prazo_horas_pagamento"]) + 1),
            "expiration_date_to" => (new FuncoesLib())->getCurrentDateIso(CONFIG_SITE["prazo_horas_pagamento"])
        ];
    }

    public function checkout(RequestClass $request)
    {

        if (empty(SessionLib::getValue("CODPESSOA"))) {
            return [
                "error" => true,
                "message" => "Faça login novamente para efetuar o pagamento."
            ];
        }

        $dataJson = $request->getJsonParams();


        $result = (new InscricaoDao())->registrarInscricao($dataJson);

        if (empty($result)) {
            return [
                "error" => true,
                "message" => "Sistema de cadastro com erro."
            ];
        }
        if ($result["error"])
            return $result;

        // ATUALIZA VALOR TOTAL DO PAGAMENTO E TIPO PAGAMENTO
        (new PagamentoDao())->updateArray(
            [
                "VALOR" => $result["data"]["VALOR_TOTAL"],
                "TIPO_PAGAMENTO" => $result["data"]["TIPO_PAGAMENTO"],
            ],
            "CODPAGAMENTO={$result["data"]["CODPAGAMENTO"]}");

        // SE A INSCRICAO FOR GRATUITA JA DIRECIONA PARA A TELA DE INSCRICOES
        if ($result["data"]["TIPO_INSCRICAO"] == "1" || $result["data"]["VALOR_TOTAL"] <= 0) {
            return [
                "error" => false,
                "message" => "Inscrições registradas.",
                "url" => "/checkout/result/?back=success&payment_id={$result["data"]["UUID_PAGAMENTO"]}&status=approved&external_reference={$result["data"]["UUID_PAGAMENTO"]}&payment_type=voucher_card",
                "paymentId" => $result["data"]["UUID_PAGAMENTO"]

            ];
        }

        $arrayItens = array();
        foreach ($result["data"]["INSCRICOES"] as $key => $item) {

            $arrayItens[] = [
                "id" => $item["ATLETAS"][0]["CODINSCRICAO"],
                "title" => "{$item["ATLETAS"][0]["NUMERO"]} [{$item["NOME_EVENTO"]}] Via Esporte - {$item["ATLETAS"][0]["NOME_ATLETA"]} - {$item["NOME_MODALIDADE"]}",
                "quantity" => 1,
                "unit_price" => $item["ATLETAS"][0]["VALOR_TOTAL"],
                "description" => "{$item["NOME_EVENTO"]}] Via Esporte",
                "category_id" => "tickets"
            ];
        }

        $payer = array(
            "email" => SessionLib::getValue("EMAIL"),
            "name" => SessionLib::getValue("NOME"),
            "cpf" => SessionLib::getValue("CPF"),
            "telefone" => SessionLib::getValue("TELEFONE")
        );

        $body = $this->getBody($arrayItens, $payer, $result["data"]["UUID_PAGAMENTO"]);

        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . CONFIG_PAYMENT['access_token']);
        $resultPay = HttpLib::post("https://api.mercadopago.com/checkout/preferences", $body, $header);

        if (empty($resultPay["body"])) {
            return [
                "error" => true,
                "message" => "Erro ao conectar ao MercadoPago"
            ];
        }

        $resultPay = json_decode($resultPay["body"], true);

        $status = $resultPay["status"]??"";
        if ($status == "400") {
            return [
                "error" => true,
                "message" => $resultPay["error"]." - ".$resultPay["message"]
            ];
        }

        $url = $resultPay["init_point"];
        $id = $resultPay["id"];
        //if (CONFIG_DISPLAY_ERROR_DETAILS)
        //    $url = $resultPay["sandbox_init_point"]; // Ambiente Sandbox

        (new PagamentoDao())->updateArray([
            "PREFERENCE_ID" => $id,
        ], "CODPAGAMENTO={$result["data"]["CODPAGAMENTO"]}");


        return [
            "error" => false,
            "message" => "Inscrições registradas.",
            "url" => $url,
            "paymentId" => $id
        ];
    }

    public function estornarPagamento(RequestClass $request)
    {
        $dataJson = $request->getJsonParams();
        $paymentId = $dataJson['payment_id'] ?? "";
        $amount = $dataJson['amount'] ?? "";

        if (empty($paymentId)){
            return [
                "error" => true,
                "message" => "Informe o paymentId"
            ];
        }

        $body = [];
        // SE MANDAR VAZIO VAI ESTORNAR 100%
        if (!empty($amount)){
            $body = [
                "amount" => $amount
            ];
        }

        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . CONFIG_PAYMENT['access_token'],
            'X-Idempotency-Key: ' . uniqid()
        );
        $result = HttpLib::post("https://api.mercadopago.com/v1/payments/{$paymentId}/refunds",
            $body,
            $header
        );

        if (empty($result["body"])) {
            return [
                "error" => true,
                "message" => "Erro ao conectar ao MercadoPago"
            ];
        }

        $result = json_decode($result["body"], true);

        $status = $result["status"]??"";
        if ($status != "approved") {
            return [
                "error" => true,
                "message" => "Erro ao processar reembolso",
                "data" => $result
            ];
        }

        return [
            "error" => false,
            "message" => "Estornar Pagamento",
            "data" => $result
        ];
    }

    public function cancelarPagamento(RequestClass $request)
    {
        $dataJson = $request->getJsonParams();
        $paymentId = $dataJson['payment_id'] ?? "";

        if (empty($paymentId)){
            return [
                "error" => true,
                "message" => "Informe o paymentId"
            ];
        }

        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . CONFIG_PAYMENT['access_token'],
            'X-Idempotency-Key: ' . uniqid()
        );

        $result = HttpLib::put("https://api.mercadopago.com/v1/payments/{$paymentId}",
            [
                "status" => "cancelled"
            ],
            $header
        );

        if (empty($result["body"])) {
            return [
                "error" => true,
                "message" => "Erro ao conectar ao MercadoPago"
            ];
        }

        $result = json_decode($result["body"], true);

        $status = $result["data"]["status"]??"";
        if ($status != "approved") {
            return [
                "error" => true,
                "message" => "Erro ao processar reembolso",
                "data" => $result
            ];
        }

        return [
            "error" => false,
            "message" => "Cancelar Pagamento",
            "data" => $result
        ];

    }

    public function consultarPagamento(RequestClass $request){

        $paymentId = $request->getJsonParams()['payment_id'] ?? "";
        if (empty($paymentId))
            $paymentId = $request->getJsonParams()["data"]["id"] ?? "";

        if (empty($paymentId)){
            return [
                "error" => true,
                "message" => "Informe o paymentId"
            ];
        }

        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . CONFIG_PAYMENT['access_token']);
        $result = HttpLib::get("https://api.mercadopago.com/v1/payments/{$paymentId}", $header);

        if (empty($result["body"])) {
            return [
                "error" => true,
                "message" => "Erro ao conectar ao MercadoPago"
            ];
        }

        $result = json_decode($result["body"], true);

        return [
            "error" => false,
            "message" => "Consultando pagamento",
            "data" => $result
        ];
    }

    public function webhook(RequestClass $request)
    {
        $pagamentoEfetuado = false;

        $type = $request->getJsonParams()['type'] ?? "";

        switch($type) {
            case "payment":
                $pagamentoEfetuado = true;
                $result = $this->consultarPagamento($request);
                break;
            case "plan":
                //$plan = MercadoPago\Plan::find_by_id($_POST["data"]["id"]);
                break;
            case "subscription":
                //$plan = MercadoPago\Subscription::find_by_id($_POST["data"]["id"]);
                break;
            case "invoice":
                //$plan = MercadoPago\Invoice::find_by_id($_POST["data"]["id"]);
                break;
            case "point_integration_wh":
                // $_POST contém as informações relacionadas à notificação.
                break;
        }

        if ($pagamentoEfetuado && !empty($result)) {
            $paymentId = $request->getJsonParams()["data"]["id"] ?? "";
            $externalReference = $result["data"]["external_reference"] ?? "";
            $status = $result["data"]["status"] ?? "";

            if ($status == "approved") {
                $tipoPagamento = PagamentoDao::getTipoPagamentoId($result["data"]["payment_type_id"]);

                (new PagamentoDao())->updateArray([
                    "SITUACAO" => 2,
                    "PAYMENT_ID" => $paymentId,
                    "TIPO_PAGAMENTO" => $tipoPagamento,
                ],"UUID LIKE '{$externalReference}'");

                // CALCULAR O VALOR DO GATEWAY DE PAGAMENTO
                (new PagamentoDao())->calculaValorGatewayPagamento($tipoPagamento, $externalReference);

                // ENVIAR EMAIL
                (new InscricaoDao())->enviarEmailConfirmacaoInscricao($externalReference);
            }

            return [
                "error" => false,
                "payment" => true,
                "message" => "Pagamento aprovado"
            ];
        }

        return [
            "error" => false,
            "payment" => false,
            "message" => "Não foi encontrado pagamento"
        ];
    }

    public function cancelarInscricoesVencidas(RequestClass $request)
    {
        $inscricoes = (new InscricaoDao())->getInscricoesVencidas();

        foreach ($inscricoes as $inscricao) {
            (new InscricaoDao())->updateArray(["SITUACAO" => 0], "CODINSCRICAO IN ({$inscricao->CODINSCRICAO})");
            (new InscricaoPagamentoDao())->updateArray(["SITUACAO" => 0], "CODINSCRICAO_PAGAMENTO IN ({$inscricao->CODINSCRICAO_PAGAMENTO})");
            (new PagamentoDao())->updateArray(["SITUACAO" => 3], "CODPAGAMENTO IN ({$inscricao->CODPAGAMENTO})");
        }

        return [
            'error' => false,
            'message'  => "Executado com sucesso",
        ];
    }

    public function estornarValoresCorridaFogo(RequestClass $request) {
        //$inscricoes = (new InscricaoDao())->aplicarDescontoProgressivo();

        return [
            'error' => false,
            'message'  => "Executado com sucesso",
            'data' => $inscricoes
        ];
    }

    private function validateRequest() {
        // Obtain the x-signature value from the header
        $xSignature = $_SERVER['HTTP_X_SIGNATURE'];
        $xRequestId = $_SERVER['HTTP_X_REQUEST_ID'];

        // Obtain Query params related to the request URL
        $queryParams = $_GET;

        // Extract the "data.id" from the query params
        $dataID = isset($queryParams['data.id']) ? $queryParams['data.id'] : '';

        // Separating the x-signature into parts
        $parts = explode(',', $xSignature);

        // Initializing variables to store ts and hash
        $ts = null;
        $hash = null;

        // Iterate over the values to obtain ts and v1
        foreach ($parts as $part) {
            // Split each part into key and value
            $keyValue = explode('=', $part, 2);
            if (count($keyValue) == 2) {
                $key = trim($keyValue[0]);
                $value = trim($keyValue[1]);
                if ($key === "ts") {
                    $ts = $value;
                } elseif ($key === "v1") {
                    $hash = $value;
                }
            }
        }

        // Obtain the secret key for the user/application from Mercadopago developers site
        $secret = "your_secret_key"; // Replace with your actual secret key

        // Generate the manifest string
        $manifest = "id:$dataID;request-id:$xRequestId;ts:$ts;";

        // Create an HMAC signature defining the hash type and the key as a byte array
        $sha = hash_hmac('sha256', $manifest, $secret);
        if ($sha === $hash) {
            // HMAC verification passed
            echo "HMAC verification passed";
            return true;
        } else {
            // HMAC verification failed
            echo "HMAC verification failed";
            return false;
        }
    }

}
