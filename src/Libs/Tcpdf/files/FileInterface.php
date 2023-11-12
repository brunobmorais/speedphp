<?php

namespace App\Libs\tcpdf\files;

use setasign\Fpdi\Tcpdf\Fpdi;

interface FileInterface
{
    public static function render(array $array): Fpdi;
}
