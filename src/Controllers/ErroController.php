<?php
namespace App\Controllers;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Core\PageCore;
use App\Core\Template\DefaultTemplate;
use App\Core\Template\LoggedTemplate;

class ErroController extends ControllerCore implements ControllerInterface
{
    /*
    * chama a view index.php do  /menu   ou somente   /
    */
    public function index($args  = [])
    {
        return $this->render(
            "Default",
                'erro/index',['TITLE' => 'Página não encontrada']);
    }

    public function erro500()
    {
        return $this->render(
            "Default",
                'erro/500',['TITLE' => 'Ops! Erro no servidor']);
    }

    public function erro503()
    {
        return $this->render(
            "Default",
                'erro/503',['TITLE' => 'Ops! Erro no servidor']);
    }

    public function seminternet()
    {
        return $this->render(
            "Default",
                'erro/seminternet',['TITLE' => 'Sem internet']);
    }

    public function database()
    {
        return $this->render(
            "Default",
                'erro/database',['TITLE' => 'Erro no servidor']);
    }

    public function manutencao()
    {
        return $this->render(
            "Default",
                'erro/manutencao',['TITLE' => 'Erro no servidor']);
    }

}
