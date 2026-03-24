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
class ImgToPdfPage extends Fpdi
{
    protected $img;

    public function setInfo($info)
    {
        $this->img = $info;
    }

    // Page footer
    public function Header()
    {
        // get the current page break margin
        $this->SetMargins(0, 0, 0);

    }
}
