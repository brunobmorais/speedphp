<?php

namespace App\Libs\Tcpdf\pages;

use App\Libs\Tcpdf\model\AssinaturaModel;
use setasign\Fpdi\Tcpdf\Fpdi;

//============================================================+
// File name   : example_001.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 001 for TCPDF class
//               Default Header and Footer
//
// Author:  Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+


/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick. tcpdf
 * @abstract TCPDF - Example: Default Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Extend the TCPDF class to create custom Header and Footer
class AssinaturaPage extends Fpdi
{
    protected AssinaturaModel $model;

    public function setInfo(AssinaturaModel $model)
    {
        $this->model = $model;
    }

    //Page header
    public function Header()
    {
        // Logo
        $this->SetMargins(0, 0, 0);
        $this->Cell(0, 0, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function addBlockAssinatura()
    {
        // estilo do QRCode
        $style = [
            'border'  => false,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => [255, 255, 255],
        ];

        $this->SetRightMargin(15);
        $this->SetLeftMargin(15);
        $this->setCellPaddings(2, 2, 2, 2);
        $this->SetFont('helvetica', '', 9);
        $this->SetFillColor(255, 255, 255);

        $quemAssina = !  empty($this->model->getQuemAssina())
            ? "por <b>" . $this->model->getQuemAssina() . "</b> "
            : "";

        $url   = $this->model->getUrlValidacao();
        $token = $this->model->getToken();

        $html = "Assinado eletronicamente {$quemAssina}em {$this->model->getDataAssinatura()},
        com validade jurídica nos termos da Lei nº 14.063/2020. 
        Para confirmar a autenticidade deste documento, acesse: 
        <a href='{$url}? token={$token}'>{$url}</a>
        e digite o código verificador <b>{$token}</b>.  
    ";

        // --- NOVA LÓGICA:  Encontrar o final real do conteúdo ---
        $this->setPage($this->getPage());

        // Tenta obter a última posição Y válida do conteúdo
        $y = $this->GetY()+30;

        // Se Y está no topo (página importada), usar margem + pequeno offset
        if ($y < 30) {
            // Em vez de ir para o final, adiciona espaço após a margem superior
            $y = $this->tMargin + 10; // 10mm após a margem superior
        } else {
            // Adiciona espaço após o último conteúdo
            $y = $y + 10;
        }

        $x = $this->lMargin;
        $alturaNecessaria = 35;

        // Verifica se há espaço na página atual
        if ($y + $alturaNecessaria > ($this->getPageHeight() - $this->getBreakMargin())) {
            $this->AddPage();
            $y = $this->tMargin;
            $x = $this->lMargin;
        }

        // --- QRCode à esquerda ---
        $qrSize = 18;
        $this->write2DBarcode(
            "{$url}?token={$token}",
            'QRCODE,H',
            $x,
            $y,
            $qrSize,
            $qrSize,
            $style,
            'N'
        );

        // --- texto ao lado do QRCode ---
        $this->SetXY($x + $qrSize + 3, $y);

        $this->writeHTMLCell(
            0,
            0,
            '',
            '',
            $html,
            1,
            1,
            true,
            true,
            'J',
            true
        );
    }

}