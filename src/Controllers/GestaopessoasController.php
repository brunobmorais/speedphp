<?php

namespace App\Controllers;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Daos\GpAndamentoDao;
use App\Daos\GpArquivoDao;
use App\Daos\GpCargoDao;
use App\Daos\GpCategoriaFuncionarioDao;
use App\Daos\GpDepartamentoDao;
use App\Daos\GpDocumentoDao;
use App\Daos\GpFuncionarioBancoDao;
use App\Daos\GpFuncionarioCargoDao as DaosGpFuncionarioCargoDao;
use App\Daos\GpFuncionarioDao;
use App\Daos\GpFuncionarioDepartamentoDao;
use App\Daos\GpFuncionarioDependenteDao;
use App\Daos\GpTipoArquivoDao;
use App\Daos\GpTipoDependenteDao;
use App\Daos\LogDao;
use App\Daos\PessoaDao;
use App\Daos\PessoaFisicaDao;
use App\Libs\AlertLib;
use App\Libs\DownloadLib;
use App\Libs\FileImageLib;
use App\Libs\FileLib;
use App\Libs\FuncoesLib;
use App\Libs\SessionLib;
use App\Libs\TableLib;
use App\Libs\Template\TemplateAbstract;
use App\Models\EnderecoModel;
use App\Models\GpArquivoModel;
use App\Models\GpDocumentoModel;
use App\Models\GpFuncionarioBancoModel;
use App\Models\GpFuncionarioCargoModel;
use App\Models\GpFuncionarioDepartamentoModel;
use App\Models\GpfuncionariodependenteModel;
use App\Models\GpFuncionarioModel;
use App\Models\PessoaFisicaModel;
use App\Models\PessoaModel;
use Error;
use Illuminate\Support\Facades\Log;
use stdClass;

