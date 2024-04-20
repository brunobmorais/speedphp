<?php

namespace App\Controllers;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Libs\AlertLib;
use App\Libs\JwtLib;

class DownloadController extends ControllerCore implements ControllerInterface
{
    /**
     * @param $args
     * @return void
     * @example /download/?folder=funcionario&file=0ea476c97fe931509c14388feffa2de7.pdf
     */
    public function index($args = [])
    {
        try {
            $this->validateRequestMethod("GET");
            $token = $this->getParams("token");

            if (empty($token) || !(new JwtLib())->decode($token))
                $this->isLogged();

            $folder = $this->getParams("folder");
            $file = $this->getParams("file");
            $caminho = $_SERVER['DOCUMENT_ROOT'];

            if (empty($folder))
                (new AlertLib())->warning("Informe a pasta!", "/");

            if (empty($file))
                (new AlertLib())->warning("Informe o arquivo!", "/");

            switch ($folder) {
                case "funcionario":
                    $caminho .= "/public/assets/upload/files/funcionario/";
                    break;
                default:
                    $caminho .= "";
            }

            if (!file_exists($caminho . $file))
                (new AlertLib())->warning("Arquivo inválido!", "/");

            $filename = $caminho . $file;

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $filename);

            header('Cache-control: private');
            header('Content-type: ' . $type);
            header('Content-Disposition: inline; filename="' . $file . '"');
            readfile($filename);
        } catch (\Error $e) {
            return $e;
        }
    }
}
    