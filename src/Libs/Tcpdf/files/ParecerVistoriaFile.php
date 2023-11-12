<?php

namespace App\Libs\tcpdf\files;

use App\Libs\tcpdf\pages\DistecPage;
use setasign\Fpdi\Tcpdf\Fpdi;

class ParecerVistoriaFile implements FileInterface
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

        $pdf->SetLeftMargin(20);
        $pdf->SetRightMargin(20);
        //$pdf->SetY(45);
        $pdf->SetFont('Times', 'B', 16);
        $pdf->Cell(0, 6, 'Parecer de Vistoria', 0, 1, 'C', 0, '', 0);
        // Line break
        $pdf->Ln(10);

        $pdf->SetFont('Times', '', 12);
        $pdf->MultiCell(0, 6, "Proprietário: <b>" . $array['proprietario'] . "</b>\n", 0, 'L', 0, 1, '', '', true, 0, true, true, '0');
        $pdf->MultiCell(0, 6, "CPF/CNPJ: <b>" . $array['cpfcnpj'] . "</b>\n", 0, 'L', 0, '1', '', '', true, 0, true, true, '0');
        $pdf->MultiCell(0, 6, "Unidade Bombeiro Militar: <b>" . $array['unidadebm'] . "</b>\n", 0, 'L', 0, '1', '', '', true, 0, true, true, '0');
        $pdf->MultiCell(0, 6, "Processo da edificação: <b>" . $array['processo'] . "</b>\n", 0, 'L', 0, '1', '', '', true, 0, true, true, '0');
        $pdf->MultiCell(0, 6, "Área Construída: <b>" . $array['areaconstruida'] . "</b>\n", 0, 'L', 0, '1', '', '', true, 0, true, true, '0');
        $pdf->MultiCell(0, 6, "Endereço da Edificação: <b>" . $array['endereco'] . "</b>\n", 0, 'L', 0, 1, '', '', true, 0, true, true, '0');
        $pdf->MultiCell(0, 6, "Nome Fantasia: <b>" . $array['nomefantasia'] . "</b>\n", 0, 'L', 0, '1', '', '', true, 0, true, true, '0');

        $pdf->Ln(5);
        $pdf->SetFont('Times', 'B', 16);
        $pdf->Cell(0, 6, 'Relatório Conclusivo', 0, 1, 'C', 0, '', 0);
        $pdf->Ln(10);
        $pdf->SetFont('Times', '', 12);
        $tagvs = array(
            'p' => array(0 => array('n' => 0, 'h' => ''), 1 => array('n' => 0, 'h' => ''))
        );
        $pdf->setHtmlVSpace($tagvs);
        $pdf->MultiCell(
            0,
            6,
            $array['parecer'] . "\n",
            0,
            'J',
            0,
            1,
            '',
            '',
            true,
            0,
            true,
            true,
            '0'
        );

        $pdf->Ln(5);
        $pdf->SetFont('Times', 'B', 16);
        $pdf->Cell(0, 6, 'Parecer Final', 0, 1, 'C', 0, '', 0);
        $pdf->Ln(10);
        $pdf->SetFont('Times', '', 12);
        if ($array['aprovado']) {
            $pdf->Cell(0, 6, "( x ) Aprovado", 0, 1.5, 'L', 0, '', 0);
            $pdf->Cell(0, 6, "(    ) Reprovado", 0, 1.5, 'L', 0, '', 0);
        } else {
            $pdf->Cell(0, 6, "(    ) Aprovado", 0, 1.5, 'L', 0, '', 0);
            $pdf->Cell(0, 6, "( x ) Reprovado", 0, 1.5, 'L', 0, '', 0);
        }

        $pdf->Ln(5);
        $pdf->SetFont('Times', 'B', 16);
        $pdf->Cell(0, 6, 'Ainda necessita entregar os documentos exigidos na vistoria', 0, 1, 'C', 0, '', 0);
        $pdf->Ln(10);
        $pdf->SetFont('Times', '', 12);
        if (!empty($array['docposvistoria'])) {
            $pdf->Cell(0, 6, "( x ) Sim", 0, 1.5, 'L', 0, '', 0);
            $pdf->Cell(0, 6, "(    ) Não", 0, 1.5, 'L', 0, '', 0);
        } else {
            $pdf->Cell(0, 6, "(    ) Sim", 0, 1.5, 'L', 0, '', 0);
            $pdf->Cell(0, 6, "( x ) Não", 0, 1.5, 'L', 0, '', 0);
        }

        $pdf->Ln(10);
        $pdf->Cell(0, 6, "Data: " . $array['dataatual'], 0, 1.5, 'L', 0, '', 0);

        $pdf->Ln(12);

        //ASSINATURA DO PROTOCOLO
        $pdf->SetTextColor(239, 0, 0);
        $pdf->SetFont('', 'I', 8);
        $pdf->Cell(0, 1, "Assinado digitalmente", 0, 0.1, 'C', 0, '', 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('', '', 12);
        $pdf->Cell(0, 1, "{$array['nomeusuario']}", 0, 0.1, 'C', 0, '', 0);
        $pdf->Cell(0, 1, "{$array['funcao']}", 0, 0.1, 'C', 0, '', 0);

        $pdf->Ln(10);

        return $pdf;
    }
}
