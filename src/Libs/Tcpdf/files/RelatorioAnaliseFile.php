<?php

namespace App\Libs\tcpdf\files;

use App\Libs\tcpdf\pages\DistecPage;
use setasign\Fpdi\Tcpdf\Fpdi;

class RelatorioAnaliseFile implements FileInterface
{
    /**
     * @param array $array
     * @return Fpdi
     */
    public static function render(array $array): Fpdi
    {
        $pdf = new DistecPage(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCompression(true);

        $pdf->SetTopMargin(50);
        $pdf->SetCompression(true);
        $pdf->AddPage();
        $pdf->SetAuthor('CBMTO');

        $pdf->SetCompression(true);

        $pdf->SetLeftMargin(20);
        $pdf->SetRightMargin(20);
        //$pdf->SetY(45);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 6, "RELATÓRIO DE ANÁLISE ({$array["qtdcorrecoes"]})", 0, 1, 'C', 0, '', 0);
        // Line break
        $pdf->Ln(10);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(80, 7, "<b>Entrada: </b>" . $array['dataentrada'] . "\n", 0, 'L', 0, '0', '', '', true, 0, true, true, '0');
        $pdf->MultiCell(80, 7, "<b>Projeto: </b>" . $array['projeto'] . "\n", 0, 'L', 0, '1', '', '', true, 0, true, true, '0');
        $pdf->MultiCell(0, 7, "<b>Proprietário: </b>" . $array['proprietario'] . "\n", 0, 'L', 0, 1, '', '', true, 0, true, true, '0');
        $pdf->MultiCell(0, 7, "<b>Ocupação/Uso: </b>" . $array['ocupacao'] . "\n", 0, 'L', 0, '1', '', '', true, 0, true, true, '0');
        $pdf->MultiCell(80, 7, "<b>Altura: </b>" . $array['altura'] . " m \n", 0, 'L', 0, '0', '', '', true, 0, true, true, '0');
        $pdf->MultiCell(80, 7, "<b>Área Construída: </b>" . $array['areaconstruida'] . " m² \n", 0, 'L', 0, '1', '', '', true, 0, true, true, '0');
        $pdf->MultiCell(0, 7, "<b>Carga de incêndio: </b>" . $array['cargaincendio'] . " Mj/m² \n", 0, 'L', 0, 1, '', '', true, 0, true, true, '0');

        $pdf->Ln(5);
        $tagvs = array(
            'p' => array(0 => array('n' => 0, 'h' => ''), 1 => array('n' => 0, 'h' => ''))
        );
        $pdf->setHtmlVSpace($tagvs);
        $pdf->MultiCell(
            0,
            6,
            $array['relatorio'] . "\n",
            0,
            'J',
            0,
            1,
            '',
            '',
            true,
            0,
            false,
            true,
            '0'
        );

        $pdf->Ln(10);
        $pdf->Cell(0, 6, "Data: " . $array['dataatual'], 0, 1.5, 'R', 0, '', 0);

        $pdf->Ln(12);

        //ASSINATURA DO PROTOCOLO
        $pdf->SetTextColor(239, 0, 0);
        $pdf->SetFont('', 'I', 8);
        $pdf->Cell(0, 1, "Assinado eletronicamente", 0, 0.1, 'C', 0, '', 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('', '', 12);
        $pdf->Cell(0, 1, "{$array['nomeusuario']}", 0, 0.1, 'C', 0, '', 0);
        $pdf->Cell(0, 1, "{$array['funcao']}", 0, 0.1, 'C', 0, '', 0);

        $pdf->Ln(10);

        return $pdf;
    }
}
