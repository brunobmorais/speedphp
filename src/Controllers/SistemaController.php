<?php

namespace App\Controllers;


use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Core\PageCore;
use App\Daos\LogDao;
use App\Daos\ModuloDao;
use App\Daos\PerfilDao;
use App\Daos\PessoaDao;
use App\Daos\PessoaFisicaDao;
use App\Daos\PrivilegioDao;
use App\Daos\ServicoDao;
use App\Daos\SiPerfilUsuarioDao;
use App\Daos\UsuarioDao;
use App\Libs\AlertLib;
use App\Libs\CookieLib;
use App\Libs\EmailLib;
use App\Libs\FuncoesLib;
use App\Libs\JwtLib;
use App\Libs\SessionLib;
use App\Libs\TableLib;
use App\Libs\Template\TemplateAbstract;
use App\Libs\TemplateEmailLib;
use App\Models\EnderecoModel;
use App\Models\ModuloModel;
use App\Models\PerfilModel;
use App\Models\PessoaFisicaModel;
use App\Models\PessoaModel;
use App\Models\ServicoModel;
use App\Models\UsuarioModel;

class SistemaController extends ControllerCore implements ControllerInterface
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

    public function privacidade($args = null){

        try {
            // CARREGA VIEW
            return $this->render(
                TemplateAbstract::NOT_LOGGED,
                'sistema/privacidade',
                ['TITLE' => "Termo de uso e privacidade",
                    "configSiteName" => CONFIG_SITE['name'],
                    "configSiteNameFull" => CONFIG_SITE['nameFull'],
                    "configSiteEmail" => CONFIG_SITE['email'],
                    "configSitePhone" => CONFIG_SITE['phone'],
                    "configSiteUrl" => CONFIG_SITE['url'],
                    "configSiteDomain" => CONFIG_SITE['domain'],
                    "configSiteAndress" => CONFIG_SITE['andress'],
                    "configSiteCnpj" => CONFIG_SITE['cnpj'],
                ]
            );
        } catch (\Error $e) {
            return $e;
        }
    }

    // ######### MODULOS ###########
    public function modulos($getParametro = null)
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $moduloDao = new ModuloDao();
            $tableLib = new TableLib();

            $data["TITLE_CARD_COMPONENT"] = "Módulos";

            $obj = $moduloDao->buscaModulos($this->getParams('buscar'));

            $tableLib->init($obj,
                array("Icone", "Titulo", "Descrição", "Controller", ""),
                array("buscar" => $this->getParams('buscar'))
            );

            foreach ($obj as $key => $item) {
                if ($tableLib->checkPagination($key)) {
                    $iconeSituacao = $item->SITUACAO == "1"?"<span class='mdi mdi-circle text-success'></span> ":"<span class='mdi mdi-circle text-warning'></span> ";

                    $tableLib->addCol($iconeSituacao.$item->TITULO)
                        ->addCol("<span class='mdi mdi-18px {$item->ICONE}'></span>")
                        ->addCol($item->DESCRICAO)
                        ->addCol($item->CONTROLLER)
                        ->addCol($data["SERVICO"]["ALTERAR"] == "1" ? "<a href='{$data["SERVICO"]["URL"]}-cadastro/?id={$item->CODMODULO}&pg={$this->getParams('pg')}&buscar={$this->getParams('buscar')}' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-pencil-outline'></span> Editar</a>" : "")
                        ->addRow();
                }
            }
            $data["TABLE_COMPONENT"] = $tableLib->render();

            $data["tableButtonInputPlaceholder"] = "Buscar...";
            $data["topTable"] = "components/button_table/button_table.html.twig";

            return $this->render(
                TemplateAbstract::LOGGED,
                "components/page_table_default",
                $data,
            );
         } catch (\Error $e) {
            return $e;
        }
    }

    public function moduloscadastro($getParametro = null)
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $moduloDao = new ModuloDao();

            $data["TITLE_CARD_COMPONENT"] = "Cadastro de módulos";

            $id = $this->getParams("id") ?? "";
            $buscar = $this->getParams("buscar") ?? "";
            $pg = $this->getParams("pg") ?? "";

            if (!empty($id)) {
                $data["MODULO"] = $moduloDao->select("*", "WHERE CODMODULO=? AND EXCLUIDO='0'", array($id))[0] ?? "";
            }

            return $this->render(
                TemplateAbstract::LOGGED,
                $data["SERVICO"]["TEMPLATE"],
                $data,
            );
        } catch (\Error $e) {
            return $e;
        }

    }

    public function modulosaction($getParametro = null)
    {
        try {
            $this->isLogged();
            $this->validateRequestMethod("POST");
            $data = $this->getServico();

            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}/" . $returnParams;

            $alerta = new AlertLib();
            $sisModuloDao = new ModuloDao();
            $moduloModel = new ModuloModel();

            $action = $this->postParams("action") ?? "";
            $moduloModel->fromMapToModel($_POST);
            $situacao = empty($moduloModel->getSITUACAO()) ? "0" : "1";

            if ($action == "insert") {
                if ($data["SERVICO"]["SALVAR"] == "1") {
                    $parametros = array($moduloModel->getCONTROLLER(), $moduloModel->getICONE(), $moduloModel->getTITULO(), $moduloModel->getDESCRICAO(), $moduloModel->getORDEM(), $situacao);
                    $atributos = "CONTROLLER, ICONE, TITULO, DESCRICAO, ORDEM, SITUACAO";
                    $result = $sisModuloDao->insert($atributos, $parametros);
                    if ($result) {
                        (new LogDao())->salvaLog("MODULO: CADASTRAR", $data["SERVICO"]["CODSERVICO"], $sisModuloDao->getLogSQL());
                        $alerta->success("Inserido com sucesso!", $returnAction);
                    } else {
                        $alerta->danger("Ops! Aconteceu um erro, tente mais tarde.", $returnAction);
                    }
                } else {
                    $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
                }
            } elseif ($action == "update") {
                $sts = empty($this->postParams("sts")) ? "0" : "1";

                if ($data["SERVICO"]["ALTERAR"] == "1") {
                    $parametros = array($moduloModel->getCONTROLLER(), $moduloModel->getICONE(), $moduloModel->getTITULO(), $moduloModel->getDESCRICAO(), $moduloModel->getORDEM(), $situacao, $moduloModel->getCODMODULO());
                    $atributos = "CONTROLLER, ICONE, TITULO, DESCRICAO, ORDEM, SITUACAO";
                    $result = $sisModuloDao->update($atributos, $parametros, "CODMODULO=?");
                    if ($result) {
                        (new LogDao())->salvaLog("MODULO: ALTERAR", $data["SERVICO"]["CODSERVICO"], $sisModuloDao->getLogSQL());
                        $alerta->success("Alerado com sucesso!", $returnAction);
                    } else {
                        $alerta->danger("Ops! Aconteceu um erro, tente mais tarde.", $returnAction);
                    }
                } else {
                    $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
                }
            } elseif ($action == "delete") {
                if ($data["SERVICO"]["EXCLUIR"] == "1") {
                    $situacao = "1";
                    $codmodulo = $this->postParams("idExcluir");
                    $parametros = array($situacao, $codmodulo);
                    $atributos = "EXCLUIDO";
                    $result = $sisModuloDao->update($atributos, $parametros, "CODMODULO=?");
                    if ($result) {
                        (new LogDao())->salvaLog("MODULO: EXCLUIR", $data["SERVICO"]["CODSERVICO"], $sisModuloDao->getLogSQL());
                        $alerta->success("Excluido com sucesso!", $returnAction);
                    } else {
                        $alerta->danger("Ops! Aconteceu um erro, tente mais tarde.", $returnAction);
                    }
                } else {
                    $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
                }
            } else {
                $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
            }

         } catch (\Error $e) {
            return $e;
        }
    }


    // ######### SERVICOS ###########
    public function servicos($getParametro = null)
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $sisServico = new ServicoDao();
            $tableComponents = new TableLib();

            $data["TITLE_CARD_COMPONENT"] = "Serviços";

            $objServicos = $sisServico->buscaServicos($this->getParams("buscar"));

            $tableComponents->init($objServicos,
                array("Serviço", "Módulo", "Ícone",""),
                array("buscar" => $this->getParams('buscar')));

            foreach ($objServicos as $key => $item) {
                if ($tableComponents->checkPagination($key)) {
                    $iconeSituacao = $item->SITUACAO == "1"?"<span class='mdi mdi-circle text-success'></span> ":"<span class='mdi mdi-circle text-warning'></span> ";

                    $tableComponents->addCol($iconeSituacao.$item->TITULO)
                        ->addCol($item->TITULOMODULO)
                        ->addCol("<span class='mdi mdi-18px {$item->ICONE}'></span>")
                        ->addCol($data["SERVICO"]["ALTERAR"] == "1" ? "<a href='{$data["SERVICO"]["URL"]}-cadastro/?id={$item->CODSERVICO}&pg={$this->getParams('pg')}&buscar={$this->getParams('buscar')}' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-pencil-outline'></span> Editar</a>" : "")
                        ->addRow();
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
         } catch (\Error $e) {
            return $e;
        }
    }

    public function servicoscadastro($getParametro = null)
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $sisServicoDao = new ServicoDao();
            $sisModuloDao = new ModuloDao();

            $data["TITLE_CARD_COMPONENT"] = "Cadastrar serviços";

            $id = $this->getParams("id") ?? "";

            if (!empty($id)) {
                $data["DATASERVICO"] = $sisServicoDao->select("*", "WHERE CODSERVICO=? AND EXCLUIDO!='1'", array($id))[0] ?? "";
            }
            $data["DATAMODULOS"] = $sisModuloDao->select("*", "WHERE EXCLUIDO!='1'");

            return $this->render(
                TemplateAbstract::LOGGED,
                $data["SERVICO"]["TEMPLATE"],
                $data,
            );


         } catch (\Error $e) {
            return $e;
        }

    }

    public function servicosaction($getParametro = null)
    {
        try {
            $this->isLogged();
            $this->validateRequestMethod("POST");
            $data = $this->getServico();

            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}/" . $returnParams;

            $alerta = new AlertLib();
            $sisServicoDao = new ServicoDao();
            $servicoModel = new ServicoModel();

            $servicoModel->fromMapToModel($_POST);

            $action = $this->postParams("action") ?? "";

            if ($action == "insert") {
                if ($data["SERVICO"]["SALVAR"] == "1") {
                    $situcao = empty($servicoModel->getSITUACAO()) ? "0" : "1";

                    $parametros = array($servicoModel->getCODMODULO(), $servicoModel->getCONTROLLER(), $servicoModel->getICONE(), $servicoModel->getTITULO(), $servicoModel->getDESCRICAO(), $servicoModel->getORDEM(), $situcao);
                    $atributos = "CODMODULO, CONTROLLER, ICONE, TITULO, DESCRICAO, ORDEM, SITUACAO";
                    $result = $sisServicoDao->insert($atributos, $parametros);
                    if ($result) {
                        (new LogDao())->salvaLog("SERVICO: CADASTRAR", $data["SERVICO"]["CODSERVICO"], $sisServicoDao->getLogSQL());
                        $alerta->success("Inserido com sucesso!", $returnAction);
                    } else {
                        $alerta->danger("Ops! Aconteceu um erro, tente mais tarde.", $returnAction);
                    }
                } else {
                    $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
                }
            } elseif ($action == "update") {
                $situcao = empty($servicoModel->getSITUACAO()) ? "0" : "1";

                if ($data["SERVICO"]["ALTERAR"] == "1") {
                    $parametros = array($servicoModel->getCODMODULO(), $servicoModel->getCONTROLLER(), $servicoModel->getICONE(), $servicoModel->getTITULO(), $servicoModel->getDESCRICAO(), $servicoModel->getORDEM(), $situcao, $servicoModel->getCODSERVICO());
                    $atributos = "CODMODULO, CONTROLLER, ICONE, TITULO, DESCRICAO, ORDEM, SITUACAO";
                    $result = $sisServicoDao->update($atributos, $parametros, "CODSERVICO=?");
                    if ($result) {
                        (new LogDao())->salvaLog("SERVIÇO: ALTERAR", $data["SERVICO"]["CODSERVICO"], $sisServicoDao->getLogSQL());
                        $alerta->success("Alerado com sucesso!", $returnAction);
                    } else {
                        $alerta->danger("Ops! Aconteceu um erro, tente mais tarde.", $returnAction);
                    }
                } else {
                    $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
                }
            } elseif ($action == "delete") {
                if ($data["SERVICO"]["EXCLUIR"] == "1") {
                    $sts = "1";
                    $id = $this->postParams("idExcluir");
                    $parametros = array($sts, $id);
                    $atributos = "EXCLUIDO";
                    $result = $sisServicoDao->update($atributos, $parametros, "CODSERVICO=?");
                    if ($result) {
                        (new LogDao())->salvaLog("SERVIÇO: EXCLUIR", $data["SERVICO"]["CODSERVICO"], $sisServicoDao->getLogSQL());
                        $alerta->success("Excluido com sucesso!", $returnAction);
                    } else {
                        $alerta->danger("Ops! Aconteceu um erro, tente mais tarde.", $returnAction);
                    }
                } else {
                    $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
                }
            } else {
                $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
            }

         } catch (\Error $e) {
            return $e;
        }
    }

    // ######### PRIVILEGIOS ###########
    public function privilegios($getParametro = null)
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $perfilDao = new PerfilDao();

            $data["TITLE_CARD_COMPONENT"] = "Privilégios";

            $data["PERFIS"] = $perfilDao->buscarPerfis();
            return $this->render(
                TemplateAbstract::LOGGED,
                $data["SERVICO"]["URL"],
                $data,
            );
         } catch (\Error $e) {
            return $e;
        }
    }

    // ######### PERFIL ###########
    public function perfil($getParametro = null)
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $perfilDao = new PerfilDao();
            $tableComponents = new TableLib();

            $data["TITLE_CARD_COMPONENT"] = "Perfis";

            $objServicos = $perfilDao->buscarPerfis($this->getParams("buscar"));

            $tableComponents->init($objServicos,
                array("Perfil", "Nível", ""),
                array("buscar" => $this->getParams('buscar')));

            foreach ($objServicos as $key => $item) {
                if ($tableComponents->checkPagination($key)) {
                    $tableComponents->addCol($item->NOME)
                        ->addCol($item->NIVEL)
                        ->addCol(
                        ($data["SERVICO"]["ALTERAR"] == "1" ? "<a href='{$data["SERVICO"]["URL"]}-cadastro/?id={$item->CODPERFIL}&pg={$this->getParams('pg')}&buscar={$this->getParams('buscar')}' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-pencil-outline'></span> Editar</a>" : "").
                            " <a href='/{$data["SERVICO"]["MODULO"]}/perfil-privilegios/?id={$item->CODPERFIL}&pg={$this->getParams('pg')}&buscar={$this->getParams('buscar')}' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-pencil-outline'></span> Privilégios</a>")
                        ->addRow();
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
        } catch (\Error $e) {
            return $e;
        }
    }

    public function perfilprivilegios($getParametro = null)
    {
        try {
            $this->isLogged();

            $data = $this->getServico();

            $sisServicoDao = new ServicoDao();
            $sisModuloDao = new ModuloDao();
            $sisPrivilegio = new PrivilegioDao();
            $perfilDao = new PerfilDao();
            $alerta = new AlertLib();

            $data["TITLE_CARD_COMPONENT"] = "Cadastrar privilégios";

            $id = $this->getParams("id") ?? "";

            $dataPerfil = $perfilDao->buscarPerfilId($id);

            if (!empty($dataPerfil)) {
                $data["PERFIL"] = $dataPerfil;
                $data["DATAMODULOS"] = $sisModuloDao->select("*", "WHERE SITUACAO='1' AND EXCLUIDO=0 ORDER BY ORDEM, TITULO");
                foreach ($data["DATAMODULOS"] as $index => $item) {
                    $data["DATAMODULOS"][$index]->SERVICOS = $sisServicoDao->buscarServicosPrivilegiosModulo($id, $item->CODMODULO);
                }

                return $this->render(
                    TemplateAbstract::LOGGED,
                    $data["SERVICO"]["TEMPLATE"],
                    $data,
                );
            } else {
                $alerta->warning("Usuário não encontrado!", "{$data["SERVICO"]["URL"]}/");
            }


        } catch (\Error $e) {
            return $e;
        }

    }

    public function perfilcadastro($getParametro = null)
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $sisServicoDao = new ServicoDao();
            $perfilDao = new PerfilDao();

            $data["TITLE_CARD_COMPONENT"] = "Cadastrar";

            $id = $this->getParams("id") ?? "";

            $data["PERFIL"] = $perfilDao->buscarPerfilId($id);

            return $this->render(
                TemplateAbstract::LOGGED,
                $data["SERVICO"]["TEMPLATE"],
                $data,
            );


        } catch (\Error $e) {
            return $e;
        }

    }

    public function perfilaction($getParametro = null)
    {
        try {
            $this->isLogged();
            $this->validateRequestMethod("POST");
            $data = $this->getServico();
            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}/" . $returnParams;

            $alerta = new AlertLib();
            $perfilModel = new PerfilModel($_POST);
            $perfilDao = new PerfilDao();

            $action = $this->postParams("action") ?? "";

            if ($action == "insert") {
                if ($data["SERVICO"]["SALVAR"] == "1") {
                    $parametros = array($perfilModel->getNOME(), $perfilModel->getNIVEL(), 0);
                    $atributos = "NOME, NIVEL, EXCLUIDO";
                    $result = $perfilDao->insert($atributos, $parametros);
                    if ($result) {
                        (new LogDao())->salvaLog("PERFIL: CADASTRAR", $data["SERVICO"]["CODSERVICO"], $perfilDao->getLogSQL());
                        $alerta->success("Inserido com sucesso!", $returnAction);
                    } else {
                        $alerta->danger("Ops! Aconteceu um erro, tente mais tarde.", $returnAction);
                    }
                } else {
                    $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
                }
            } elseif ($action == "update") {

                if ($data["SERVICO"]["ALTERAR"] == "1") {
                    $parametros = array($perfilModel->getNOME(), $perfilModel->getNIVEL(), $perfilModel->getCODPERFIL());
                    $atributos = "NOME, NIVEL";
                    $result = $perfilDao->update($atributos, $parametros, "CODPERFIL=?");
                    if ($result) {
                        (new LogDao())->salvaLog("PERFIL: ALTERAR", $data["SERVICO"]["CODSERVICO"], $perfilDao->getLogSQL());
                        $alerta->success("Alerado com sucesso!", $returnAction);
                    } else {
                        $alerta->danger("Ops! Aconteceu um erro, tente mais tarde.", $returnAction);
                    }
                } else {
                    $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
                }
            } elseif ($action == "delete") {
                if ($data["SERVICO"]["EXCLUIR"] == "1") {
                    $id = $this->postParams("idExcluir");
                    $parametros = array(1, $id);
                    $atributos = "EXCLUIDO";
                    $result = $perfilDao->update($atributos, $parametros, "CODPERFIL=?");
                    if ($result) {
                        (new LogDao())->salvaLog("PERFIL: EXCLUIR", $data["SERVICO"]["CODSERVICO"], $perfilDao->getLogSQL());
                        $alerta->success("Excluido com sucesso!", $returnAction);
                    } else {
                        $alerta->danger("Ops! Aconteceu um erro, tente mais tarde.", $returnAction);
                    }
                } else {
                    $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
                }
            } else {
                $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
            }

        } catch (\Error $e) {
            return $e;
        }
    }

    public function perfilprivilegiosaction($getParametro = null)
    {
        try {

            $this->isLogged();
            $this->validateRequestMethod("POST");
            $data = $this->getServico();

            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}/" . $returnParams;

            $funcoesClass = new FuncoesLib();
            $alerta = new AlertLib();
            $sisPrivilegioDao = new PrivilegioDao();

            $arrayCodservico = $this->postParams("codservico");
            $codperfil = $this->postParams("codperfil");

            if (!empty($codperfil)) {
                $sisPrivilegioDao->delete([$codperfil], "CODPERFIL=?");

                foreach ($arrayCodservico as $key => $item) {
                    $coservico = $item;
                    $priv_ler = empty($_POST["ckbLer{$item}"])?"0":"1";
                    $priv_salvar = empty($_POST["ckbSalvar{$item}"])?"0":"1";
                    $priv_alterar = empty($_POST["ckbAlterar{$item}"])?"0":"1";
                    $priv_excluir = empty($_POST["ckbExcluir{$item}"])?"0":"1";
                    $priv_outras = empty($_POST["ckbOutros{$item}"])?"0":"1";

                    $result = $sisPrivilegioDao->insert(
                        "CODPERFIL, CODSERVICO, LER, SALVAR, ALTERAR, EXCLUIR, OUTROS",
                        array($codperfil, $coservico, $priv_ler, $priv_salvar, $priv_alterar, $priv_excluir, $priv_outras),
                    );
                }

                (new LogDao())->salvaLog("PRIVILEGIO-PERFIL: CADASTRAR", $data["SERVICO"]["CODSERVICO"], $sisPrivilegioDao->getLogSQL());
                $alerta->success("Registrado com sucesso!", $returnAction);
            } else {
                $alerta->danger("Parametro não enviado!", $returnAction);
            }

        } catch (\Error $e) {
            return $e;
        }
    }

    // ######### USUÁRIOS ###########
    public function usuarios($getParametro = null)
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $usuariosDao = new PessoaDao();
            $tableComponents = new TableLib();

            $data["TITLE_CARD_COMPONENT"] = "Usuários";

            $objServicos = $usuariosDao->buscarTodosUsuarios($this->getParams("buscar"));

            $tableComponents->init($objServicos,
                array("Nome", ""),
                array("buscar" => $this->getParams('buscar')));

            foreach ($objServicos as $key => $item) {
                if ($tableComponents->checkPagination($key)) {

                    $iconeSituacao = $item->SITUACAO == "1"?"text-success":"text-warning";

                    $tableComponents->addCol("<span class='mdi mdi-circle {$iconeSituacao}'></span> ".$item->NOME);
                    $tableComponents->addCol("<div class='dropdown col'>
                        <button class='btn btn-outline-secondary btn-sm ' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                            <span class='mdi mdi-dots-vertical mdi-18px'></span>
                        </button>
                        <ul class='dropdown-menu'>
                            <li><a class='dropdown-item' href='{$data["SERVICO"]["URL"]}-cadastro/?id={$item->CODPESSOA}'><span class='mdi mdi-account-edit-outline'></span> Editar</a></li>
                            <li><a class='dropdown-item' href='#' onclick='alterarSenha(`{$item->CODUSUARIO}`,`{$item->NOME}`, `{$item->EMAIL}`)'><span class='mdi mdi-account-key-outline'></span> Alterar Senha</a></li>
                            <li><a class='dropdown-item' href='#' onclick='trocarUsuario(`{$item->CODUSUARIO}`,`{$item->NOME}`, `{$item->EMAIL}`)'><span class='mdi mdi-login-variant'></span> Logar Como</a></li>
                            <li><a class='dropdown-item' href='{$data["SERVICO"]["URL"]}-perfil/?id={$item->CODUSUARIO}'><span class='mdi mdi-account-group-outline'></span> Alterar Perfil</a></li>
                            <li><a class='dropdown-item' href='#' onclick='alterarSituacaoUsuario(`{$item->CODUSUARIO}`,`".($item->SITUACAO == '1'?'0':'1')."`)'><span class='mdi ".($item->SITUACAO == '1'?'mdi-thumb-down-outline':'mdi-thumb-up-outline')."'></span> ".($item->SITUACAO == '1'?'Desativar':'Ativar')."</a></li>
                        </ul>
                    </div>");


                    $tableComponents->addRow();
                }
            }
            $data["TABLE_COMPONENT"] = $tableComponents->render();
            $data["topTable"] = "components/button_table/button_table.html.twig";

            return $this->render(
                TemplateAbstract::LOGGED,
                $data["SERVICO"]["TEMPLATE"],
                $data,
            );
        } catch (\Error $e) {
            return $e;
        }
    }

    public function usuarioscadastro($args = null)
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            $codpessoa = $this->getParams('id');
            if (!empty($codpessoa)) {
                $pessoaObj = (new PessoaDao)->buscarPessoa($codpessoa);
                $data['PESSOA'] = $pessoaObj[0];
                $data['PESSOAFISICA'] = (new PessoaFisicaDao)->buscarPessoa($codpessoa);
            }

            $data["TITLE_CARD_COMPONENT"] = "Usuários";

            return $this->render(
                TemplateAbstract::LOGGED,
                $data["SERVICO"]["MODULO"]."/usuarios/cadastro",
                $data,
            );
        } catch (\Error $e) {
            return $e;
        }
    }

    public function usuarioscadastroaction()
    {
        try {
            $this->isLogged();
            $data = $this->getServico();
            $this->validateRequestMethod("POST");

            if (!$data["SERVICO"]['SALVAR'] == 1)
                (new AlertLib)->warning('Você não tem permissão para realizar essa ação', "{$data["SERVICO"]["URL"]}-cadastro/");


            $endereco = (new EnderecoModel($_POST));
            $pessoa = (new PessoaModel($_POST));
            $pessoaFisica = (new PessoaFisicaModel($_POST));

            $result = (new PessoaFisicaDao)->inserirUsuario($endereco, $pessoa, $pessoaFisica);

            if (empty($result))
                (new AlertLib)->warning("Opss! Aconteceu um erro", "{$data["SERVICO"]["URL"]}-cadastro/");

            if ($result["error"])
                (new AlertLib)->warning($result["message"], "{$data["SERVICO"]["URL"]}-cadastro/");

            (new AlertLib)->success($result["message"], "{$data["SERVICO"]["URL"]}-cadastro/?id={$result["codpessoa"]}");
        } catch (\Error $e) {
            return $e;
        }
    }

    public function usuariosperfil($args = []){
        try {
            $this->isLogged();
            $data = $this->getServico();

            $sisServicoDao = new ServicoDao();
            $perfilDao = new PerfilDao();

            $data["TITLE_CARD_COMPONENT"] = "Perfil do Usuário";

            $id = $this->getParams("id") ?? "";

            $data["PERFIS"] = $perfilDao->buscarPerfisUsuario($id);
            $data["USUARIO"] = (new UsuarioDao())->buscarCodusuario($id);

            return $this->render(
                TemplateAbstract::LOGGED,
                $data["SERVICO"]["MODULO"] ."/usuarios/perfil",
                $data,
            );


        } catch (\Error $e) {
            return $e;
        }
    }

    public function usuariosperfilaction($args = []){
        try {
            $this->isLogged();
            $this->validateRequestMethod("POST");
            $data = $this->getServico();
            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}/" . $returnParams;

            $alerta = new AlertLib();
            $perfilUsuarioDao = new SiPerfilUsuarioDao();

            $action = $this->postParams("action");
            $codusuario = $this->postParams("CODUSUARIO");
            $perfis = $this->postParams("PERFIS");

            if ($action == "update") {
                if ($data["SERVICO"]["ALTERAR"] == "1") {
                    $result = $perfilUsuarioDao->delete([$codusuario],"CODUSUARIO=?");
                    $atributos = "CODUSUARIO, CODPERFIL";
                    foreach ($perfis as $item) {
                        $parametros = array($codusuario, $item);
                        $result = $perfilUsuarioDao->insert($atributos, $parametros);
                    }
                    if ($result) {
                        (new LogDao())->salvaLog("USUARIO-PERFIL: ALTERAÇÃO DE PERFIL CODUSUARIO {$codusuario}", $data["SERVICO"]["CODSERVICO"]);
                        $alerta->success("Alerado com sucesso!", $returnAction);
                    } else {
                        $alerta->danger("Ops! Aconteceu um erro, tente mais tarde.", $returnAction);
                    }
                } else {
                    $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
                }
            } else {
                $alerta->warning("Nenhuma ação informada!", $returnAction);
            }

        } catch (\Error $e) {
            return $e;
        }
    }

    public function usuariossenhaaction($getParametro = null)
    {
        try {

            $this->isLogged();
            $this->validateRequestMethod("POST");
            $data = $this->getServico();

            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}/" . $returnParams;

            $func = new FuncoesLib();
            $usuarioModel = new UsuarioModel($_POST);
            $alerta = new AlertLib();
            $usuarioDao = new UsuarioDao();

            $senha = $usuarioModel->getSENHA();
            $usuarioModel->setSenha($func->create_password_hash($usuarioModel->getSENHA()));

            // ATUALIZA SENHA
            if (!$usuarioDao->updateSenha($usuarioModel)) {
                $alerta->warning('Aconteceu um erro, tente mais tarde', $returnAction);
            }

            $msge = (new TemplateEmailLib)->template1(
                "Alteração de senha de acesso",
                "Solicitação de alteração de senha",
                "Foi gerando uma nova senha de acesso: <b>{$senha}</b>",
                CONFIG_SITE['url'],
                "Acessar agora");


            if (!EmailLib::sendEmail("Alteração de senha de acesso", $msge, array($usuarioModel->getEMAIL()))) {
                $alerta->warning('Senha alterada, e-mail não enviado!', $returnAction);
            }

            (new LogDao())->salvaLog("USUARIO-SENHA: ALTERAÇÃO DE SENHA DO CODUSUARIO {$usuarioModel->getCODUSUARIO()}", $data["SERVICO"]["CODSERVICO"],$usuarioDao->getLogSQL());
            $alerta->success("Alteração realizada, e-mail enviado!", $returnAction);

        } catch (\Error $e) {
            return $e;
        }
    }

    public function usuariostrocaraction($getParametro = null)
    {
        try {

            $this->isLogged();
            $data = $this->getServico();
            $this->validateRequestMethod("POST");

            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}/" . $returnParams;

            $usuarioDao = new UsuarioDao();
            $jwtTokenClass = new JwtLib();
            $codusuario = $this->postParams("CODUSUARIO");

            $usuarioModel = $usuarioDao->buscarCodusuario($codusuario);

            $data['id'] = $codusuario;
            $token = $jwtTokenClass->encode(1440, $data);
            CookieLib::setValue("token", $token,1);
            SessionLib::setDataSession($usuarioModel);

            (new LogDao())->salvaLog("USUARIO-LOGAR-COMO: LOGOU COMO CODUSUARIO: {$codusuario}", $data["SERVICO"]["CODSERVICO"], null);
            (new AlertLib())->success("Efetuado com sucesso!", "/");

        } catch (\Error $e) {
            return $e;
        }
    }

    public function usuariossituacaoaction($args = []){
        try {
            $this->isLogged();
            $this->validateRequestMethod("POST");
            $data = $this->getServico();
            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "{$data["SERVICO"]["URL"]}/" . $returnParams;

            $alerta = new AlertLib();
            $usuarioModel = new UsuarioModel($_POST);

            if ($data["SERVICO"]["ALTERAR"] == "1") {
                $result =(new UsuarioDao())->update("SITUACAO",[$usuarioModel->getSITUACAO(),$usuarioModel->getCODUSUARIO()],"CODUSUARIO=?");

                if ($result) {
                    (new LogDao())->salvaLog("USUARIO: ALTERAÇÃO DA SITUACAO CODUSUARIO {$usuarioModel->getCODUSUARIO()}", $data["SERVICO"]["CODSERVICO"]);
                    $alerta->success("Alerado com sucesso!", $returnAction);
                } else {
                    $alerta->danger("Ops! Aconteceu um erro, tente mais tarde.", $returnAction);
                }
            } else {
                $alerta->warning("Você não tem privilégio para executar essa ação!", $returnAction);
            }


        } catch (\Error $e) {
            return $e;
        }
    }

}