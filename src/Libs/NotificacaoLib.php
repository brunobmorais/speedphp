<?php

namespace App\Libs;

use App\Daos\intranet\FuncionarioDao;
use App\Daos\intranet\NotificacaoDao;
use App\Daos\PessoaDao;
use App\Daos\SiNotificacaoDao;

class NotificacaoLib
{
    protected $title;
    protected $message;
    protected $codpessoa;
    protected $link;
    protected $tipo;

    protected $sendPushNotificationImportant = false;
    protected $sendEmailImportant = false;

    /**
     * @param string $title
     * @param string $message
     * @param string $link
     * @param int $tipo // 0 = DEFAULT; 1 = ESCALA; 2 = BOLETIM;3 = NOTICIA; 4 = ALMANAQUE; 5 = TROCA; 6 = AUTORIZA TROCA; 7 = EXTERNO
     */
    public function __construct(string $title, string $message, string $link, int $tipo = 0)
    {
        $this->title = $title;
        $this->message = $message;
        $this->link = $link;
        $this->tipo = $tipo;

        return $this;
    }

    public function setSendPushNotificationImportant(bool $status)
    {
        $this->sendPushNotificationImportant = $status;
    }

    public function setSendEmailImportant(bool $status)
    {
        $this->sendEmailImportant = $status;
    }

    /**
     * @param $cpf
     * @return bool
     */
    public function insertOne(string $codpessoa): bool
    {
        $this->codpessoa = $codpessoa;

        $objFuncionario = (new PessoaDao)->buscarOnePessoaId($this->codpessoa);
        if (empty($objFuncionario)) {
            return false;
        }

        $email = $objFuncionario->EMAIL;

        // REGISTRAR NA TABELA NOTIFICAÇÃO
        $arrayNotificacao = [
            'title' => $this->title,
            'message' => $this->message,
            'codpessoa' => $this->codpessoa,
            'link' => $this->link
        ];
        $notificacao = (new SiNotificacaoDao())->novaNotificacao($arrayNotificacao);

        // ENVIAR EMAIL
        $msge = (new TemplateEmailLib)->template1(
            $this->title,
            $this->message,
            $this->message,
            CONFIG_SITE['url'] . $this->link,
            "Acessar");

        $resultEmail = EmailLib::sendEmail($this->title, $msge, [$email]);


        return true;
    }

    public function insertArray(array $codpessoas): bool
    {
        foreach ($codpessoas as $codpessoa) {
            $this->codpessoa = $codpessoa;

           $objFuncionario = (new PessoaDao)->buscarOnePessoaId($this->codpessoa);
            if (empty($objFuncionario)) {
                return false;
            }

            $email = $objFuncionario->EMAIL;

            // REGISTRAR NA TABELA NOTIFICAÇÃO
            $arrayNotificacao = [
                'title' => $this->title,
                'message' => $this->message,
                'codpessoa' => $this->codpessoa,
                'link' => $this->link
            ];
            $notificacao = (new SiNotificacaoDao())->novaNotificacao($arrayNotificacao);

            // ENVIAR EMAIL
            $msge = (new TemplateEmailLib)->template1(
                $this->title,
                $this->message,
                $this->message,
                CONFIG_SITE['url'] . $this->link,
                "Acessar");

            $resultEmail = EmailLib::sendEmail($this->title, $msge, [$email]);

        }

        return true;
    }

    /**
     * @param $cpf
     * @return bool
     */
    public function insertAll(): bool
    {
        $objFuncionario = (new PessoaDao())->buscarAll();
        if (empty($obj)) {
            return false;
        }

        foreach ($obj as $objFuncionario) {
            $email = $objFuncionario->EMAIL;
            $this->codpessoa = $objFuncionario->CODPESSOA;

            // REGISTRAR NA TABELA NOTIFICAÇÃO
            $arrayNotificacao = [
                'title' => $this->title,
                'message' => $this->message,
                'codpessoa' => $this->codpessoa,
                'link' => $this->link
            ];
            $notificacao = (new SiNotificacaoDao())->novaNotificacao($arrayNotificacao);

            // ENVIAR EMAIL
            $resultEmail = EmailLib::sendEmail($this->title, $this->message, [$email], $this->link);

        }
        return true;
    }

}
