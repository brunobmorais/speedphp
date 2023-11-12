<?php

namespace App\Libs\tcpdf\files;

use App\Libs\tcpdf\pages\CorporacaoPage;
use setasign\Fpdi\Tcpdf\Fpdi;

class DecisaoRecursoFile implements FileInterface
{
    /**
     * @param array $array
     * @return Fpdi
     */
    public static function render(array $array): Fpdi
    {
        $pdf = new CorporacaoPage(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, $array);
        $pdf->setInfo($array);

        $pdf->SetCompression(true);

        $pdf->AddPage();
        $pdf->SetAuthor('CBMTO');

        $pdf->SetTopMargin(30);
        $pdf->SetLeftMargin(20);
        $pdf->SetRightMargin(20);
        $pdf->setCellHeightRatio(1.5);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, $array["TITULO"], 0, 1, 'C', 0, '', 0);
        $pdf->Ln(8);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(190, 7, "Auto de infração: ".$array['NUMEROAUTO'], 0, 'L', 0, 1, '', '', true, 0, true, true, '0');
        $pdf->MultiCell(190, 7, "Recorrente: ".$array['RECORRENTE'], 0, 'L', 0, 1, '', '', true, 0, true, true, '0');
        $pdf->MultiCell(190, 7, "Autuado(a): ".$array['AUTUADO'], 0, 'L', 0, 1, '', '', true, 0, true, true, '0');
        $pdf->MultiCell(190, 7, "Recorrido: Corpo de Bombeiros Militar do Estado do Tocantins – CBMTO", 0, 'L', 0, 1, '', '', true, 0, true, true, '0');

        $pdf->Ln(3);
        $pdf->writeHTML($array['TEXTO']);

        $pdf->Ln(3);
        $pdf->Cell(0, 12, "Publique-se. Intime-se. Cumpra-se.", 0, 1, 'L', 0, '', 0);

        $pdf->Ln(5);
        $pdf->Cell(0, 12, "{$array["USUARIOCIDADE"]}, {$array["DATADOCUMENTO"]}", 0, 1, 'R', 0, '', 0);

        //ASSINATURA DO PROTOCOLO
        $pdf->Ln(12);
        $pdf->SetTextColor(239, 0, 0);
        $pdf->SetFont('', 'I', 8);
        $pdf->Cell(0, 1, "Assinado eletronicamente", 0, 1, 'C', 0, '', 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('', '', 12);
        $pdf->Cell(0, 1, "{$array['USUARIONOME']}", 0, 1, 'C', 0, '', 0);
        $pdf->Cell(0, 1, "{$array['USUARIOFUNCAO']}", 0, 1, 'C', 0, '', 0);
        $pdf->Cell(0, 1, "Julgador de {$array['INSTANCIA']}ª instância", 0, 1, 'C', 0, '', 0);
        $pdf->Ln(10);

        return $pdf;
    }
}
