<?php

namespace App\Libs;

use App\Daos\ArquivoDao;
use App\Daos\ColaboradorDao;
use App\Daos\EventoDao;
use App\Daos\PessoaDao;
use App\Daos\PrAndamentoArquivoDao;
use App\Daos\PrAndamentoDao;
use App\Daos\PrAoscuidadosDao;
use App\Daos\PrProtocoloDao;
use App\Daos\PrTipoprotocoloDao;
use App\Enums\ProtocoloSituacaoEnum;
use App\Enums\ProtocoloTipoAndamentoEnum;
use App\Models\PrAndamentoModel;
use App\Models\PrAoscuidadosModel;
use App\Models\PrProtocoloModel;

class ProtocoloLib
{

    public $protocolo;

    public $cadastrante;

    public $evento;

    public $andamentos;

    public $aosCuidados;

    public $equipeOrganizadora;

    public function mount($codprotocolo)
    {
        $this->protocolo = (new PrProtocoloDao)->buscarProtocolo($codprotocolo);

        $this->evento = (new EventoDao)->buscarEventoCodevento($this->protocolo->CODEVENTO_PROTOCOLO);

        $this->cadastrante = (new PessoaDao)->buscarPessoa($this->protocolo->CODPESSOA_CADASTRO)[0];

        $this->andamentos = (new PrAndamentoDao)->buscarAndamentos($this->protocolo->CODPROTOCOLO);

        $this->aosCuidados = (new PrAoscuidadosDao)->buscarCodprotocolo($this->protocolo->CODPROTOCOLO);

        $this->equipeOrganizadora = (new ColaboradorDao)->buscarCodevento($this->evento->CODEVENTO);

        $prAndamentoArquivoDao = new \App\Daos\PrAndamentoArquivoDao();
        foreach ($this->andamentos as $andamento) {
            $andamento->cadastrante = (new PessoaDao)->buscarPessoa($andamento?->CODPESSOA_RESPONSAVEL)[0];
            $andamento->arquivos = $prAndamentoArquivoDao->buscarArquivos($andamento?->CODANDAMENTO);
        }
        return $this;
    }

    public function getSituacao()
    {
        if (!$this->aosCuidados and $this->protocolo->SITUACAO == ProtocoloSituacaoEnum::EM_ANALISE->value) {
            return ProtocoloSituacaoEnum::AGUARDANDO;
        }
        return ProtocoloSituacaoEnum::tryFrom($this->protocolo->SITUACAO);
    }

    public function getTitulo()
    {
        $titulo = (new PrTipoprotocoloDao)?->buscarCod($this->protocolo->TIPO)->NOME;
        return $titulo;
    }


    public function getQtdComentarios()
    {
        $andamentos = array_filter($this->andamentos, function ($e) {
            return in_array($e->CODTIPOANDAMENTO, array(ProtocoloTipoAndamentoEnum::PADRAO->value));
        });

        return count($andamentos) - 1;
    }

    public function aguardandoRespostaModerador(): bool
    {
        if ($this->getSituacao() !== ProtocoloSituacaoEnum::EM_ANALISE) {
            return false;
        }

        $comentarios = array_values(array_filter($this->andamentos, function ($e) {
            return $e->CODTIPOANDAMENTO == ProtocoloTipoAndamentoEnum::PADRAO->value;
        }));

        if (empty($comentarios)) {
            return true;
        }

        $ultimoComentario = end($comentarios);
        return $ultimoComentario->CODPESSOA_RESPONSAVEL == $this->protocolo->CODPESSOA_CADASTRO;
    }


    public function podeIniciarAtendimento()
    {
        $naoTemProtocolo = !isset($this->aosCuidados->CODPROTOCOLO);

        return $this->isEquipeOrganizadora(SessionLib::getValue('CODPESSOA')) && $naoTemProtocolo && $this->getSituacao() == ProtocoloSituacaoEnum::AGUARDANDO;
    }

    public function isAdministrador($codusuario = null)
    {
        if (is_null($codusuario)) {
            $codusuario = SessionLib::getValue('CODPESSOA');
        }
        return in_array($codusuario, explode(',', CONFIG_ADMIN['codpessoa']));
    }

    public function isEquipeOrganizadora($codusuario = null)
    {
        if (is_null($codusuario)) {
            $codusuario = SessionLib::getValue('CODPESSOA');
        }
        $data =  (in_array($codusuario, explode(',', CONFIG_ADMIN['codpessoa'])) ||  in_array($codusuario, array_column($this->equipeOrganizadora, 'CODPESSOA_USUARIO')));

        return $data;
    }

    public function isCadastrante($codusuario)
    {
        return $this->protocolo->CODPESSOA_CADASTRO == $codusuario;
    }

    public function podeGerenciarAtendimento()
    {
        if ($this->getSituacao() == ProtocoloSituacaoEnum::EM_ANALISE && $this->isEquipeOrganizadora(SessionLib::getValue('CODPESSOA'))) {
            return true;
        }
    }

    public function podeAdicionarAndamento()
    {
        if (
            ($this->getSituacao() == ProtocoloSituacaoEnum::EM_ANALISE ||
                $this->getSituacao() == ProtocoloSituacaoEnum::AGUARDANDO
            ) &&
            ($this->isEquipeOrganizadora(SessionLib::getValue('CODPESSOA')) || $this->isCadastrante(SessionLib::getValue('CODPESSOA'))) && $this->aosCuidados
        ) {
            return true;
        }
    }

