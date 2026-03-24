<?php

namespace App\Libs;

class TimeLib
{
    public static function tempoParaSegundos($tempo) {
        if (empty($tempo)) {
            return 0;
        }
        // Separa por ":"
        $partes = explode(":", $tempo);

        // Lida com segundos e milissegundos (ex: "59.321")
        $segundos = 0;

        // Normaliza o tempo (inverte para facilitar)
        $partes = array_reverse($partes);

        // Processa segundos (com ou sem milissegundos)
        if (isset($partes[0])) {
            $segundos += floatval($partes[0]);
        }

        // Processa minutos
        if (isset($partes[1])) {
            $segundos += intval($partes[1]) * 60;
        }

        // Processa horas
        if (isset($partes[2])) {
            $segundos += intval($partes[2]) * 3600;
        }

        return $segundos;
    }
}