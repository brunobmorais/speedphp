<?php

namespace App\Libs\MercadoPago;

use App\Daos\EventoConfigDao;
use App\Daos\InscricaoDao;
use App\Daos\PagamentoDao;
use App\Enums\SituacaoInscricaoEnum;
use App\Enums\SituacaoPagamentoEnum;
use App\Libs\Exception\CheckoutException;
use App\Libs\FuncoesLib;
use App\Libs\HttpLib;
use App\Libs\SessionLib;
use App\Models\PagamentoModel;
use Exception;
use MercadoPago\MercadoPagoConfig;

final class TransparentCheckout
{


    private $header;

    private $endpoint = 'https://api.mercadopago.com/v1/payments/';

    private $idepotency_key;

    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(CONFIG_PAYMENT['access_token']);
    }

    public function montarHeader($idepotency_key)
    {
        $this->header =  ['Content-Type: application/json', 'Authorization: Bearer ' . CONFIG_PAYMENT['access_token'], 'X-Idempotency-Key: ' . $idepotency_key];
    }

    private function montarPayload($data)
    {


        $pagamento_uuid = $data['metadata']['pedido_id'];

        $idepotency_key = match ($data['payment_method_id']) {

            'pix' => $pagamento_uuid,


            'credit_card', 'master', 'visa', 'elo', 'amex', 'hiper'   =>
            $pagamento_uuid . '-' . uniqid()
        };

        $this->montarHeader($idepotency_key);

        $pagamentoModel = (new PagamentoDao)->buscarPedido($pagamento_uuid);
        if(!$pagamentoModel){
            throw new CheckoutException('Pedido não encontrado', 400);
        }

        if($pagamentoModel->SITUACAO != SituacaoPagamentoEnum::AGUARDANDO->value){
            throw new CheckoutException('Pedido não disponível para pagamento', 400);
        }
        
        $itens_pedido = (new InscricaoDao)->buscarInscricoesPagamento($pagamentoModel->CODPAGAMENTO);


        if (!(PagamentoModel::hasPrazoPagamento($pagamento_uuid))) {
            throw new CheckoutException('O prazo para pagamento se esgotou', 400);
        }

        if(empty($itens_pedido)){
            throw new CheckoutException('Inscrição não encontrada', 400);
        }

        foreach($itens_pedido as $item){
            if($item->SITUACAO_INSCRICAO != SituacaoInscricaoEnum::AGUARDANDO_PAGAMENTO->value){
                throw new CheckoutException('Inscrição não disponível para pagamento', 400);
            }
        }

    

        $inscricao = (new InscricaoDao)->buscarInscricaoCodinscricao($item->CODINSCRICAO);

        if(empty($inscricao) || !$inscricao){
            throw new CheckoutException('Inscrição não encontrada', 400);
        }

        $eventoConfig = (new EventoConfigDao)->buscarEventoConfig($inscricao->CODEVENTO);

        if(!$eventoConfig){
            throw new CheckoutException('Ocorreu um erro ao buscar as configurações do evento', 400);
        }

        $arrayItens = array();
        foreach ($itens_pedido  as $key => $item) {

            $nomeEvento = (new FuncoesLib())->sanitizeJsonText($item->NOME_EVENTO);
            $nomeModalidade = (new FuncoesLib())->sanitizeJsonText($item->NOME_MODALIDADE);
            $nomeAtleta = $item->NOME_ATLETA;
            $title = "{$item->NUMERO} - {$nomeEvento} - Via Esporte - {$nomeAtleta} - {$nomeModalidade}";

            $arrayItens[] = [
                "id" => $item->CODINSCRICAO,
                "title" => mb_substr($title, 0, 255), // 256 caracteres
                "quantity" => 1,
                "unit_price" => $item->VALOR_PAGAMENTO,
                "description" => "{$nomeEvento} - Via Esporte", // 600 caracteres
                "category_id" => "tickets",

            ];
        }
        // Nome
        $nomeCompleto =  $pagamentoModel->CADASTRANTE;
        // Evita erro de índice se for vazio
        if (empty($nomeCompleto)) {
            $nomeCompleto = "Sem Nome";
        }

        $partesNome = explode(" ", $nomeCompleto, 2);
        $primeiroNome = $partesNome[0];
        $restanteNome = $partesNome[1] ?? "";

        // Telefone
        $telefoneOriginal = $pagamentoModel->TELEFONE_CADASTRANTE;
        $telefoneLimpo = preg_replace("/\\D/", "", $telefoneOriginal ?? 0);

        // Evita problemas se houver menos de 3 dígitos
        if (strlen($telefoneLimpo) < 3) {
            $ddd = "00";
            $numeroSemDDD = $telefoneLimpo;
        } else {
            $ddd = substr($telefoneLimpo, 0, 2);
            $numeroSemDDD = substr($telefoneLimpo, 2);
        }

        // Email, CPF
        $email = $pagamentoModel->EMAIL_CADASTRANTE;
        $cpf =  $pagamentoModel->CPF_CADASTRANTE;

        $date = (new \DateTime())->createFromFormat('Y-m-d H:i:s', $pagamentoModel->CRIADO_EM);
        $prazoPagamento = $eventoConfig->QTD_MINUTOS_VALIDADE_CHECKOUT;
        $date->modify("+$prazoPagamento minutes");
        $vencimentoPix = $date->format('Y-m-d\TH:i:s.000P');

        $body = match ($data['payment_method_id']) {

            'pix' => [
                'transaction_amount' => (float)$data['transaction_amount'],
                'description' => "{$nomeEvento} - Via Esporte",
                'statement_descriptor' => "{$nomeEvento} - Via Esporte",
                'payment_method_id' => $data['payment_method_id'],
                'payer' => [
                    'email' => $data['payer']['email'],
                    'first_name' => $primeiroNome,
                    'last_name' => $restanteNome,
                    'identification' => [
                        'type' => $data['payer']['identification']['type'],
                        'number' => $data['payer']['identification']['number']
                    ]
                ],
                "additional_info" => [
                    "items" => $arrayItens,
                    "payer" => [
                        "first_name" => $primeiroNome,
                        "last_name" => $restanteNome,
                        "phone" => [
                            "area_code" => $ddd,
                            "number" => $numeroSemDDD
                        ],
                    ],
                ],
                'date_of_expiration' => $vencimentoPix,
                'external_reference' => $data['metadata']['pedido_id'],
                'notification_url' => CONFIG_HEADER['url'] . '/api/mercadopago/webhook'
            ],


            'credit_card', 'master', 'visa', 'elo', 'amex', 'hiper'   => [
                'transaction_amount' => (float)$data['transaction_amount'],
                'token' => $data['token'] ?? null,
                'description' => "{$nomeEvento} - Viaesporte.com",
                'statement_descriptor' => "{$nomeEvento} - Viaesporte.com",
                'installments' => (int)$data['installments'],
                'payment_method_id' => $data['payment_method_id'],
                'issuer_id' => $data['issuer_id'],
                'payer' => [
                    'email' => $data['payer']['email'],
                    'first_name' => $primeiroNome,
                    'last_name' => $restanteNome,
                    'identification' => [
                        'type' => $data['payer']['identification']['type'],
                        'number' => $data['payer']['identification']['number']
                    ]
                ],
                "additional_info" => [
                    "items" => $arrayItens,
                    "payer" => [
                        "first_name" => $primeiroNome,
                        "last_name" => $restanteNome,
                        "phone" => [
                            "area_code" => $ddd,
                            "number" => $numeroSemDDD
                        ],
                    ],
                ],
                'external_reference' => $data['metadata']['pedido_id'],
                'notification_url' => CONFIG_HEADER['url'] . '/api/mercadopago/webhook'
            ]
        };


        return $body;
    }




    public function processarPagamentoPix(array $data)
    {
        try {

            $body = $this->montarPayload($data);

            $resultPay = HttpLib::post($this->endpoint, $body, $this->header);

            $response = json_decode($resultPay['body'], true);

            if (isset($response['id']) && $response['status'] == 'pending') {
                return [
                    'success' => true,
                    'payment_id' => $response['id'],
                    'qr_code' => $response['point_of_interaction']['transaction_data']['qr_code'],
                    'qr_code_base64' => $response['point_of_interaction']['transaction_data']['qr_code_base64']
                ];
            }

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Erro ao gerar PIX'
            ];
        } 
        
        catch (CheckoutException $e){
                return ['success' => false, 'message' => $e->getMessage()];
        
        }catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ocorreu um erro ao processar o pix.'];
        }
    }

    public function processarPagamentoCartao(array $data)
    {
        try {
            $body = $this->montarPayload($data);
            $resultPay = HttpLib::post($this->endpoint, $body, $this->header);
            $response = json_decode($resultPay['body'], true);

            if (isset($response['id']) and $response['status'] == 'approved') {
                return [
                    'success' => true,
                    'id' => $response['id'],
                    'status' => $response['status'],
                    'status_detail' => $response['status_detail'] ?? 'Pagamento processado',
                    'message' => $response['status_detail'] ?? 'Pagamento processado'
                ];
            }

                return [
                    'success' => false,
                    'id' => $response['id'],
                    'status' => $response['status'],
                    'status_detail' => $response['status_detail'] ,
                    'message' => 'Ocorreu um erro ao processar o pagamento'
                ];
        } 
          catch (CheckoutException $e){
            return ['success' => false, 'message' => $e->getMessage()];
        
        }
        catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ocorreu um erro ao processar o pagamento.'];
        }
    }

    public function verificarPagamento(?string $paymentId)
    {
        try {
            if (empty($paymentId)) {
                return ['status' => 'error', 'message' => 'Payment ID não fornecido'];
            }

            $this->montarHeader(uniqid());
            $result = HttpLib::get($this->endpoint . $paymentId, $this->header);
            $response = json_decode($result['body'], true);

            return ['status' => $response['status'] ?? 'error'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