    public function novoAndamento($codprotocolo, $titulo, $descricao, $tipo)
    {
        $andamento = new PrAndamentoModel();
        $andamento->setCODPROTOCOLO($codprotocolo);
        $andamento->setTITULO($titulo);
        $andamento->setDESCRICAO($descricao);
        $andamento->setCODTIPOANDAMENTO($tipo);
        return (new PrAndamentoDao)->criarAndamento($andamento);
    }

    public function inserirImagens($base64, $codandamento)
    {
        try {

            $diretorioImgEvento = "/public/assets/upload/suporte/";
            $fileLib = new FileLib();

            $base64String = "data:{$base64['type']};base64,{$base64['data']}";

            $nomeArquivo = $fileLib->uploadFileBase64($base64String, $diretorioImgEvento);


            //se for imagem
            if (str_starts_with($base64['type'], 'image/')) {
                $caminhoImagem = $_SERVER['DOCUMENT_ROOT'] . $diretorioImgEvento . $nomeArquivo;
                $arquivoFinal = (new FileLib())->convertImgToPdfBase64($caminhoImagem, $diretorioImgEvento);
            } else {
                $arquivoFinal = $nomeArquivo;
            }

            $codarquivo = (new ArquivoDao)->insertArquivo(1, $arquivoFinal, 'CODPROTOCOLO ' . $this->protocolo->CODPROTOCOLO);


            return (new PrAndamentoArquivoDao)->inserirImagem($codarquivo, $codandamento);
        } catch (\Throwable $e) {
            return $e;
        }
    }


    public function iniciarAtendimento($codprotocolo)
    {
        if (!$this->isEquipeOrganizadora(SessionLib::getValue('CODPESSOA'))) {
            throw new \Exception('Não autorizado', 403);
        }
        return (new PrAoscuidadosDao)->atribuir($codprotocolo, SessionLib::getValue('CODPESSOA'));
    }

    public function marcarConcluido($codprotocolo, $com_andamento = true)
    {
        if ($com_andamento && !$this->isEquipeOrganizadora(SessionLib::getValue('CODPESSOA'))) {
            throw new \Exception('Não autorizado', 403);
        }

        try {
            $andamento = new PrAndamentoModel();
            $andamento->setCODPROTOCOLO($codprotocolo);
            $andamento->setTITULO('Concluiu o atendimento');
            $andamento->setDESCRICAO('Finalizou este atendimento');
            $andamento->setCODTIPOANDAMENTO(ProtocoloTipoAndamentoEnum::ACAO->value);


            $protocolo = new PrProtocoloModel;
            $protocolo->setCODPROTOCOLO($codprotocolo);
            $protocolo->setSITUACAO(ProtocoloSituacaoEnum::CONCLUIDO->value);
            return $com_andamento ?
                (new PrProtocoloDao)->finalizarProtocolo($protocolo, $andamento) : (new PrProtocoloDao)->finalizarProtocoloSemAndamento($protocolo, $andamento);
        } catch (\Throwable $e) {
            return $e;
        }
    }

    public function marcarCancelado($codprotocolo)
    {
        try {
            if (!$this->isEquipeOrganizadora(SessionLib::getValue('CODPESSOA')) and !$this->isCadastrante(SessionLib::getValue('CODPESSOA'))) {
                throw new \Exception('Não autorizado', 403);
            }
            $andamento = new PrAndamentoModel();
            $andamento->setCODPROTOCOLO($codprotocolo);
            $andamento->setTITULO('Cancelou o atendimento');
            $andamento->setDESCRICAO('Cancelou o atendimento');
            $andamento->setCODTIPOANDAMENTO(ProtocoloTipoAndamentoEnum::ACAO->value);

            $protocolo = new PrProtocoloModel;
            $protocolo->setCODPROTOCOLO($codprotocolo);
            $protocolo->setSITUACAO(ProtocoloSituacaoEnum::CANCELADO->value);

            return (new PrProtocoloDao)->finalizarProtocolo($protocolo, $andamento);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function transferirPessoa($codprotocolo, $codpessoa)
    {
        if (!$this->isEquipeOrganizadora(SessionLib::getValue('CODPESSOA'))) {
            throw new \Exception('Não autorizado', 403);
        }

        return (new PrAoscuidadosDao)->transferir($codprotocolo, $codpessoa);
    }

    public function assumirProtocolo($codprotocolo)
    {
        if (!$this->isEquipeOrganizadora(SessionLib::getValue('CODPESSOA'))) {
            throw new \Exception('Não autorizado', 403);
        }

        return (new PrAoscuidadosDao)->assumir($codprotocolo);
    }

    public function transferirViaEsporte($codprotocolo)
    {
        if (!$this->isEquipeOrganizadora(SessionLib::getValue('CODPESSOA'))) {
            throw new \Exception('Não autorizado', 403);
        }

        return (new PrAoscuidadosDao)->transferirViaEsporte($codprotocolo);
    }

    public function transferirEvento($codevento, $codprotocolo)
    {
        if (!in_array(SessionLib::getValue('CODPESSOA'), explode(',', CONFIG_ADMIN['codpessoa']))) {
            throw new \Exception('Não autorizado', 403);
        }

        return (new PrAoscuidadosDao)->transferirEvento($codprotocolo, $codevento);
    }

    public function removerArquivo($codarquivo)
    {
        if (!$this->isEquipeOrganizadora(SessionLib::getValue('CODPESSOA'))) {
            throw new \Exception('Não autorizado', 403);
        }
        return (new PrAndamentoArquivoDao)->removerArquivo($codarquivo);
    }
}
