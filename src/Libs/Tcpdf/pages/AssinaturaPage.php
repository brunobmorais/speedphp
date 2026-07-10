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

    public function addBlockAssinatura(float $y = -1)
    {
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

        $quemAssina = !empty($this->model->getQuemAssina())
            ? "por <b>" . $this->model->getQuemAssina() . "</b> "
            : "";

        $url   = $this->model->getUrlValidacao();
        $token = $this->model->getToken();

        $html = "Assinado eletronicamente {$quemAssina}em {$this->model->getDataAssinatura()},
        com validade jurídica nos termos da Lei nº 14.063/2020.
        Para confirmar a autenticidade deste documento, acesse:
        <a href='{$url}?token={$token}'>{$url}</a>
        e digite o código verificador <b>{$token}</b>.";

        if ($y < 0) {
            $y = $this->tMargin;
        }

        $x            = $this->lMargin;
        $qrSize       = 18;
        $alturaNecessaria = 30;

        if ($y + $alturaNecessaria > ($this->getPageHeight() - $this->getBreakMargin())) {
            $this->AddPage();
            $y = $this->tMargin;
        }

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