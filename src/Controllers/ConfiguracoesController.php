<?php

namespace App\Controllers;


use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Core\PageCore;
use App\Core\Template\LoggedTemplate;
use App\Core\Template\ModuloTemplate;
use App\Core\Template\TemplateAbstract;
use App\Daos\ModuloDao;
use App\Daos\PerfilDao;
use App\Daos\PessoaDao;
use App\Daos\PrivilegioDao;
use App\Daos\ServicoDao;
use App\Libs\AlertLib;
use App\Libs\FuncoesLib;
use App\Libs\TableLib;
use App\Models\ModuloModel;
use App\Models\PerfilModel;
use App\Models\ServicoModel;

class ConfiguracoesController extends ControllerCore implements ControllerInterface
{
    /*
    * chama a view index.php   /
    */
    public function index($args = [])
    {
        try {
            return $this->render(TemplateAbstract::MODULE);
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
            $tableComponents = new TableLib();

            $data["titleCardComponent"] = "Módulos";

            $objModulos = $moduloDao->buscaModulos($data['GETPARAMS']['buscar']);

            $tableComponents->defineVars($objModulos,
            array("Icone", "Titulo", "Descrição", "Controller", ""),
            array("buscar" => $data['GETPARAMS']['buscar']));

            foreach ($objModulos as $key => $item) {
                $line = [];
                if (($key >= $tableComponents->getInicioPg()) and ($key < $tableComponents->getFimPg())) {

                    $line[] = $item->ICONE;
                    $line[] = $item->TITULO;
                    $line[] = $item->DESCRICAO;
                    $line[] = $item->CONTROLLER;
                    $line[] = $data["SERVICO"]["ALTERAR"] == "1" ? "<a href='/{$data["SERVICO"]["url"]}-cadastro/?id={$item->CODMODULO}&pg={$data['GETPARAMS']['pg']}&buscar={$data['GETPARAMS']['buscar']}' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-pencil-outline'></span> Editar</a>" : "";
                    //$tableComponents->link = 'location.href="cadDiversos.php?id='.$_GET['id'].'&div='.$aID.'&amp;pg='.$pg.'"';
                    $tableComponents->addLine($line);
                }
            }
            $data["tableComponent"] = $tableComponents->render();
            $data["tableButtonInputPlaceholder"] = "Buscar...";
            $data["tablebuttonComponent"] = $this->render(TemplateAbstract::COMPONENT, "components/button_table", $data);

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

            $data["titleCardComponent"] = "Cadastro de módulos";

            $id = $this->getParams("id") ?? "";
            $buscar = $this->getParams("buscar") ?? "";
            $pg = $this->getParams("pg") ?? "";

            if (!empty($id)) {
                $data["MODULO"] = $moduloDao->select("*", "WHERE CODMODULO=? AND SITUACAO!='X'", array($id))[0] ?? "";
            }

            return $this->render(
                TemplateAbstract::LOGGED,
                $data["SERVICO"]["url"] . "cadastro",
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
            $returnAction = "/{$data["SERVICO"]["url"]}/" . $returnParams;

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

            $data["titleCardComponent"] = "Serviços";

            $objServicos = $sisServico->buscaServicos($data["GETPARAMS"]["buscar"]);

            $tableComponents->defineVars($objServicos,
                array("Módulo", "Serviço", ""),
                array("buscar" => $data['GETPARAMS']['buscar']));

            foreach ($objServicos as $key => $item) {
                $line = [];
                if (($key >= $tableComponents->getInicioPg()) and ($key < $tableComponents->getFimPg())) {

                    $line[] = $item->TITULOMODULO;
                    $line[] = $item->TITULO;
                    $line[] = $data["SERVICO"]["ALTERAR"] == "1" ? "<a href='/{$data["SERVICO"]["url"]}-cadastro/?id={$item->CODSERVICO}&pg={$data['GETPARAMS']['pg']}&buscar={$data['GETPARAMS']['buscar']}' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-pencil-outline'></span> Editar</a>" : "";
                    $tableComponents->addLine($line);
                }
            }
            $data["tableComponent"] = $tableComponents->render();
            $data["tableButtonInputPlaceholder"] = "Buscar...";
            $data["tablebuttonComponent"] = $this->render("Component","components/button_table", $data);

            return $this->render(
                "Logged",
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

            $data["titleCardComponent"] = "Cadastrar serviços";

            $id = $this->getParams("id") ?? "";

            if (!empty($id)) {
                $data["DATASERVICO"] = $sisServicoDao->select("*", "WHERE CODSERVICO=? AND EXCLUIDO!='1'", array($id))[0] ?? "";
            }
            $data["DATAMODULOS"] = $sisModuloDao->select("*", "WHERE EXCLUIDO!='1'");

            return $this->render(
                "Logged",
                $data["SERVICO"]["url"] . 'cadastro',
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
            $returnAction = "/{$data["SERVICO"]["url"]}/" . $returnParams;

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

            $data["titleCardComponent"] = "Privilégios";

            $data["PERFIS"] = $perfilDao->buscarPerfis();
            return $this->render(
                "Logged",
                $data["SERVICO"]["url"],
                $data,
            );
         } catch (\Error $e) {
            return $e;
        }
    }


    public function privilegiosaction($getParametro = null)
    {
        try {

            $this->isLogged();
            $this->validateRequestMethod("POST");
            $data = $this->getServico();

            $returnParams = ($this->postParams("pg") != "" ? "?pg=" . $this->postParams("pg") : "") . ($this->postParams("buscar") != "" ? "&buscar=" . $this->postParams("buscar") : "");
            $returnAction = "/{$data["SERVICO"]["url"]}/" . $returnParams;

            $funcoesClass = new FuncoesLib();
            $alerta = new AlertLib();
            $sisPrivilegioDao = new PrivilegioDao();

            $arrayCodservico = $this->postParams("codservico");
            $codusuario = $this->postParams("codusuario");

            if (!empty($codusuario)) {
                $sisPrivilegioDao->delete([$codusuario], "CODUSUARIO=?");

                foreach ($arrayCodservico as $key => $item) {
                    $coservico = $item;
                    $priv_ler = empty($_POST["ckbLer{$item}"])?"0":"1";
                    $priv_salvar = empty($_POST["ckbSalvar{$item}"])?"0":"1";
                    $priv_alterar = empty($_POST["ckbAlterar{$item}"])?"0":"1";
                    $priv_excluir = empty($_POST["ckbExcluir{$item}"])?"0":"1";
                    $priv_outras = empty($_POST["ckbOutros{$item}"])?"0":"1";

                    $result = $sisPrivilegioDao->insert(
                        "CODUSUARIO, CODSERVICO, LER, SALVAR, ALTERAR, EXCLUIR, OUTROS",
                        array($codusuario, $coservico, $priv_ler, $priv_salvar, $priv_alterar, $priv_excluir, $priv_outras),
                    );
                }

                $alerta->success("Registrado com sucesso!", $returnAction);
            } else {
                $alerta->danger("Parametro não enviado!", $returnAction);
            }

         } catch (\Error $e) {
            $alerta->danger("Ops! Aconteceu um erro, tente mais tarde!", $returnAction);
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

            $data["titleCardComponent"] = "Perfis";

            $objServicos = $perfilDao->buscarPerfis($data["GETPARAMS"]["buscar"]);

            $tableComponents->defineVars($objServicos,
                array("Perfil", "Nível", ""),
                array("buscar" => $data['GETPARAMS']['buscar']));

            foreach ($objServicos as $key => $item) {
                $line = [];
                if (($key >= $tableComponents->getInicioPg()) and ($key < $tableComponents->getFimPg())) {

                    $line[] = $item->NOME;
                    $line[] = $item->NIVEL;
                    $line[] = ($data["SERVICO"]["ALTERAR"] == "1" ? "<a href='/{$data["SERVICO"]["url"]}-cadastro/?id={$item->CODPERFIL}&pg={$data['GETPARAMS']['pg']}&buscar={$data['GETPARAMS']['buscar']}' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-pencil-outline'></span> Editar</a>" : "").
                        " <a href='/{$data["SERVICO"]["modulo"]}/perfil-privilegios/?id={$item->CODPERFIL}&pg={$data['GETPARAMS']['pg']}&buscar={$data['GETPARAMS']['buscar']}' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-pencil-outline'></span> Privilégios</a>";
                    $tableComponents->addLine($line);
                }
            }
            $data["tableComponent"] = $tableComponents->render();
            $data["tableButtonInputPlaceholder"] = "Buscar...";
            $data["tablebuttonComponent"] = $this->render("Component","components/button_table", $data);

            return $this->render(
                "Logged",
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

            $data["titleCardComponent"] = "Cadastrar privilégios";

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
                    $data["SERVICO"]["url"] . 'privilegio',
                    $data,
                );
            } else {
                $alerta->warning("Usuário não encontrado!", "/{$data["SERVICO"]["url"]}/");
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

            $data["titleCardComponent"] = "Cadastrar";

            $id = $this->getParams("id") ?? "";

            $data["PERFIL"] = $perfilDao->buscarPerfilId($id);

            return $this->render(
                "Logged",
                $data["SERVICO"]["url"] . 'cadastro',
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
            $returnAction = "/{$data["SERVICO"]["url"]}/" . $returnParams;

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

    // ######### USUÁRIOS ###########
    public function usuarios($getParametro = null)
    {
        try {
            $this->isLogged();
            $data = $this->getServico();

            $usuariosDao = new PessoaDao();
            $tableComponents = new TableLib();

            $data["titleCardComponent"] = "Usuários";

            $objServicos = $usuariosDao->buscarTodosFuncionarios($data["GETPARAMS"]["buscar"]);

            $tableComponents->defineVars($objServicos,
                array("Nome", "Email", ""),
                array("buscar" => $data['GETPARAMS']['buscar']));

            foreach ($objServicos as $key => $item) {
                $line = [];
                if (($key >= $tableComponents->getInicioPg()) and ($key < $tableComponents->getFimPg())) {

                    $line[] = $item->NOME;
                    $line[] = $item->EMAIL;
                    $line[] = "<a href='/{$data["SERVICO"]["modulo"]}/alterarsenha/?id={$item->CODUSUARIO}&pg={$data['GETPARAMS']['pg']}&buscar={$data['GETPARAMS']['buscar']}' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-pencil-outline'></span> Alterar Senha</a>".
                        " <a href='/{$data["SERVICO"]["modulo"]}/trocarusuario/?id={$item->CODUSUARIO}&pg={$data['GETPARAMS']['pg']}&buscar={$data['GETPARAMS']['buscar']}' class='btn btn-outline-secondary btn-sm'><span class='mdi mdi-pencil-outline'></span> Trocar Usuário</a>";

                    $tableComponents->addLine($line);
                }
            }
            $data["tableComponent"] = $tableComponents->render();
            $data["tableButtonInputPlaceholder"] = "Buscar...";
            $data["tablebuttonComponent"] = $this->render(TemplateAbstract::COMPONENT,"components/button_table", $data);

            return $this->render(
                TemplateAbstract::LOGGED,
                "components/page_table_default",
                $data,
            );
        } catch (\Error $e) {
            return $e;
        }
    }
}
