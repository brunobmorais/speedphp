<?php
namespace App\Controllers;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Core\PageCore;
use App\Core\Template\BlankTemplate;
use App\Core\Template\LoggedTemplate;
use App\Core\Template\TemplateAbstract;

class ErroController extends ControllerCore implements ControllerInterface
{
    /*
    * chama a view index.php do  /menu   ou somente   /
    */
    public function index($args  = [])
    {
        return $this->render(
            TemplateAbstract::BLANK,
                'erro/index',['TITLE' => 'Página não encontrada']);
    }

    public function erro500()
    {
        return $this->render(
            TemplateAbstract::BLANK,
                'erro/500',['TITLE' => 'Ops! Erro no servidor']);
    }

    public function erro503()
    {
        return $this->render(
            TemplateAbstract::BLANK,
                'erro/503',['TITLE' => 'Ops! Erro no servidor']);
    }

    public function seminternet()
    {
        return $this->render(
            TemplateAbstract::BLANK,
                'erro/seminternet',['TITLE' => 'Sem internet']);
    }

    public function database()
    {
        return $this->render(
            TemplateAbstract::BLANK,
                'erro/database',['TITLE' => 'Erro no servidor']);
    }

    public function manutencao()
    {
        return $this->render(
            TemplateAbstract::BLANK,
                'erro/manutencao',['TITLE' => 'Erro no servidor']);
    }

}
