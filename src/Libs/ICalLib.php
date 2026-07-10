<?php

namespace App\Libs;

class ICalLib
{
    public static function gerarFeed(array $eventos): string
    {
        $lines = [];
        $lines[] = "BEGIN:VCALENDAR";
        $lines[] = "VERSION:2.0";
        $lines[] = "PRODID:-//ViaEsporte//Calendario//PT-BR";
        $lines[] = "CALSCALE:GREGORIAN";
        $lines[] = "METHOD:PUBLISH";
        $lines[] = "X-WR-CALNAME:ViaEsporte - Eventos";
        $lines[] = "X-WR-TIMEZONE:America/Sao_Paulo";

        foreach ($eventos as $evento) {
            $uuid = $evento->UUID ?? $evento->CODEVENTO;
            $url = $evento->URL ?? $evento->CODEVENTO; 
            $nome = $evento->NOME_EVENTO ?? '';
            $local = $evento->LOCAL_EVENTO ?? '';
            $url = CONFIG_URL . "/evento/" . $url;

            // Evento 1: Início da Inscrição
            if (!empty($evento->DATA_INSCRICAO_INICIO)) {
                $lines[] = "BEGIN:VEVENT";
                $lines[] = "UID:inscricao-abre-" . $uuid . "@viaesporte.com";
                $lines[] = "DTSTART:" . self::formatDate($evento->DATA_INSCRICAO_INICIO);
                $lines[] = "SUMMARY:" . self::escapeText("[Inscrição Abre] " . $nome);
                $lines[] = "DESCRIPTION:" . self::escapeText($url);
                $lines[] = "END:VEVENT";
            }

            // Evento 2: Fim da Inscrição
            if (!empty($evento->DATA_INSCRICAO_FIM)) {
                $lines[] = "BEGIN:VEVENT";
                $lines[] = "UID:inscricao-fecha-" . $uuid . "@viaesporte.com";
                $lines[] = "DTSTART:" . self::formatDate($evento->DATA_INSCRICAO_FIM);
                $lines[] = "SUMMARY:" . self::escapeText("[Inscrição Fecha] " . $nome);
                $lines[] = "DESCRIPTION:" . self::escapeText($url);
                $lines[] = "END:VEVENT";
            }

            // Evento 3: Dia do Evento
            if (!empty($evento->DATA_EVENTO)) {
                $lines[] = "BEGIN:VEVENT";
                $lines[] = "UID:evento-" . $uuid . "@viaesporte.com";
                $lines[] = "DTSTART:" . self::formatDate($evento->DATA_EVENTO);
                $lines[] = "SUMMARY:" . self::escapeText($nome);
                if (!empty($local)) {
                    $lines[] = "LOCATION:" . self::escapeText($local);
                }
                $lines[] = "DESCRIPTION:" . self::escapeText($url);
                $lines[] = "END:VEVENT";
            }
        }

        $lines[] = "END:VCALENDAR";

        return implode("\r\n", $lines);
    }

    private static function formatDate(string $dateStr): string
    {
        $dt = new \DateTime($dateStr);
        return $dt->format('Ymd\THis\Z');
    }

    private static function escapeText(string $text): string
    {
        $text = str_replace("\\", "\\\\", $text);
        $text = str_replace(",", "\\,", $text);
        $text = str_replace(";", "\\;", $text);
        $text = str_replace("\n", "\\n", $text);
        return $text;
    }
}
