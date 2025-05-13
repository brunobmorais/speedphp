<?php
namespace App\Modules\Usuario\Mudarfotoperfil;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerModuleInterface;
use App\Daos\PessoaDao;
use App\Libs\AlertLib;
use App\Libs\CookieLib;
use App\Libs\FileLib;
use App\Libs\SessionLib;
use App\Models\PessoaModel;

class MudarfotoperfilController extends ControllerCore implements ControllerModuleInterface
{
    public function index($args = null)
    {
        try {
            $this->isLogged();
            $this->validateRequestMethod("POST");
            $diretorioImgPerfil = "/public/assets/upload/pessoa/";
            $fileLib = new FileLib();
            $pessoaModel = new PessoaModel();
            if ($this->postParams("IMAGEM_BASE64")) {
                $nomeArquivo = $fileLib->uploadFileBase64($this->postParams("IMAGEM_BASE64"), $diretorioImgPerfil);

                $nomeAntigo = !empty($this->postParams('IMAGEM')) ? $this->postParams('IMAGEM') : "default.png";
                if ($nomeAntigo != 'default.png') {
                    $fileLib->removeFile($diretorioImgPerfil . $nomeAntigo);
                }
                $pessoaModel->setIMAGEM($nomeArquivo);
                (new PessoaDao())->updateObject($pessoaModel->toObject(), "CODPESSOA = " . $this->postParams('CODPESSOA'));
            }
            (new AlertLib())->success("Sua foto de perfil foi atualizada!", $this->postParams('URL_RETORNO'));
        } catch (\Error $e) {
            return $e;
        }
    }

    public function action(array $args = [])
    {

    }
}