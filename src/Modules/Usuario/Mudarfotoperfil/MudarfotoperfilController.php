<?php
namespace App\Modules\Usuario\Mudarfotoperfil;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerModuleInterface;
use App\Daos\ModuloDao;
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
            $alerta = new AlertLib();
            $pessoaDao = new PessoaDao();
            $urlRetorno = $this->urlRetornoSegura($_POST['URL_RETORNO'] ?? '');

            $codpessoaSessao = (int) SessionLib::getValue("CODPESSOA");
            $codpessoa = (int) $this->postParams('CODPESSOA') ?: $codpessoaSessao;

            // ALTERAR A FOTO DE OUTRA PESSOA EXIGE PRIVILÉGIO DE ALTERAR EM SISTEMA/PESSOAS
            if ($codpessoa !== $codpessoaSessao) {
                $servico = (new ModuloDao())->buscaServicoUsuario(SessionLib::getValue("CODUSUARIO"), "sistema", "pessoas");
                if (empty($servico) || $servico["ALTERAR"] != "1") {
                    $alerta->warning("Você não tem privilégio para executar essa ação!", $urlRetorno);
                }
            }

            if ($this->postParams("IMAGEM_BASE64")) {
                $nomeArquivo = $fileLib->uploadFileBase64($this->postParams("IMAGEM_BASE64"), $diretorioImgPerfil);
                if (empty($nomeArquivo)) {
                    $alerta->warning("Imagem inválida! Envie um arquivo JPG, PNG, GIF ou WEBP.", $urlRetorno);
                }

                // NOME DA IMAGEM ANTIGA VEM DO BANCO, NUNCA DO FORMULÁRIO
                $pessoaAtual = $pessoaDao->select("IMAGEM", "WHERE CODPESSOA=?", [$codpessoa]);
                $nomeAntigo = basename($pessoaAtual[0]->IMAGEM ?? "");

                $pessoaModel = new PessoaModel();
                $pessoaModel->setIMAGEM($nomeArquivo);
                $pessoaDao->updateObject($pessoaModel->toObject(), "CODPESSOA = ?", [$codpessoa]);

                if (!empty($nomeAntigo) && $nomeAntigo != 'default.png') {
                    $fileLib->removeFile($diretorioImgPerfil . $nomeAntigo);
                }
            }
            $alerta->success("Sua foto de perfil foi atualizada!", $urlRetorno);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function action(array $args = [])
    {

    }

    /**
     * Mantém apenas caminho e query da URL de retorno, evitando redirecionamento para outro domínio
     */
    private function urlRetornoSegura(string $url): string
    {
        $partes = parse_url($url);
        $path = $partes['path'] ?? '/';

        if (!str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '\\')) {
            return '/';
        }

        return $path . (isset($partes['query']) ? '?' . $partes['query'] : '');
    }
}
