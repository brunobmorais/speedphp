<?php

namespace App\Libs\tcpdf\files;

use App\Libs\tcpdf\pages\CorporacaoPage;
use setasign\Fpdi\Tcpdf\Fpdi;

class HtmlFile implements FileInterface
{
    /**
     * @param array $array
     * @return Fpdi
     */
    public static function render(array $array): Fpdi
    {
        $pdf = new CorporacaoPage(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setInfo($array);
        $pdf->SetCompression(true);

        $pdf->AddPage();
        $pdf->SetAuthor('CBMTO');

        $pdf->SetTopMargin(30);
        $pdf->SetLeftMargin(20);
        $pdf->SetRightMargin(20);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->writeHTML($array['TEXTO']);
        $pdf->Ln(10);

        return $pdf;
    }
}
