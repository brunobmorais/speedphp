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
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+


/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
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
    public function Footer()
    {

        // set style for barcode
        // new style
        $style = array(
            'border' => false,
            'padding' => 0,
            'fgcolor' => array(0,0,0),
            'bgcolor' => array(255,255,255)
        );

        $this->SetRightMargin(3);
        $this->SetLeftMargin(3);
        $this->setCellPaddings(2, 2, 2, 2);
        $this->SetFont('helvetica', 'I', 9);
        $quemAssina = !empty($this->model->getQuemAssina())?"por <b>" . $this->model->getQuemAssina() . "</b> ":"";
        $html = "Assinado eletronicamente {$quemAssina}em {$this->model->getDataAssinatura()}. Para confirmar a validade deste documento, acesse: <a href='{$this->model->getUrlValidacao()}?token={$this->model->getToken()}'>{$this->model->getUrlValidacao()}</a> e digite o codigo verificador <b>{$this->model->getToken()}</b>";
        $this->write2DBarcode($this->model->getUrlValidacao()."?token=".$this->model->getToken(), 'QRCODE,H', 2, $this->getPageHeight()-19, 17, 17, $style, 'L');
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(0, 20, $html, 1, 'J', 1, 1, '22', '-19', true, 0, true, true, false, '0');

    }

}
