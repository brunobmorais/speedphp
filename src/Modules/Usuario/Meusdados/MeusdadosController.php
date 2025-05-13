<?php

namespace App\Modules\Usuario\Meusdados;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerModuleInterface;
use App\Daos\AcessoDao;
use App\Daos\EnderecoDao;
use App\Daos\PessoaDao;
use App\Daos\PessoaFisicaDao;
use App\Daos\UsuarioDao;
use App\Enums\LocalAcesso;
use App\Libs\AlertLib;
use App\Libs\FuncoesLib;
use App\Libs\SessionLib;
use App\Libs\Template\TemplateAbstract;
use App\Models\EnderecoModel;
use App\Models\PessoaFisicaModel;
use App\Models\PessoaModel;

class MeusdadosController extends ControllerCore implements ControllerModuleInterface
{
    public function index($args = null)
    {
        try {
            (new AcessoDao())->setVisita(LocalAcesso::MEUS_DADOS);

            $this->isLogged();

            $data['HEAD']['title'] = "Meus Dados";
            $data["TITLE"] = "Meus dados";
            $data["TITLEIMAGE"] = "mdi mdi-account-outline";
            $data["TITLEBREADCRUMB"] = "<li class='breadcrumb-item-custom '><a href='/atleta/'>Perfil</a></li><i class='mdi mdi-chevron-right mx-1' aria-hidden='true'></i></li><li class='breadcrumb-item-custom '><a href='./'>Meus Dados</a></li>";
            $codpessoa = SessionLib::getValue('CODPESSOA');
            $pessoaObj = (new PessoaDao)->buscarPessoaCodpessoa($codpessoa);
            $data['PESSOA'] = $pessoaObj;

            return $this->render(
                TemplateAbstract::ATLETA,
                'usuario/meusdados',
                $data
            );
        } catch (\Error $e) {
            return $e;
        }
    }

    public function action(array $args = [])
    {
        $this->isLogged();
        $this->validateRequestMethod("POST");

        $alertaClass = new AlertLib();
        $usuarioDao = new UsuarioDao();
        $pessoaDao = new PessoaDao();
        $pessoaModel = new PessoaModel($_POST);
        $enderecoDao = new EnderecoDao();
        $enderecoModel = new EnderecoModel($_POST);
        $pessoaFisicaDao = new PessoaFisicaDao();
        $pessoaFisicaModel = new PessoaFisicaModel($_POST);

        $pessoaFisicaModel->setDATANASCIMENTO((new FuncoesLib())->formatDataBanco($pessoaFisicaModel->getDATANASCIMENTO()));

        // ATUALIZA PESSOA
        $result = $enderecoDao->updateObject($enderecoModel->toObject(), "CODENDERECO={$enderecoModel->getCODENDERECO()}");
        $result = $pessoaDao->updateObject($pessoaModel->toObject(), "CODPESSOA={$pessoaModel->getCODPESSOA()}");
        $result = $pessoaFisicaDao->updateObject($pessoaFisicaModel->toObject(), "CODPESSOA={$pessoaFisicaModel->getCODPESSOA()}");

        if ($result) {
            $resultUsuarioModel = $usuarioDao->buscarCodusuario($_POST['CODUSUARIO']);
            SessionLib::setDataSession($resultUsuarioModel->getDataSession());
            $alertaClass->success("Atualização realizada com sucesso!", "/usuario/meusdados");
        } else {
            $alertaClass->danger("Erro ao atualizar informações", "/usuario/meusdados");
        }
    }
}