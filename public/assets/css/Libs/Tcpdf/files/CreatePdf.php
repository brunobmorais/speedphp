<?php

namespace App\Libs\Tcpdf\files;

use App\Libs\Tcpdf\pages\DefaultPage;
use setasign\Fpdi\Tcpdf\Fpdi;

class CreatePdf implements FileInterface
{
    private Fpdi $pdf;

    public function renderHtml($body, $fileName = "", $print = true): Fpdi
    {
        $fileName = empty($fileName) ? "arquivo.pdf" : $fileName;
        $this->pdf = new DefaultPage(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->pdf->SetCompression(true);
        $this->pdf->SetCreator(CONFIG_SITE["name"]);
        $this->pdf->SetAuthor(CONFIG_SITE["name"]);
        // Remove cabeçalho e rodapé padrão
        $this->pdf->setPrintHeader(true);
        $this->pdf->setPrintFooter(false);

        // Margens reduzidas
        $this->pdf->SetMargins(20, 25, 20);
        $this->pdf->SetAutoPageBreak(true, 20); // Alterado de 10 para 25

        $this->pdf->AddPage();

        $this->pdf->SetFont('helvetica', '', 12);
        $this->pdf->writeHTML($body, true, false, true, false, '');

        if ($print)
            $this->pdf->Output($fileName, 'I');
        else
            $this->pdf->Output($_SERVER['DOCUMENT_ROOT'].$fileName, 'F');

        return $this->pdf;
    }
}
