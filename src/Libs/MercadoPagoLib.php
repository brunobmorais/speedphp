<?php
namespace App\Libs;

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Resources\Preference;
use MercadoPago\Serialization\Serializer;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\Client\Common\RequestOptions;

class MercadoPagoLib {
    private PreferenceClient $client;

    public function __construct()
    {
        $this->client = new PreferenceClient();
    }

    public function create(array $request, ?RequestOptions $request_options = null): ?Preference
    {
        try {
            // Chama o método original para criar a preferência
            $response = $this->client->create($request, $request_options);

            // Converte a resposta para array
            $decodedResponse = json_decode(json_encode($response), true);

            // Remove a propriedade `financing_group` se existir
            if (isset($decodedResponse['financing_group'])) {
                unset($decodedResponse['financing_group']);
            }

            // Desserializa sem a propriedade problemática
            $cleanedJson = json_encode($decodedResponse);
            $result = Serializer::deserializeFromJson(Preference::class, $cleanedJson);
            return $result;
        } catch (MPApiException $e) {
            throw $e;
        }
    }
}