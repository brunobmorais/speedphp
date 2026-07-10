<?php
namespace App\Libs;

use App\Daos\PessoaStravaDao;

class StravaLib
{
    private const BASE_URL  = 'https://www.strava.com/api/v3';
    private const AUTH_URL  = 'https://www.strava.com/oauth/authorize';
    private const TOKEN_URL = 'https://www.strava.com/oauth/token';

    public static function getAuthorizationUrl(string $state = ''): string
    {
        $config = CONFIG_SOCIAL_LOGIN['strava'];
        $params = http_build_query([
            'client_id'     => $config['client_id'],
            'redirect_uri'  => $config['callback'],
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'scope'         => 'read,activity:read_all',
            'state'         => $state,
        ]);

        return self::AUTH_URL . '?' . $params;
    }

    public static function exchangeCodeForToken(string $code): array
    {
        $config = CONFIG_SOCIAL_LOGIN['strava'];
        $response = HttpLib::post(self::TOKEN_URL, [
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code'          => $code,
            'grant_type'    => 'authorization_code',
        ]);

        return json_decode($response['body'], true) ?: [];
    }

    public static function refreshAccessToken(string $refreshToken): array
    {
        $config = CONFIG_SOCIAL_LOGIN['strava'];
        $response = HttpLib::post(self::TOKEN_URL, [
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ]);

        return json_decode($response['body'], true) ?: [];
    }

    public static function getValidToken(int $codPessoa): ?string
    {
        $dao = new PessoaStravaDao();
        $strava = $dao->buscarPorPessoa($codPessoa);

        if (empty($strava)) {
            return null;
        }

        if ($strava->EXPIRES_AT < time() + 300) {
            $tokenData = self::refreshAccessToken($strava->REFRESH_TOKEN);
            if (empty($tokenData['access_token'])) {
                return null;
            }
            $dao->atualizarToken(
                $codPessoa,
                $tokenData['access_token'],
                $tokenData['refresh_token'],
                $tokenData['expires_at']
            );
            return $tokenData['access_token'];
        }

        return $strava->ACCESS_TOKEN;
    }

    public static function getActivities(string $accessToken, int $after, int $before, int $page = 1, int $perPage = 30): array
    {
        $params = http_build_query([
            'after'    => $after,
            'before'   => $before,
            'page'     => $page,
            'per_page' => $perPage,
        ]);

        $url = self::BASE_URL . '/athlete/activities?' . $params;
        $header = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ];

        $response = HttpLib::get($url, $header);
        return json_decode($response['body'], true) ?: [];
    }

    public static function getActivity(string $accessToken, int $activityId): ?array
    {
        $url = self::BASE_URL . '/activities/' . $activityId;
        $header = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ];

        $response = HttpLib::get($url, $header);
        return json_decode($response['body'], true);
    }

    public static function buscarAtividadesParaEvento(int $codPessoa, string $dataEvento, float $distanciaKm): array
    {
        $accessToken = self::getValidToken($codPessoa);
        if (empty($accessToken)) {
            return [];
        }

        $dataBase = self::parseEventDate($dataEvento);
        if ($dataBase === null) {
            return [];
        }

        $diaInicio = strtotime(date('Y-m-d 00:00:00', $dataBase));
        $diaFim    = strtotime(date('Y-m-d 23:59:59', $dataBase));

        $after  = $diaInicio;
        $before = $diaFim;
        $activities = [];
        for ($page = 1; $page <= 3; $page++) {
            $pageActivities = self::getActivities($accessToken, $after, $before, $page, 50);
            if (empty($pageActivities) || !is_array($pageActivities)) {
                break;
            }

            $activities = array_merge($activities, $pageActivities);

            if (count($pageActivities) < 50) {
                break;
            }
        }

        if (empty($activities) || !is_array($activities)) {
            return [];
        }

        $distanciaMetros = $distanciaKm * 1000;
        $tolerancia = 0.50; // 50%

        $matchedComDistancia = [];
        $matchedSemDistancia = [];
        foreach ($activities as $activity) {
            if (!in_array($activity['type'] ?? '', ['Run', 'VirtualRun', 'TrailRun'])) {
                continue;
            }

            $actDist = $activity['distance'] ?? 0;
            $distanciaCompativel = true;
            if ($distanciaKm > 0 && abs($actDist - $distanciaMetros) > ($distanciaMetros * $tolerancia)) {
                $distanciaCompativel = false;
            }

            $tempoSegundos = $activity['moving_time'] ?? 0;
            $horas   = floor($tempoSegundos / 3600);
            $minutos = floor(($tempoSegundos % 3600) / 60);
            $segs    = $tempoSegundos % 60;

            $item = [
                'id'              => $activity['id'],
                'name'            => $activity['name'] ?? '',
                'start_date'      => $activity['start_date_local'] ?? $activity['start_date'] ?? '',
                'distance'        => round(($activity['distance'] ?? 0) / 1000, 3),
                'moving_time'     => $activity['moving_time'] ?? 0,
                'elapsed_time'    => $activity['elapsed_time'] ?? 0,
                'tempo_formatado' => sprintf('%02d:%02d:%02d', $horas, $minutos, $segs),
                'type'            => $activity['type'] ?? '',
            ];

            if ($distanciaCompativel) {
                $matchedComDistancia[] = $item;
                continue;
            }

            $matchedSemDistancia[] = $item;
        }

        $matched = !empty($matchedComDistancia) ? $matchedComDistancia : $matchedSemDistancia;

        usort($matched, function ($a, $b) use ($dataBase) {
            $diffA = abs(strtotime($a['start_date']) - $dataBase);
            $diffB = abs(strtotime($b['start_date']) - $dataBase);
            return $diffA <=> $diffB;
        });

        return $matched;
    }

    private static function parseEventDate(string $dataEvento): ?int
    {
        $dataEvento = trim($dataEvento);
        if ($dataEvento === '') {
            return null;
        }

        $timestamp = strtotime($dataEvento);
        if ($timestamp !== false) {
            return $timestamp;
        }

        $formatos = ['Y-m-d H:i:s', 'Y-m-d', 'd/m/Y H:i:s', 'd/m/Y'];
        foreach ($formatos as $formato) {
            $dt = \DateTimeImmutable::createFromFormat($formato, $dataEvento);
            if ($dt instanceof \DateTimeImmutable) {
                return $dt->getTimestamp();
            }
        }

        return null;
    }
}
