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
            // basename IMPEDE PATH TRAVERSAL (../)
            $file = basename($this->getParams("file"));

            if (empty($folder))
                (new AlertLib())->warning("Informe a pasta!", "/");

            if (empty($file))
                (new AlertLib())->warning("Informe o arquivo!", "/");

            // SOMENTE PASTAS LIBERADAS PARA DOWNLOAD
            $pastas = [
                "funcionario" => "/public/assets/upload/files/funcionario/",
            ];

            if (!isset($pastas[$folder]))
                (new AlertLib())->warning("Pasta inválida!", "/");

            $caminho = realpath(dirname(__DIR__, 2) . $pastas[$folder]);
            $filename = realpath($caminho . "/" . $file);

            if (!$caminho || !$filename || !str_starts_with($filename, $caminho . DIRECTORY_SEPARATOR) || !is_file($filename))
                (new AlertLib())->warning("Arquivo inválido!", "/");

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
    