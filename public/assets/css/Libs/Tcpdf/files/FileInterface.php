<?php

namespace App\Libs\tcpdf\files;

use setasign\Fpdi\Tcpdf\Fpdi;

interface FileInterface
{
    public function renderHtml(array $array): Fpdi;
}
