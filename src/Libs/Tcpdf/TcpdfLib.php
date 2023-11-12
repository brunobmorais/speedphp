<?php

namespace App\Libs\Tcpdf;

/**
 * Created by PhpStorm.
 * User: Bruno Morais2
 * Date: 23/12/2018
 * Time: 23:41
 */

use App\Libs\tcpdf\files\FileInterface;
use App\Libs\tcpdf\pages\AssinaturaPage;
use App\Libs\tcpdf\pages\ImgToPdfPage;
use setasign\Fpdi\PdfReader;
use setasign\Fpdi\Tcpdf\Fpdi;

// create new PDF document


class TcpdfLib
{
    private array $array = [];
    private bool $view = false;
    private FileInterface $function;
    private Fpdi $pdf;

    public function __construct()
    {
    }

    public function imageToPdf($fileImage, $filePdf): void
    {
        if (!empty($array)) {
            $this->array = $array;
        }

        $pdf = new ImgToPdfPage(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCompression(true);
        $pdf->AddPage();

        $pdf->setJPEGQuality(75);

        $img_file = $fileImage;

        $pdf->Image($img_file, 0, 0, 210, 0, '', '', 'C', true, 300, 'C', false, false, 0, '', false, false);

        $pdf_string = $pdf->Output("protocolo.pdf", 'S');
        file_put_contents($filePdf, $pdf_string);
    }

    public function assinarDocumento(array $array = []): void
    {

        if (!empty($array)) {
            $this->array = $array;
        }

        $pdf = new AssinaturaPage(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCompression(true);

        $pdf->setInfo($this->array);

        try {
            $pagecount = $pdf->setSourceFile($this->array['arquivopdf']);

            for ($i = 1; $i <= $pagecount; $i++) {
                $tplidx = $pdf->importPage($i, PdfReader\PageBoundaries::BLEED_BOX);
                $size = $pdf->getTemplatesize($tplidx);
                $orientation = $size['orientation'];
                $pdf->AddPage($orientation, array($size['width'], $size['height'] + 25));
                $pdf->useTemplate($tplidx);
            }
        } catch (\Exception $exception) {
            $outputName = md5(uniqid()) . ".pdf";
            $cmd = "gs -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -sOutputFile={$outputName} {$this->array['arquivopdf']}";
            shell_exec($cmd);
            shell_exec("mv -f {$outputName} {$this->array['arquivopdf']}");
            $this->assinarDocumento($this->array);
            return;
        }

        $pdf_string = $pdf->Output("protocolo.pdf", 'S');
        file_put_contents($this->array['arquivopdf'], $pdf_string);
    }

    public function assinarProjeto(array $array = []): void
    {
        if (!empty($array)) {
            $this->array = $array;
        }


        $style = array(
            'border' => false,
            'padding' => 0,
            'fgcolor' => array(233, 233, 233),
            'bgcolor' => false
        );
        $pdf = new AssinaturaPage(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCompression(true);

        $pdf->SetMargins(0, 0, 0);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        $pdf->setInfo($this->array);

        try {
            $pagecount = $pdf->setSourceFile($this->array['arquivopdf']);

            for ($i = 1; $i <= $pagecount; $i++) {
                $pageId = $pdf->importPage($i, PdfReader\PageBoundaries::BLEED_BOX);
                $size = $pdf->getTemplateSize($pageId);
                $orientation = $size['orientation'];
                //var_dump($size); exit;
                $alturaQrcode = $size['height'] * 50 / 100;
                $larguraQrcode = $size['width'] * 50 / 100;
                $tamanhoFont = $size['height'] * 5 / 100;
                $posicaoX = ($size['width'] / 2) - $larguraQrcode / 2;
                $posicaoY = ($size['height'] / 2) - $alturaQrcode / 2;

                $pdf->AddPage($orientation, array($size['width'], $size['height'] + 25));
                $pdf->write2DBarcode('http://intranet.bombeiros.to.gov.br/dist/validadocumento.php?chave=' . $this->array['codigovalidadorarquivo'], 'QRCODE,H', $posicaoX, $posicaoY, $larguraQrcode, $alturaQrcode, $style, 'C');
                $pdf->SetTextColor(233, 233, 233);
                $pdf->SetFont('', 'B', $tamanhoFont);
                $pdf->MultiCell($larguraQrcode, $tamanhoFont, $this->array['codigovalidadorarquivo'], 0, 'C', 0, 1, $posicaoX, $posicaoY - 20, true, 0, true, true, '0');
                $pdf->useTemplate($pageId);
            }
        } catch (\Exception $exception) {
            $outputName = md5(uniqid()) . ".pdf";
            $cmd = "gs -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -sOutputFile={$outputName} {$this->array['arquivopdf']}";
            shell_exec($cmd);
            shell_exec("mv -f {$outputName} {$this->array['arquivopdf']}");
            $this->assinarProjeto($this->array);
            return;
        }

        $pdf_string = $pdf->Output("protocolo.pdf", 'S');
        file_put_contents($this->array['arquivopdf'], $pdf_string);
    }

    public function mesclarDocumentos(array $array, $caminhoArquivo, $view = false)
    {
        if (!empty($array)) {
            $this->array = $array;
        }

        //$pdf = new TCPDI(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false,$this->array);
        @$pdf = new Fpdi();
        $pdf->SetCompression(true);

        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        foreach ($this->array as $arquivo) {
            if (file_exists($arquivo)) {
                try {
                    $pagecount = $pdf->setSourceFile($arquivo);
                    for ($i = 1; $i < $pagecount + 1; $i++) {
                        $tplidx = $pdf->importPage($i, PdfReader\PageBoundaries::BLEED_BOX);
                        $size = $pdf->getTemplatesize($tplidx);
                        $orientation = $size['orientation'];

                        $pdf->AddPage($orientation, array($size['width'], $size['height']));
                        //$pdf->setPageFormatFromTemplatePage($i, $orientation);
                        $pdf->useTemplate($tplidx);
                    }
                } catch (\Exception $exception) {
                    $outputName = md5(uniqid()) . ".pdf";
                    $cmd = "gs -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -sOutputFile={$outputName} {$arquivo}";
                    shell_exec($cmd);
                    shell_exec("mv -f {$outputName} {$arquivo}");
                    $this->mesclarDocumentos($this->array, $caminhoArquivo);
                    return;
                }
            }
        }

        if (!$view) {
            $pdf_string = $pdf->Output("protocolo.pdf", 'S');
            file_put_contents($caminhoArquivo, $pdf_string);
        } else {
            $pdf_string = $pdf->Output("arquivos_mesclado.pdf", 'I');
        }
    }

    public function createPdf(FileInterface $function, array $array = [], bool $view = false): self
    {
        $this->array = $array;
        $this->view = $view;
        $this->function = $function;

        if (!$view) {
            $pdfString = $function::render($this->array)->Output("arquivo.pdf", "S");
            file_put_contents($this->array['arquivopdf'], $pdfString);
            return $this;
        }

        $pdfString = $function::render($this->array)->Output("arquivo.pdf", "I");
        return $this;
    }
}
