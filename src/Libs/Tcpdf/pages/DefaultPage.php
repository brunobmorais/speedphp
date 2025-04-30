<?php

namespace App\Libs\Tcpdf\pages;

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
class DefaultPage extends Fpdi
{

    public function Header()
    {
        //Logo
        $diretorioImg = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "public". DIRECTORY_SEPARATOR ."assets" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR;


        $this->Image($diretorioImg.'logo.png', 50, 3, 45, 20);

        $this->SetFont('helvetica', 'B', 8);
        $this->MultiCell(80, 20, CONFIG_SITE["nameFull"], 0, 'L', 0, '', 110, 5, 'M', 'M', true);
        $this->MultiCell(80, 20, CONFIG_SITE["email"], 0, 'L', 0, '', 110, 9, 'M', 'M', true);
        $this->MultiCell(80, 20, CONFIG_SITE["phone"], 0, 'L', 0, '', 110, 13, 'M', 'M', true);

        $this->SetFont('helvetica', '', 7);
        $this->MultiCell(80, 30, CONFIG_SITE["andress"], 0, 'L', 0, '', 110, 17, 'M', 'M', true);

        $styleLinha = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
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