class GestaopessoasController extends ControllerCore implements ControllerInterface
{
    /*
    * chama a view index.php   /
    */
    public function index($args = [])
    {
        try {
            return $this->render(TemplateAbstract::SERVICOS);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function funcionarios($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $listaFuncionarios = (new GpFuncionarioDao)->buscarTodos($this->getParams("buscar"));
            $tableComponents = new TableLib();

            $categorias = (new GpCategoriaFuncionarioDao)->buscarTodos();
            $tableComponents->init(
                $listaFuncionarios,
                array('Nome', 'CPF', ''),
                array("buscar" => $this->getParams('buscar'))
            );

            foreach ($listaFuncionarios as $key => $funcionario) {
                if ($tableComponents->checkPagination($key)) {
                    $chave = FuncoesLib::searchArray($funcionario->CODCATEGORIA, $categorias, 'CODCATEGORIA');
                    $situacao = $funcionario->SITUACAO == "1" ? "<span class='mdi mdi-circle text-success'></span> " : "<span class='mdi mdi-circle text-warning'></span> ";
                    $tableComponents->addCol($situacao . $funcionario->NOME);
                    $tableComponents->addCol((new FuncoesLib())->formatCpfUsuario($funcionario->CPF));
                    $tableComponents->addCol(("<a href='{$data["SERVICO"]["URL"]}-perfil/?id={$funcionario->CODPESSOA}&pg={$this->getParams('pg')}&buscar={$this->getParams('buscar')}' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-pencil-outline'></span> Editar</a>"));
                    $tableComponents->addRow();
                }
            }
            $data["TABLE_COMPONENT"] = $tableComponents->render();
            $data["tableButtonInputPlaceholder"] = "Buscar...";
            $data["topTable"] = "components/button_table/button_table.html.twig";

            return $this->render(
                TemplateAbstract::LOGGED,
                "components/page_table_default",
                $data,
            );
            // montar tabela
        } catch (\Error $e) {
            return $e;
        }
    }

    public function funcionarioscadastro($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            if ($this->getParams('id')) {
                $pessoaObj = (new PessoaDao)->buscarPessoa($this->getParams('id'));
                $data['PESSOA'] = $pessoaObj[0];
            }
            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["TEMPLATE"], $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function funcionarioscadastroaction($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            $this->validateRequestMethod("POST");

            if (!$data["SERVICO"]['SALVAR'] == 1) (new AlertLib)->warning('Você não tem permissão para realizar essa ação', "{$data["SERVICO"]["URL"]}/");


            $endereco = (new EnderecoModel());
            $endereco->setCEP($this->postParams('CEP'));
            $endereco->setBAIRRO($this->postParams('BAIRRO'));
            $endereco->setCODCIDADE($this->postParams('CODCIDADE'));
            $endereco->setLOGRADOURO($this->postParams('LOGRADOURO'));
            $endereco->setNUMERO($this->postParams('NUMERO'));
            $endereco->setCOMPLEMENTO($this->postParams('COMPLEMENTO'));

            $pessoa = (new PessoaModel());
            $pessoa->setTIPOPESSOA($this->postParams('TIPOPESSOA'));
            $pessoa->setNOME((new FuncoesLib())->textoPrimeiraLetraMaiusculoCadaPalavra($this->postParams('NOME')));
            $pessoa->setTELEFONE($this->postParams('TELEFONE'));
            $pessoa->setEMAIL($this->postParams('EMAIL'));

            $pessoaFisica = (new PessoaFisicaModel());
            $pessoaFisica->setCPF((new FuncoesLib)->formatCpfBanco($this->postParams('CPF')));
            $pessoaFisica->setDATANASCIMENTO($this->postParams('DATANASCIMENTO'));
            $pessoaFisica->setSEXO($this->postParams('SEXO'));

            $funcionario = new GpFuncionarioModel();
            $funcionario->setNOMESOCIAL($this->postParams('NOMESOCIAL'));
            $funcionario->setCODCATEGORIA($this->postParams('CODCATEGORIA'));

            $result = (new PessoaFisicaDao)->inserirFuncionario($endereco, $pessoa, $pessoaFisica, $funcionario);

            if (empty($result)) (new AlertLib)->warning("Opss! Aconteceu um erro", "{$data["SERVICO"]["URL"]}-cadastro/");

            if ($result["error"]) (new AlertLib)->warning($result["message"], "{$data["SERVICO"]["URL"]}-cadastro/");

            (new GpAndamentoDao())->insertAndamento($result["codfuncionario"], "Cadastro de funcionário ao sistema")(new AlertLib)->success($result["message"], "{$data["SERVICO"]["URL"]}-perfil/?id={$result["codpessoa"]}");
        } catch (\Error $e) {
            return $e;
        }
    }

    public function funcionariosperfil($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            if (empty($this->getParams('id'))) (new AlertLib())->warning("Selecine um funcionário", $data["SERVICO"]["URL"]);

            $codpessoa = $this->getParams('id');
            $pessoaObj = (new PessoaDao)->buscarPessoa($codpessoa);
            $data['PESSOA'] = $pessoaObj[0];
            $data['PESSOAFISICA'] = (new PessoaFisicaDao)->buscarPessoa($codpessoa);
            $data['FUNCIONARIO'] = (new GpFuncionarioDao)->buscarPessoa($codpessoa);
            $data['FUNCIONARIO_CATEGORIA'] = ((new GpCategoriaFuncionarioDao)->buscar(['CODCATEGORIA' => $data['FUNCIONARIO']->CODCATEGORIA])[0]);

            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["TEMPLATE"], $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function funcionariosperfilaction($args = [])
    {
        $this->isLogged();
        $data = $this->getServico();

        $this->debug($data); 
        try {
            $this->isLogged();
            $data = $this->getServico();
            $this->validateRequestMethod("POST");
            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}-perfil/?id={$this->postParams("CODPESSOA")}" . $returnParams;

            if (!$data["SERVICO"]["ALTERAR"] == "1") (new AlertLib)->warning('Você não tem permissão para realizar essa ação', "{$data["SERVICO"]["URL"]}/");


            if ($this->postParams('acao') == 'editar_funcionario') {

                $fotoFile = $_FILES["FILE_FOTO"] ?? [];
                $fotoAnterior = $this->postParams("FOTO");
                $nameFoto = $fotoAnterior;
                // ENVIA FOTO
                if (!empty($fotoFile["tmp_name"])) {
                    if (!FileLib::isImage($fotoFile)) (new AlertLib)->danger("Arquivo com extensão não permitida!", $returnAction);

                    $nameFoto = (new FileLib())->uploadImage($fotoFile, "/public/assets/upload/img/pessoa/", $fotoAnterior);
                }

                $endereco = (new EnderecoModel($_POST));

                $pessoa = (new PessoaModel());
                $pessoa->setNOME((new FuncoesLib())->textoPrimeiraLetraMaiusculoCadaPalavra($this->postParams('NOME')));
                $pessoa->setTELEFONE($this->postParams('TELEFONE'));
                $pessoa->setEMAIL($this->postParams('EMAIL'));
                $pessoa->setCODPESSOA($this->postParams('CODPESSOA'));
                $pessoa->setIMAGEM($nameFoto);


                $pessoaFisica = (new PessoaFisicaModel());
                $pessoaFisica->setCPF((new FuncoesLib)->formatCpfBanco($this->postParams('CPF')));
                $pessoaFisica->setDATANASCIMENTO($this->postParams('DATANASCIMENTO'));
                $pessoaFisica->setSEXO($this->postParams('SEXO'));
                $pessoaFisica->setCODPESSOA($this->postParams('CODPESSOA'));


                $funcionario = new GpFuncionarioModel();
                $funcionario->setCODFUNCIONARIO($this->postParams('CODFUNCIONARIO'));
                $funcionario->setNOMESOCIAL($this->postParams('NOMESOCIAL'));
                $funcionario->setCODCATEGORIA($this->postParams('CODCATEGORIA'));
                $funcionario->setSITUACAO(isset($_POST['SITUACAO']) ? '1' : '0');

                try {
                    $result = (new PessoaFisicaDao)->editarFuncionario($endereco, $pessoa, $pessoaFisica, $funcionario);

                    $returnAction = "{$data["SERVICO"]["URL"]}-perfil/?id={$pessoa->getCODPESSOA()}" . $returnParams;

                    if (empty($result)) (new AlertLib)->warning("Opss! Aconteceu um erro", $returnAction);

                    if ($result["error"]) (new AlertLib)->warning($result["message"], $returnAction);

                    (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Atualização de dados principais", $this->postParams("JUSTIFICATIVA"));
                    (new AlertLib)->success($result["message"], $returnAction);
                } catch (Error $e) {
                    return $e;
                }
            }

            //FUNCIONARIO-BANCO
            if (str_contains($this->postParams('acao'), 'banco')) {

                $bancoObj = new GpFuncionarioBancoModel($_POST);
                unset($bancoObj->acao);
                $bancoObj->SITUACAO = ($this->postParams('SITUACAO') == 'on' ? '1' : '0');
                $insertObj = $bancoObj->getDataModelArray();
                unset($insertObj['dataModelArray']);
                unset($insertObj['EXCLUIDO']);
                unset($insertObj['CODPESSOA']);
                unset($insertObj['CODFUNCIONARIO_BANCO']);
                unset($insertObj['acao']);
                unset($insertObj['ANDAMENTO']);
                unset($insertObj['JUSTIFICATIVA']);



                $insertObj['SITUACAO'] = ($this->postParams('SITUACAO') == 'on' ? '1' : '0');

                $insertObj['CODPESSOA_CADASTRO'] = SessionLib::getValue('CODPESSOA');

                if ($this->postParams('acao') == 'cadastrar_banco') {

                    if ((new GpFuncionarioBancoDao)->insertArray($insertObj)) {
                        (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Cadastro de conta bancária", $this->postParams("JUSTIFICATIVA"));
                        (new AlertLib)->success('Cadastrado com sucesso', '/gestaopessoas/funcionarios-banco/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                    }
                    throw new Error('Erro ao cadastrar');
                }
                if ($this->postParams('acao') == 'editar_banco') {

                    if ((new GpFuncionarioBancoDao)->updateArray($insertObj, 'CODFUNCIONARIO_BANCO = ' . $this->postParams('CODFUNCIONARIO_BANCO'))) {
                        (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Atualização de conta bancária", $this->postParams("JUSTIFICATIVA"));
                        (new AlertLib)->success('Alterado com sucesso', '/gestaopessoas/funcionarios-banco/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                    }
                    throw new Error('Erro ao Alterar');
                }

                if ($this->postParams('acao') == 'excluir_banco') {
                    $insertObj['EXCLUIDO'] = '1';
                    if ((new GpFuncionarioBancoDao)->updateArray($insertObj, 'CODFUNCIONARIO_BANCO = ' . $this->postParams('CODFUNCIONARIO_BANCO'))) {
                        (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Exclusão de conta bancária", $this->postParams("JUSTIFICATIVA"));
                        (new AlertLib)->success('Alterado com sucesso', '/gestaopessoas/funcionarios-banco/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                    }
                    throw new Error('Erro ao Alterar');
                }
            }

            if (str_contains($this->postParams('acao'), 'documentos')) {
                $obj = new GpDocumentoModel($_POST);
                unset($obj->acao);
                $insertObj = $obj->getDataModelArray();
                unset($insertObj['acao']);
                unset($insertObj['TE_ESTADO']);
                unset($insertObj['dataModelArray']);
                unset($insertObj['CODPESSOA']);
                unset($insertObj['JUSTIFICATIVA']);


                $insertObj['CODPESSOA_CADASTRO'] = SessionLib::getValue('CODPESSOA');

                if (!$this->postParams('CODDOCUMENTO')) {
                    if ((new GpDocumentoDao)->insertArray($insertObj)) {
                        (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Cadastro de dados funcionáis", $this->postParams("JUSTIFICATIVA"));
                        (new AlertLib)->success('Inserido com sucesso', '/gestaopessoas/funcionarios-funcionais/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                    }
                    throw new Error('Erro ao Inserir Documentos');
                } else {
                    if ((new GpDocumentoDao)->updateArray($insertObj, 'CODDOCUMENTO = ' . $obj->getCODDOCUMENTO())) {
                        (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Atualização de dados funcionais", $this->postParams("JUSTIFICATIVA"));
                        (new AlertLib)->success('Alterado com sucesso', '/gestaopessoas/funcionarios-funcionais/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                    }

                    throw new Error('Erro ao alterar Documentos');
                }
            }

            //funcionario departamento
            if (str_contains($this->postParams('acao'), 'departamento') == true) {

                $postObject = new GpFuncionarioDepartamentoModel($_POST);
                unset($postObject->acao);

                $insertObj = $postObject->getDataModelArray();

                unset($insertObj['dataModelArray']);
                unset($insertObj['EXCLUIDO']);
                unset($insertObj['acao']);

                unset($insertObj['CODPESSOA']);
                unset($insertObj['CODFUNCIONARIO_DEPARTAMENTO']);
                $insertObj['CODPESSOA_CADASTRO'] = SessionLib::getValue('CODPESSOA');
                unset($insertObj['JUSTIFICATIVA']);


                if (str_contains($this->postParams('acao'), 'departamento') == true) {

                    if ($this->postParams('acao') == 'cadastrar_departamento') {

                        if ((new GpFuncionarioDepartamentoDao)->insertArray($insertObj)) {
                            (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Cadastro de nova lotação", $this->postParams("JUSTIFICATIVA"));
                            (new AlertLib)->success('Cadastrado com sucesso', '/gestaopessoas/funcionarios-departamentos/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                        }
                        throw new Error('Erro ao cadastrar');
                    }
                    if ($this->postParams('acao') == 'editar_departamento') {

                        if ((new GpFuncionarioDepartamentoDao)->updateArray($insertObj, 'CODFUNCIONARIO_DEPARTAMENTO = ' . $this->postParams('CODFUNCIONARIO_DEPARTAMENTO'))) {
                            (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Atualização de lotação", $this->postParams("JUSTIFICATIVA"));
                            (new AlertLib)->success('Alterado com sucesso', '/gestaopessoas/funcionarios-departamentos/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                        }
                        throw new Error('Erro ao Alterar');
                    }

                    if ($this->postParams('acao') == 'excluir_departamento') {
                        $insertObj['EXCLUIDO'] = '1';
                        if ((new GpFuncionarioDepartamentoDao)->updateArray($insertObj, 'CODFUNCIONARIO_DEPARTAMENTO = ' . $this->postParams('CODFUNCIONARIO_DEPARTAMENTO'))) {
                            (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Exclusão de lotação", $this->postParams("JUSTIFICATIVA"));
                            (new AlertLib)->success('Alterado com sucesso', '/gestaopessoas/funcionarios-departamentos/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                        }
                        throw new Error('Erro ao Alterar');
                    }
                }
            }

            //FUNCIONARIO-CARGOS
            if (str_contains($this->postParams('acao'), 'cargo') == true) {
                $cargoObj = new GpFuncionarioCargoModel($_POST);

                $cargoObj->setPCD($cargoObj->getPCD() == null ? '0' : '1');
                $cargoObj->setCODPESSOA_CADASTRO(SessionLib::getValue('CODPESSOA'));
                $cargoObj->setADICIONALNOTURNO($cargoObj->getADICIONALNOTURNO() == null ? '0' : '1');
                $insertObj = (object) $cargoObj->getDataModelArray();
                unset($insertObj->dataModelArray);
                unset($insertObj->acao);
                unset($insertObj->CODPESSOA);
                $insertObj->ADICIONALNOTURNO = ($this->postParams('ADICIONALNOTURNO') == 'on' ? '1' : '0');
                $insertObj->PCD = ($this->postParams('PCD') == 'on' ? '1' : '0');
                $insertObj->CODPESSOA_CADASTRO = SessionLib::getValue('CODPESSOA');
                unset($insertObj->JUSTIFICATIVA);

                if ($this->postParams('acao') == 'cadastrar_cargo') {
                    unset($insertObj->CODFUNCIONARIO_CARGO);
                    if ((new DaosGpFuncionarioCargoDao)->insertObject($insertObj)) {
                        (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Cadastro de novo cargo", $this->postParams("JUSTIFICATIVA"));
                        (new AlertLib)->success('Cadastrado com sucesso', '/gestaopessoas/funcionarios-cargos/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                    }
                    (new AlertLib)->danger('Erro ao cadastrar', '/gestaopessoas/funcionarios-cargos/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                }
                if ($this->postParams('acao') == 'editar_cargo') {
                    if ((new DaosGpFuncionarioCargoDao)->updateObject($insertObj, 'CODFUNCIONARIO_CARGO = ' . $this->postParams('CODFUNCIONARIO_CARGO'))) {
                        (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Atualização de cargo", $this->postParams("JUSTIFICATIVA"));
                        (new AlertLib)->success('Alterado com sucesso', '/gestaopessoas/funcionarios-cargos/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                    }
                    (new AlertLib)->danger('Erro ao Altear', '/gestaopessoas/funcionarios-cargos/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                }

                if ($this->postParams('acao') == 'excluir_cargo') {
                    $insertObj->EXCLUIDO = 1;
                    if ((new DaosGpFuncionarioCargoDao)->updateObject($insertObj, 'CODFUNCIONARIO_CARGO = ' . $this->postParams('CODFUNCIONARIO_CARGO'))) {
                        (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Exclusão de cargo", $this->postParams("JUSTIFICATIVA"));
                        (new AlertLib)->success('Alterado com sucesso', '/gestaopessoas/funcionarios-cargos/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                    }
                    (new AlertLib)->danger('Erro ao alterar', '/gestaopessoas/funcionarios-cargos/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                }
            }

            //FUNCIONARIO-ARQUIVOS
            if (str_contains($this->postParams('acao'), 'arquivo') == true) {
                $object = new GpArquivoModel($_POST);
                $diretorio = "/public/assets/upload/files/funcionario/";

                $insertObj = (object) $object->getDataModelArray();
                $insertObj->CODPESSOA_CADASTRO = SessionLib::getValue('CODPESSOA');

                unset($insertObj->acao);
                unset($insertObj->CODPESSOA);
                unset($insertObj->dataModelArray);
                unset($insertObj->JUSTIFICATIVA);

                if ($this->postParams('acao') == 'cadastrar_arquivo') {
                    $arquivo = $this->filesParams('ARQUIVO');

                    if (FileLib::isEmpty($arquivo) || !FileLib::isPdf($arquivo)) (new AlertLib)->danger("Arquivo com extensão não permitida!", "{$data["SERVICO"]["URL"]}-arquivos/?id=" . $this->postParams("CODPESSOA") . $returnParams);

                    $upload = (new FileLib)->uploadFile($arquivo, $diretorio);
                    $insertObj->ARQUIVO = $upload;

                    $arquivoDao = new GpArquivoDao();

                    if ($arquivoDao->insertObject($insertObj)) {
                        $codarquivo = $arquivoDao->lastInsertId();
                        $nomeTipoArquivo = ($arquivoDao->buscarNomeArquivo($codarquivo))->NOMETIPOARQUIVO;
                        $codandamento = (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Cadastro de novo arquivo: {$nomeTipoArquivo}", $this->postParams("JUSTIFICATIVA"));
                        (new GpAndamentoDao())->insertAndamentoArquivo($codandamento, $codarquivo);
                        (new AlertLib)->success('Cadastrado com sucesso', "{$data["SERVICO"]["URL"]}-arquivos/?id=" . $this->postParams("CODPESSOA") .  $returnParams);
                    }

                    (new AlertLib)->danger("Erro ao cadastrar!", "{$data["SERVICO"]["URL"]}-arquivos/?id=" . $this->postParams("CODPESSOA") . $returnParams);
                }

                if ($this->postParams('acao') == 'excluir_arquivo') {
                    $insertObj->EXCLUIDO = 1;
                    $arquivoDao = new GpArquivoDao();
                    $objArquivo = $arquivoDao->buscarArquivoId($this->postParams('CODARQUIVO'))[0];
                    if ($arquivoDao->updateObject($insertObj, 'CODARQUIVO = ' . $this->postParams('CODARQUIVO'))) {
                        (new FileLib())->removeFile($diretorio . $objArquivo->ARQUIVO);
                        $nomeTipoArquivo = ($arquivoDao->buscarNomeArquivo($this->postParams('CODARQUIVO')))->NOMETIPOARQUIVO;
                        (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Exclusão de arquivo: {$nomeTipoArquivo}", $this->postParams("JUSTIFICATIVA"));
                        (new AlertLib)->success('Alterado com sucesso', "{$data["SERVICO"]["URL"]}-arquivos/?id=" . $this->postParams("CODPESSOA") . '&pg=1');
                    }
                    (new AlertLib)->danger("Erro ao alterar!", "{$data["SERVICO"]["URL"]}-arquivos/?id=" . $this->postParams("CODPESSOA") . $returnParams);
                }
            }

            //FUNCIONARIO-DEPENDENTES
            if (str_contains($this->postParams('acao'), 'dependente') == true) {


                $dependenteObj = new GpfuncionariodependenteModel($_POST);

                $insertObj = (object) $dependenteObj->getDataModelArray();
                unset($insertObj->dataModelArray);
                unset($insertObj->CODPESSOA);
                unset($insertObj->acao);
                $insertObj->IR = $dependenteObj->getIR() == null ? '0' : '1';
                $insertObj->SF = $dependenteObj->getSF() == null ? '0' : '1';
                $insertObj->PENSAO = $dependenteObj->getPENSAO() == null ? '0' : '1';
                $insertObj->CPF = (new FuncoesLib)->formatCpfBanco($dependenteObj->getCPF());


                if ($this->postParams('acao') == 'cadastrar_dependente') {

                    if ((new GpFuncionarioDependenteDao)->insertObject($insertObj)) {
                        (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Cadastro de dependente: {$insertObj->NOME}", $this->postParams("JUSTIFICATIVA"));
                        (new AlertLib)->success('Cadastrado com sucesso', '/gestaopessoas/funcionarios-dependentes/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                    }
                    throw new Error('Erro ao cadastrar');
                }
                if ($this->postParams('acao') == 'editar_dependente') {
                    if ((new GpFuncionarioDependenteDao)->updateObject($insertObj, 'CODPESSOA_DEPENDENTE = ' . $this->postParams('CODPESSOA_DEPENDENTE'))) {
                        (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Atualização de dependente: {$insertObj->NOME}", $this->postParams("JUSTIFICATIVA"));;
                        (new AlertLib)->success('Alterado com sucesso', '/gestaopessoas/funcionarios-dependentes/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                    }
                    throw new Error('Erro ao Alterar');
                }

                if ($this->postParams('acao') == 'excluir_dependente') {
                    $insertObj->EXCLUIDO = '1';
                    if ((new GpFuncionarioDependenteDao)->updateObject($insertObj, 'CODPESSOA_DEPENDENTE = ' . $this->postParams('CODPESSOA_DEPENDENTE'))) {
                        (new GpAndamentoDao())->insertAndamento($this->postParams('CODFUNCIONARIO'), "Exclusão de dependente: {$insertObj->NOME}", $this->postParams("JUSTIFICATIVA"));
                        (new AlertLib)->success('Alterado com sucesso', '/gestaopessoas/funcionarios-dependentes/?id=' . $this->postParams("CODPESSOA") . '&pg=1');
                    }
                    throw new Error('Erro ao Alterar');
                }
            }
        } catch (Error $e) {
            return $e;
        }
    }

    public function funcionariosfuncionais($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            $data["TITLE_CARD_COMPONENT"] = "Documentos";

            if ($this->getParams('id')) {
                $pessoaObj = (new PessoaDao)->buscarPessoa($this->getParams('id'));
                $data['PESSOA'] = $pessoaObj[0];
                $data['PESSOAFISICA'] = (new PessoaFisicaDao)->buscarPessoa($this->getParams('id'));
                $data['FUNCIONARIO'] = (new GpFuncionarioDao)->buscarPessoa($this->getParams('id'));
            }
            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["TEMPLATE"], $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function funcionariosbanco($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $data["TITLE_CARD_COMPONENT"] = "Dados Bancários";
            $data['tituloPagina'] = 'Dados Bancários';


            if ($args['id'] ?? $this->getParams('id')) {
                $pessoaObj = (new PessoaDao)->buscarPessoa($this->getParams('id'));
                $data['PESSOA'] = $pessoaObj[0];
                $data['PESSOAFISICA'] = (new PessoaFisicaDao)->buscarPessoa($this->getParams('id'));
                $data['FUNCIONARIO'] = (new GpFuncionarioDao)->buscarPessoa($this->getParams('id'));
                $data['FUNCIONARIO_CATEGORIA'] = (new GpCategoriaFuncionarioDao)->buscar(['CODCATEGORIA' => $data['FUNCIONARIO']->CODCATEGORIA])[0];
            } else {
                throw new Error('Nao selecionou funcionário');
            }
            $bancosFuncionario = (new GpFuncionarioBancoDao)->buscarFuncionario($data['FUNCIONARIO']->CODFUNCIONARIO);

            $tableComponents = new TableLib();

            $tableComponents->init(
                $bancosFuncionario,
                array(' ', 'Banco', 'Tipo', 'Agencia', 'Conta', ' '),
            );
            $data['BANCOS'] = $bancosFuncionario;


            foreach ($bancosFuncionario as $key => $contaBanco) {

                if ($tableComponents->checkPagination($key)) {
                    $iconeSituacao = $contaBanco->SITUACAO == "1" ? "text-success" : "text-warning";
                    $objeto = json_encode($contaBanco);

                    $tableComponents->addCol("<span class='mdi mdi-circle {$iconeSituacao}'></span> ");
                    $tableComponents->addCol($contaBanco->NOMECURTO);
                    $tableComponents->addCol($contaBanco->TIPOCONTA);
                    $tableComponents->addCol($contaBanco->AGENCIA);
                    $tableComponents->addCol($contaBanco->CONTA);
                    $tableComponents->addCol(("<a href='javascript:void(0)' onclick='fillModal({$objeto})' class='btn btn-outline-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#modalFuncionarios'><span class='mdi mdi-eye-outline'></span>Ver</a>"));
                    $tableComponents->addRow();
                }
            }
            $data["TABLE_COMPONENT"] = $tableComponents->render();

            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["TEMPLATE"], $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function funcionariosarquivos($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            if ($this->getParams('id')) {
                $pessoaObj = (new PessoaDao)->buscarPessoa($this->getParams('id'));
                $data['PESSOA'] = $pessoaObj[0];
                $data['PESSOAFISICA'] = (new PessoaFisicaDao)->buscarPessoa($this->getParams('id'));
                $data['FUNCIONARIO'] = (new GpFuncionarioDao)->buscarPessoa($this->getParams('id'));
                $data['FUNCIONARIO_CATEGORIA'] = (new GpCategoriaFuncionarioDao)->buscar(['CODCATEGORIA' => $data['FUNCIONARIO']->CODCATEGORIA])[0];
            }
            $tableComponents = new TableLib();
            $arquivosFuncs = (new GpArquivoDao)->buscarCodfuncionario($data['FUNCIONARIO']->CODFUNCIONARIO);
            $tableComponents->init(
                $arquivosFuncs,
                array('Tipo', 'Data', ' '),
            );


            foreach ($arquivosFuncs as $key => $arq) {

                if ($tableComponents->checkPagination($key)) {
                    $objeto = json_encode($arq);
                    $link = DownloadLib::createLink($arq->ARQUIVO, "funcionario");


                    $tableComponents->addCol($arq->NOME);
                    $tableComponents->addCol(date('d/m/y', strtotime($arq->ALTERADO_EM)));

                    $tableComponents->addCol(("<a href='{$link}' target='_blank' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-file-outline'></span> Ver</a>") . " " .
                        ($data['SERVICO']['EXCLUIR'] == 1 ? "<a href='javascript:void(0)' onclick='excluirItemTabela(`{$arq->CODARQUIVO}`,`codarquivo`,`formFuncionarios`,`excluir_arquivo`)' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-trash-can-outline'></span> Excluir</a>" : ""));

                    $tableComponents->addRow();
                }
            }
            $data["TABLE_COMPONENT"] = $tableComponents->render();



            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["TEMPLATE"], $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function funcionarioscargos($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            $data['tituloPagina'] = 'Cargos';
            $data["TITLE_CARD_COMPONENT"] = "Cargos";

            if ($args['id'] ?? $this->getParams('id')) {
                $pessoaObj = (new PessoaDao)->buscarPessoa($this->getParams('id'));
                $data['PESSOA'] = $pessoaObj[0];
                $data['PESSOAFISICA'] = (new PessoaFisicaDao)->buscarPessoa($this->getParams('id'));
                $data['FUNCIONARIO'] = (new GpFuncionarioDao)->buscarPessoa($this->getParams('id'));
                $data['FUNCIONARIO_CATEGORIA'] = (new GpCategoriaFuncionarioDao)->buscar(['CODCATEGORIA' => $data['FUNCIONARIO']->CODCATEGORIA])[0];
            } else {
                throw new Error('Nao selecionou funcionário');
            }
            $cargosFuncionario = (new DaosGpFuncionarioCargoDao)->getByCodfuncionario($data['FUNCIONARIO']->CODFUNCIONARIO);

            $tableComponents = new TableLib();

            $tableComponents->init(
                $cargosFuncionario,
                array(' ', 'Cargo', 'Data Admissão', 'Salário', 'c/h', ' '),
            );


            foreach ($cargosFuncionario as $key => $cargoFunc) {
                if ($tableComponents->checkPagination($key)) {
                    $iconeSituacao = $cargoFunc->SITUACAO == "1" ? "text-success" : "text-warning";
                    $cargoFunc->DATAADMISSAO = date('Y-m-d', strtotime($cargoFunc->DATAADMISSAO));
                    $cargoFunc->DATADEMISSAO = $cargoFunc->DATADEMISSAO ? date('Y-m-d', strtotime($cargoFunc->DATADEMISSAO)) : null;
                    $objeto = json_encode($cargoFunc);

                    $tableComponents->addCol("<span class='mdi mdi-circle {$iconeSituacao}'></span> ");
                    $tableComponents->addCol($cargoFunc->NOME);
                    $tableComponents->addCol(date('d/m/Y', strtotime($cargoFunc->DATAADMISSAO)));
                    $tableComponents->addCol('R$ ' . $cargoFunc->SALARIO);
                    $tableComponents->addCol($cargoFunc->CARGAHORARIA . 'h');
                    $tableComponents->addCol(("<a href='javascript:void(0)' onclick='fillModal({$objeto})' class='btn btn-outline-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#modalFuncionarios'><span class='mdi mdi-eye-outline'></span>Ver</a>"));
                    $tableComponents->addRow();
                }
            }
            $data["TABLE_COMPONENT"] = $tableComponents->render();


            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["TEMPLATE"], $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function funcionariosdepartamentos($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            $data['tituloPagina'] = 'Departamentos';
            $data["TITLE_CARD_COMPONENT"] = "Departamentos";

            if ($this->getParams('id')) {
                $pessoaObj = (new PessoaDao)->buscarPessoa($this->getParams('id'));
                $data['PESSOA'] = $pessoaObj[0];
                $data['PESSOAFISICA'] = (new PessoaFisicaDao)->buscarPessoa($this->getParams('id'));
                $data['FUNCIONARIO'] = (new GpFuncionarioDao)->buscarPessoa($this->getParams('id'));
                $data['FUNCIONARIO_CATEGORIA'] = (new GpCategoriaFuncionarioDao)->buscar(['CODCATEGORIA' => $data['FUNCIONARIO']->CODCATEGORIA])[0];
            }
            $tableObj = (new GpFuncionarioDepartamentoDao)->buscarFuncionario($data['FUNCIONARIO']->CODFUNCIONARIO);
            $tableComponents = new TableLib();
            $tableComponents->init(
                $tableObj,
                array('Departamento', 'Início', 'Término', ' '),
            );
            foreach ($tableObj as $key => $objet) {
                if ($tableComponents->checkPagination($key)) {
                    $objet->DATAINICIO = date('Y-m-d', strtotime($objet->DATAINICIO));
                    $objet->DATAFIM = $objet->DATAFIM ? date('Y-m-d', strtotime($objet->DATAFIM)) : null;
                    $objeto = json_encode($objet);

                    $tableComponents->addCol($objet->NOME);
                    $tableComponents->addCol((new FuncoesLib)->formatDataUsuarioAmigavel($objet->DATAINICIO));
                    $tableComponents->addCol($objet->DATAFIM ? (new FuncoesLib)->formatDataUsuarioAmigavel($objet->DATAFIM) : '-');
                    $tableComponents->addCol(("<a href='javascript:void(0)' onclick='fillModal({$objeto})' class='btn btn-outline-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#modalFuncionarios'><span class='mdi mdi-eye-outline'></span>Ver</a>"));
                    $tableComponents->addRow();
                }
            }
            $data["TABLE_COMPONENT"] = $tableComponents->render();
            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["TEMPLATE"], $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function funcionariosdependentes($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            $data['tituloPagina'] = 'Dependentes';



            if ($args['id'] ?? $this->getParams('id')) {
                $pessoaObj = (new PessoaDao)->buscarPessoa($this->getParams('id'));
                $data['PESSOA'] = $pessoaObj[0];
                $data['PESSOAFISICA'] = (new PessoaFisicaDao)->buscarPessoa($this->getParams('id'));
                $data['FUNCIONARIO'] = (new GpFuncionarioDao)->buscarPessoa($this->getParams('id'));
            } else {
                throw new Error('Nao selecionou funcionário');
            }
            $dependentesObj = (new GpFuncionarioDependenteDao)->buscarFuncionario($data['FUNCIONARIO']->CODFUNCIONARIO);

            $tableComponents = new TableLib();

            $tableComponents->init(
                $dependentesObj,
                array('Nome', 'Vínculo', ' '),
            );

            foreach ($dependentesObj as $key => $dependente) {
                if ($tableComponents->checkPagination($key)) {
                    $dependente->DATANASCIMENTO = date('Y-m-d', strtotime($dependente->DATANASCIMENTO));
                    $objeto = json_encode($dependente);

                    $tableComponents->addCol($dependente->NOME);
                    $tableComponents->addCol($dependente->VINCULO);
                    $tableComponents->addCol(("<a href='javascript:void(0)' onclick='fillModal({$objeto})' class='btn btn-outline-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#modalFuncionarios'><span class='mdi mdi-eye-outline'></span>Ver</a>"));
                    $tableComponents->addRow();
                }
            }
            $data["TABLE_COMPONENT"] = $tableComponents->render();

            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["TEMPLATE"], $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function funcionarioshistorico($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $data['tituloPagina'] = 'Histórico Funcional';


            if (!$this->getParams('id')) (new AlertLib())->warning("Parâmetro inválido", "/");

            $codpessoa = $this->getParams('id');
            $pessoaObj = (new PessoaDao)->buscarPessoa($codpessoa);
            $data['PESSOA'] = $pessoaObj[0];
            $data['FUNCIONARIO'] = (new GpFuncionarioDao)->buscarPessoa($codpessoa);
            $data['ANDAMENTOS'] = (new GpAndamentoDao())->andamentoByCodFuncionario($data['FUNCIONARIO']->CODFUNCIONARIO);



            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["TEMPLATE"], $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function gerenciamento($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $tableComponents = new TableLib();

            //ROTEAMENTO DAS OPCOES DA TABELA COM BASE NAS FUNCOES DO CONTROLLER
            $data["SERVICOS"] = [
                ["ICONE" => "mdi-account-details-outline", "TITULO" => "Arquivos", "DESCRICAO" => "Cadastro de tipos de arquivo", "CONTROLLER" => "tipoarquivo"],
                ["ICONE" => "mdi-account-details-outline", "TITULO" => "Cargos", "DESCRICAO" => "Cadastro de Cargos", "CONTROLLER" => "cargos"],
                ["ICONE" => "mdi-account-details-outline", "TITULO" => "Categorias", "DESCRICAO" => "Cadastro de Categorias", "CONTROLLER" => "categorias"],
                ["ICONE" => "mdi-account-details-outline", "TITULO" => "Dependentes", "DESCRICAO" => "Cadastro de Dependentes", "CONTROLLER" => "dependentes"],
                ["ICONE" => "mdi-account-details-outline", "TITULO" => "Departamentos", "DESCRICAO" => "Cadastro de Departamentos", "CONTROLLER" => "departamentos"]

            ];

            return $this->render(
                TemplateAbstract::LOGGED,
                "components/page_servicos",
                $data,
            );
        } catch (\Error $e) {
            return $e;
        }
    }

    public function gerenciamentodependentes($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            $tableComponents = new TableLib();
            $data["TITLE_CARD_COMPONENT"] = "Tipos de Dependentes";
            $lista = (new GpTipoDependenteDao)->buscarTodos();
            $tableComponents->init($lista, array('Nome', 'Situação', ' '), array("buscar" => $this->getParams('buscar'), 'id' => $this->getParams('buscar')));
            foreach ($lista as $key => $value) {
                if ($tableComponents->checkPagination($key)) {
                    $objeto = json_encode($value);

                    $tableComponents->addCol($value->NOME);
                    $tableComponents->addCol($value->EXCLUIDO ? 'Inativo' : 'Ativo');
                    $tableComponents->addCol(("<a href='javascript:void(0)' onclick='fillModal({$objeto})' class='btn btn-outline-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#modalDependentes'><span class='mdi mdi-eye-outline'></span>Ver</a>"));
                    $tableComponents->addRow();
                }
            }

            $data["TABLE_COMPONENT"] = $tableComponents->render();
            $data["tableButtonInputPlaceholder"] = "Buscar...";
            $data["topTable"] = "components/button_table/button_table.html.twig";

            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["MODULO"] . '/' . __FUNCTION__, $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function gerenciamentodependentesaction($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}-dependentes/" . $returnParams;

            if ($this->postParams('acao') == 'editar' and $data["SERVICO"]["ALTERAR"] == "1") {
                try {
                    $tipoDependente = new stdClass;
                    $tipoDependente->NOME = $this->postParams('NOME');
                    $tipoDependente->EXCLUIDO = ($this->postParams('EXCLUIDO') == 'on' ? '0' : '1');
                    $tipoDependente->CODDEPENDENTE_TIPO = $this->postParams('CODDEPENDENTE_TIPO');

                    (new GpTipoDependenteDao)->updateObject($tipoDependente, 'CODDEPENDENTE_TIPO = ' . $tipoDependente->CODDEPENDENTE_TIPO);
                    return (new AlertLib)->success("Alterado com sucesso!", $returnAction);
                } catch (\Error $e) {
                    throw  $e;
                }
            }

            if ($this->postParams('acao') == 'cadastrar' and $data["SERVICO"]["SALVAR"] == "1") {
                try {
                    $tipoDependente = new stdClass;
                    $tipoDependente->NOME = $this->postParams('NOME');

                    (new GpTipoDependenteDao)->insertObject($tipoDependente);
                    (new AlertLib)->success("Cadastrado com sucesso!", $returnAction);
                } catch (\Error $e) {
                    throw  $e;
                }
            }
            (new AlertLib)->warning("Você não tem privilégio para executar essa ação!", $returnAction);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function gerenciamentotipoarquivo($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            $tableComponents = new TableLib();
            $data["TITLE_CARD_COMPONENT"] = "Tipos de Arquivos";
            $lista = (new GpTipoArquivoDao)->buscarTodos();
            $tableComponents->init($lista, array('Nome', 'Sigla', 'Situação', ' '), array("buscar" => $this->getParams('buscar'), 'id' => $this->getParams('buscar')));
            foreach ($lista as $key => $value) {
                if ($tableComponents->checkPagination($key)) {
                    $objeto = json_encode($value);

                    $tableComponents->addCol($value->NOME);
                    $tableComponents->addCol($value->SIGLA);
                    $tableComponents->addCol($value->EXCLUIDO ? 'Inativo' : 'Ativo');
                    $tableComponents->addCol(("<a href='javascript:void(0)' onclick='fillModal({$objeto})' class='btn btn-outline-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#modalTipoArquivos'><span class='mdi mdi-eye-outline'></span>Ver</a>"));
                    $tableComponents->addRow();
                }
            }

            $data["TABLE_COMPONENT"] = $tableComponents->render();
            $data["tableButtonInputPlaceholder"] = "Buscar...";
            $data["topTable"] = "components/button_table/button_table.html.twig";

            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["MODULO"] . '/' . __FUNCTION__, $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function gerenciamentotipoarquivoaction($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}-tipoarquivo/" . $returnParams;

            $tipoArquivo = new stdClass;
            $tipoArquivo->NOME = $this->postParams('NOME');
            $tipoArquivo->EXCLUIDO = ($this->postParams('EXCLUIDO') == 'on' ? '0' : '1');
            $tipoArquivo->SIGLA = $this->postParams('SIGLA');


            if ($this->postParams('acao') == 'editar' and $data["SERVICO"]["ALTERAR"] == "1") {
                try {
                    $tipoArquivo->CODTIPOARQUIVO = $this->postParams('CODTIPOARQUIVO');

                    (new GpTipoArquivoDao)->updateObject($tipoArquivo, 'CODTIPOARQUIVO = ' . $tipoArquivo->CODTIPOARQUIVO);
                    (new AlertLib)->success("Alterado com sucesso!", $returnAction);
                } catch (\Error $e) {
                    throw  $e;
                }
            }

            if ($this->postParams('acao') == 'cadastrar' and $data["SERVICO"]["SALVAR"] == "1") {
                try {

                    (new GpTipoArquivoDao)->insertObject($tipoArquivo);
                    return (new AlertLib)->success("Cadastrado com sucesso!", $returnAction);
                } catch (\Error $e) {
                    throw  $e;
                }
            }
            (new AlertLib)->warning("Você não tem privilégio para executar essa ação!", $returnAction);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function gerenciamentocargos($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            $tableComponents = new TableLib();
            $data["TITLE_CARD_COMPONENT"] = "Cargos";
            $lista = (new GpCargoDao)->buscarTodos();
            $tableComponents->init($lista, array('Nome', 'Sigla', 'Situação', ' '), array("buscar" => $this->getParams('buscar'), 'id' => $this->getParams('buscar')));
            foreach ($lista as $key => $value) {

                if ($tableComponents->checkPagination($key)) {
                    $objeto = json_encode($value);

                    $tableComponents->addCol($value->NOME);
                    $tableComponents->addCol($value->SIGLA);
                    $tableComponents->addCol($value->EXCLUIDO ? 'Inativo' : 'Ativo');
                    $tableComponents->addCol(("<a href='javascript:void(0)' onclick='fillModal({$objeto})' class='btn btn-outline-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#modalCargos'><span class='mdi mdi-eye-outline'></span>Ver</a>"));
                    $tableComponents->addRow();
                }
            }
            $data["TABLE_COMPONENT"] = $tableComponents->render();

            $data["tableButtonInputPlaceholder"] = "Nome...";
            $data["topTable"] = "components/button_table/button_table.html.twig";

            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["MODULO"] . '/' . __FUNCTION__, $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function gerenciamentocargosaction($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}-cargos/" . $returnParams;

            $cargoObj = new stdClass;
            $cargoObj->NOME = $this->postParams('NOME');
            $cargoObj->EXCLUIDO = ($this->postParams('EXCLUIDO') == 'on' ? '0' : '1');
            $cargoObj->SIGLA = $this->postParams('SIGLA');


            if ($this->postParams('acao') == 'editar' and $data["SERVICO"]["ALTERAR"] == "1") {
                try {
                    $cargoObj->CODCARGO = $this->postParams('CODCARGO');

                    (new GpCargoDao)->updateObject($cargoObj, 'CODCARGO = ' . $cargoObj->CODCARGO);
                    (new AlertLib)->success("Alterado com sucesso!", $returnAction);
                } catch (\Error $e) {
                    throw  $e;
                }
            }

            if ($this->postParams('acao') == 'cadastrar' and $data["SERVICO"]["SALVAR"] == "1") {
                try {

                    (new GpCargoDao)->insertObject($cargoObj);
                    return (new AlertLib)->success("Cadastrado com sucesso!", $returnAction);
                } catch (\Error $e) {
                    throw  $e;
                }
            }
            (new AlertLib)->warning("Você não tem privilégio para executar essa ação!", $returnAction);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function gerenciamentocategorias($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            $data["TITLE_CARD_COMPONENT"] = "Categorias";
            $lista = (new GpCategoriaFuncionarioDao)->buscarTodos();
            $tableComponents = new TableLib();

            $tableComponents->init($lista, array('Nome', 'Situação', ' '), array("buscar" => $this->getParams('buscar'), 'id' => $this->getParams('buscar')));
            foreach ($lista as $key => $value) {
                if ($tableComponents->checkPagination($key)) {
                    $objeto = json_encode($value);

                    $tableComponents->addCol($value->NOME);
                    $tableComponents->addCol($value->EXCLUIDO ? 'Inativo' : 'Ativo');
                    $tableComponents->addCol(("<a href='javascript:void(0)' onclick='fillModal({$objeto})' class='btn btn-outline-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#modalCategorias'><span class='mdi mdi-eye-outline'></span>Ver</a>"));
                    $tableComponents->addRow();
                }
            }

            $data["TABLE_COMPONENT"] = $tableComponents->render();
            $data["tableButtonInputPlaceholder"] = "Buscar...";
            $data["topTable"] = "components/button_table/button_table.html.twig";

            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["MODULO"] . '/' . __FUNCTION__, $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function gerenciamentocategoriasaction($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}-categorias/" . $returnParams;

            $categoriaObj = new stdClass;
            $categoriaObj->NOME = $this->postParams('NOME');
            $categoriaObj->EXCLUIDO = ($this->postParams('EXCLUIDO') == 'on' ? '0' : '1');


            if ($this->postParams('acao') == 'editar' and $data["SERVICO"]["ALTERAR"] == "1") {
                try {
                    $categoriaObj->CODCATEGORIA = $this->postParams('CODCATEGORIA');

                    (new GpCategoriaFuncionarioDao)->updateObject($categoriaObj, 'CODCATEGORIA = ' . $categoriaObj->CODCATEGORIA);
                    return (new AlertLib)->success("Alterado com sucesso!", $returnAction);
                } catch (\Error $e) {
                    throw  $e;
                }
            }

            if ($this->postParams('acao') == 'cadastrar' and $data["SERVICO"]["SALVAR"] == "1") {
                try {

                    (new GpCategoriaFuncionarioDao)->insertObject($categoriaObj);
                    return (new AlertLib)->success("Cadastrado com sucesso!", $returnAction);
                } catch (\Error $e) {
                    throw  $e;
                }
            }
            (new AlertLib)->warning("Você não tem privilégio para executar essa ação!", $returnAction);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function gerenciamentodepartamentos($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $tableComponents = new TableLib();
            $data["TITLE_CARD_COMPONENT"] = "Cadastro de Departamentos";
            $lista = (new GpDepartamentoDao)->buscarTodos();
            $tableComponents->init($lista, array('Nome', 'Sigla', 'Situação', ""), array("buscar" => $this->getParams('buscar')));
            foreach ($lista as $key => $value) {

                if ($tableComponents->checkPagination($key)) {
                    $tableComponents->addCol($value->NOME);
                    $tableComponents->addCol($value->SIGLA);
                    $tableComponents->addCol($value->EXCLUIDO ? 'Inativo' : 'Ativo');
                    $objeto = json_encode($value);
                    $tableComponents->addCol(("<a href='javascript:void(0)' onclick='fillModal({$objeto})' class='btn btn-outline-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#modalDepartamento'><span class='mdi mdi-eye-outline'></span>Ver</a>"));

                    $tableComponents->addRow();
                }
            }

            $data["TABLE_COMPONENT"] = $tableComponents->render();
            $data["topTable"] = "components/button_table/button_table.html.twig";

            return $this->render(TemplateAbstract::LOGGED, $data["SERVICO"]["MODULO"] . '/' . __FUNCTION__, $data);
        } catch (\Error $e) {
            return $e;
        }
    }

    public function gerenciamentodepartamentosaction($args = [])
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}-departamentos/" . $returnParams;

            $categoriaObj = new stdClass;
            $categoriaObj->NOME = $this->postParams('NOME');
            $categoriaObj->EXCLUIDO = ($this->postParams('EXCLUIDO') == 'on' ? '0' : '1');
            $categoriaObj->SIGLA = $this->postParams('SIGLA');
            $categoriaObj->CODDEPARTAMENTOPAI = $this->postParams('CODDEPARTAMENTODAO');
            if ($this->postParams('acao') == 'editar' and $data["SERVICO"]["ALTERAR"] == "1") {
                try {
                    $categoriaObj->CODDEPARTAMENTO = $this->postParams('CODDEPARTAMENTO');

                    (new GpDepartamentoDao)->updateObject($categoriaObj, 'CODDEPARTAMENTO = ' . $categoriaObj->CODDEPARTAMENTO);
                    (new AlertLib)->success("Alterado com sucesso!", $returnAction);
                } catch (\Error $e) {
                    throw  $e;
                }
            }

            if ($this->postParams('acao') == 'cadastrar' and $data["SERVICO"]["SALVAR"] == "1") {
                try {

                    (new GpDepartamentoDao)->insertObject($categoriaObj);
                    return (new AlertLib)->success("Cadastrado com sucesso!", $returnAction);
                } catch (\Error $e) {
                    throw  $e;
                }
            }
            (new AlertLib)->warning("Você não tem privilégio para executar essa ação!", $returnAction);
        } catch (\Error $e) {
            return $e;
        }
    }
}
