<?php

namespace App\Libs\Tcpdf\files;

use App\Libs\tcpdf\pages\DefaultPage;
use setasign\Fpdi\Tcpdf\Fpdi;

class createPdf implements FileInterface
{
    private Fpdi $pdf;

    public function create():Fpdi
    {
        $this->pdf = new DefaultPage(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->pdf->SetCompression(true);
        $this->pdf->AddPage();
        $this->pdf->SetAuthor(CONFIG_SITE["name"]);
        $this->pdf->SetTopMargin(30);
        $this->pdf->SetLeftMargin(20);
        $this->pdf->SetRightMargin(20);

        return $this->pdf;
    }

    public function renderHtml($body): Fpdi
    {
        $this->pdf->SetFont('helvetica', '', 12);
        $this->pdf->writeHTML($body);
        $this->pdf->Ln(10);

        return $this->pdf;
    }
}
