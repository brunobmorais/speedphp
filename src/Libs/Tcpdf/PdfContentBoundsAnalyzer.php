<?php

namespace App\Libs\Tcpdf;

use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfReader\PdfReader;

class PdfContentBoundsAnalyzer
{
    private const FOOTER_ZONE_PTS = 30.0; // ~10mm de zona de rodapé (Powered by TCPDF etc.)

    /**
     * Retorna o Y do ponto mais baixo do conteúdo "principal" da página, em mm a partir do topo.
     * Ignora o rodapé (~10mm finais). Fallback: 75% da altura da página.
     */
    public function getContentBottomY(string $filePath, int $pageNumber, float $pageHeightMm): float
    {
        if (!is_readable($filePath)) {
            return $pageHeightMm * 0.75;
        }

        try {
            $parser = new PdfParser(StreamReader::createByFile($filePath));
            $reader = new PdfReader($parser);
            $page   = $reader->getPage($pageNumber);
            $stream = $page->getContentStream();
        } catch (\Throwable $e) {
            return $pageHeightMm * 0.75;
        }

        if (empty($stream)) {
            return $pageHeightMm * 0.75;
        }

        $positions = $this->collectYPositions($stream);

        $filtered = array_filter($positions, fn($y) => $y >= self::FOOTER_ZONE_PTS);
        if (empty($filtered)) {
            return $pageHeightMm * 0.75;
        }

        $minYPts       = min($filtered);
        $pageHeightPts = $pageHeightMm / 25.4 * 72;
        $yFromTopMm    = ($pageHeightPts - $minYPts) / 72 * 25.4;

        return max(0.0, min($yFromTopMm, $pageHeightMm));
    }

    /**
     * Tokeniza o content stream e retorna lista de Y (em pts) onde há conteúdo visível.
     * Rastreia estado de texto (BT/ET, Tm, Td, TD) e marca Y quando há Tj/TJ/'/" (texto desenhado).
     * Também coleta Y de cm (imagens), re (retângulos) e l/m (linhas/paths).
     */
    private function collectYPositions(string $stream): array
    {
        $ys   = [];
        $stack = [];
        $textY = 0.0;
        $textYInit = false;
        $pos  = 0;
        $len  = strlen($stream);

        while ($pos < $len) {
            $ch = $stream[$pos];

            if ($this->isWhitespace($ch)) {
                $pos++;
                continue;
            }

            if ($ch === '%') {
                while ($pos < $len && $stream[$pos] !== "\n" && $stream[$pos] !== "\r") {
                    $pos++;
                }
                continue;
            }

            // Literal string (...)
            if ($ch === '(') {
                $depth = 1;
                $pos++;
                while ($pos < $len && $depth > 0) {
                    if ($stream[$pos] === '\\') {
                        $pos += 2;
                        continue;
                    }
                    if ($stream[$pos] === '(') {
                        $depth++;
                    } elseif ($stream[$pos] === ')') {
                        $depth--;
                        if ($depth === 0) {
                            $pos++;
                            break;
                        }
                    }
                    $pos++;
                }
                $stack[] = null;
                continue;
            }

            // Hex string <...> ou dict <<
            if ($ch === '<') {
                $pos++;
                if ($pos < $len && $stream[$pos] === '<') {
                    $depth = 1;
                    $pos++;
                    while ($pos < $len && $depth > 0) {
                        if ($pos + 1 < $len && $stream[$pos] === '<' && $stream[$pos + 1] === '<') {
                            $depth++;
                            $pos += 2;
                            continue;
                        }
                        if ($pos + 1 < $len && $stream[$pos] === '>' && $stream[$pos + 1] === '>') {
                            $depth--;
                            $pos += 2;
                            continue;
                        }
                        $pos++;
                    }
                } else {
                    while ($pos < $len && $stream[$pos] !== '>') {
                        $pos++;
                    }
                    if ($pos < $len) {
                        $pos++;
                    }
                }
                $stack[] = null;
                continue;
            }

            // Array [...]
            if ($ch === '[') {
                $depth = 1;
                $pos++;
                while ($pos < $len && $depth > 0) {
                    if ($stream[$pos] === '[') {
                        $depth++;
                    } elseif ($stream[$pos] === ']') {
                        $depth--;
                        if ($depth === 0) {
                            $pos++;
                            break;
                        }
                    }
                    $pos++;
                }
                $stack[] = null;
                continue;
            }

            // Name /xxx
            if ($ch === '/') {
                $pos++;
                while ($pos < $len && !$this->isDelimiter($stream[$pos])) {
                    $pos++;
                }
                $stack[] = null;
                continue;
            }

            // Número ou operador
            $start = $pos;
            while ($pos < $len && !$this->isDelimiter($stream[$pos])) {
                $pos++;
            }
            $token = substr($stream, $start, $pos - $start);

            if ($token === '') {
                $pos++;
                continue;
            }

            if (is_numeric($token)) {
                $stack[] = (float) $token;
                continue;
            }

            $count = count($stack);
            switch ($token) {
                case 'BT':
                    $textY     = 0.0;
                    $textYInit = false;
                    break;
                case 'Tm':
                    if ($count >= 6 && is_numeric($stack[$count - 1])) {
                        $textY     = (float) $stack[$count - 1];
                        $textYInit = true;
                    }
                    break;
                case 'Td':
                case 'TD':
                    if ($count >= 2 && is_numeric($stack[$count - 1])) {
                        $textY    += (float) $stack[$count - 1];
                        $textYInit = true;
                    }
                    break;
                case 'Tj':
                case 'TJ':
                case "'":
                case '"':
                    if ($textYInit) {
                        $ys[] = $textY;
                    }
                    break;
                case 're':
                    if ($count >= 4 && is_numeric($stack[$count - 3])) {
                        $ys[] = (float) $stack[$count - 3];
                    }
                    break;
                case 'cm':
                    if ($count >= 6 && is_numeric($stack[$count - 1])) {
                        $ys[] = (float) $stack[$count - 1];
                    }
                    break;
                case 'm':
                case 'l':
                    if ($count >= 2 && is_numeric($stack[$count - 1])) {
                        $ys[] = (float) $stack[$count - 1];
                    }
                    break;
            }

            $stack = [];
        }

        return $ys;
    }

    private function isWhitespace(string $ch): bool
    {
        return $ch === ' ' || $ch === "\n" || $ch === "\r" || $ch === "\t" || $ch === "\f" || $ch === "\0";
    }

    private function isDelimiter(string $ch): bool
    {
        if ($this->isWhitespace($ch)) {
            return true;
        }
        return $ch === '(' || $ch === ')' || $ch === '<' || $ch === '>'
            || $ch === '[' || $ch === ']' || $ch === '/' || $ch === '%';
    }
}
