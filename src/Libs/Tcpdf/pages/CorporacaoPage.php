<?php

namespace App\Libs\tcpdf\pages;

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
class CorporacaoPage extends Fpdi
{
    protected $array;

    public function setInfo($info)
    {
        $this->array = $info;
    }
    public function Header()
    {
        //Logo
        $diretorioImg = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR;


        $this->Image($diretorioImg.'logo-cbmto/logo-default.png', 20, 3, 20, 20);
        $this->Image($diretorioImg.'logo-estado/logo-default.png', 50, 3, 50, 20);

        $this->SetFont('helvetica', 'B', 8);
        $this->MultiCell(80, 20, $this->array["UNIDADEPAI"], 0, 'L', 0, '', 120, 7, 'M', 'M', true);
        $this->MultiCell(80, 20, $this->array["UNIDADEFILHO"], 0, 'L', 0, '', 120, 10.5, 'M', 'M', true);

        $this->SetFont('helvetica', '', 7);
        $this->MultiCell(80, 30, $this->array["UNIDADEENDERECO"], 0, 'L', 0, '', 120, 14.5, 'M', 'M', true);

        $styleLinha = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0));
        $this->Line(20, 28, 190, 28, $styleLinha);



    }

    // Page footer
    public function Footer()
    {

        // Position at 15 mm from bottom
        //$this->SetY(-15);
        //$this->Image(ROOT . DS.'../../img/rodape-pdf.jpg',10,270,190,30);
    }
}
