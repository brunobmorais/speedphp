<?php

namespace App\Libs\AgentesLLM;

use App\Daos\PessoaFisicaDao;
use App\Daos\WppMensagensDao;
use App\Libs\OpenAiApi;

class AutorizarContatoWhatsapp
{

    private WppMensagensDao $wppMensagensDao;

    private PessoaFisicaDao $pessoaFisicaDao;

    private OpenAiApi $openAi;

    private string $model_llm = 'gpt-4o-mini';

    private float $temperatura = 0.7;

    private int $max_tokens = 500;

    private const RATE_LIMIT_PER_HOUR = 10;

    public function __construct(
        ?WppMensagensDao $wppMensagensDao = null,
        ?PessoaFisicaDao $pessoaFisicaDao = null,
        ?OpenAiApi $openAi = null
    ) {
        $this->wppMensagensDao = $wppMensagensDao ?? new WppMensagensDao();
        $this->pessoaFisicaDao = $pessoaFisicaDao ?? new PessoaFisicaDao();
        $this->openAi = $openAi ?? new OpenAiApi();
    }


    public function entenderMensagensRecentes($phone)
    {
        $countAgente = $this->wppMensagensDao->contarMensagensAgente($phone, 60);
        if ($countAgente >= self::RATE_LIMIT_PER_HOUR) {
            return [
                'intencao' => 'PENDENTE',
                'mensagem' => 'Estou processando muitas mensagens no momento. Por favor, aguarde um instante antes de enviar outra mensagem.',
            ];
        }

        $mensagensRecentes = $this->wppMensagensDao->buscarUltimasMensagensPorTelefone($phone, 20, 1);
        $pessoa = $this->pessoaFisicaDao->buscarPessoaTelefoneWhatsapp($phone);

        $systemPrompt = $this->montarSystemPrompt($pessoa);
        $messages = $this->montarHistoricoMensagens($systemPrompt, $mensagensRecentes);

        $payload = [
            'model' => $this->model_llm,
            'temperature' => $this->temperatura,
            'max_tokens' => $this->max_tokens,
            'response_format' => ['type' => 'json_object'],
            'messages' => $messages,
        ];

        $response = $this->openAi->response($payload);
        $content = $response->choices[0]->message->content ?? '';
        $json = json_decode($content, true);

        if (is_array($json)) {
            $this->resolve($json['intencao'] ?? null, $phone);
        }

        return $json;
    }

    private function montarHistoricoMensagens(string $systemPrompt, array $mensagensRecentes): array
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        $historicoOrdenado = array_reverse($mensagensRecentes);

        foreach ($historicoOrdenado as $msg) {
            $texto = $msg->MESSAGE ?? '';
            if (empty(trim($texto))) {
                continue;
            }

            $role = ($msg->ORIGEM == 1) ? 'assistant' : 'user';
            $messages[] = ['role' => $role, 'content' => $texto];
        }

        return $messages;
    }

    private function montarSystemPrompt(?object $pessoa): string
    {
        $isCadastrado = !empty($pessoa);

        $contextoUsuario = "- **Telefone**: informado via webhook\n";
        $contextoUsuario .= $isCadastrado
            ? "- **Status**: USUARIO CADASTRADO\n- **Nome**: {$pessoa->NOME}\n- **Email**: {$pessoa->EMAIL}\n"
            : "- **Status**: NOVO USUARIO\n";

        $instrucoes = $isCadastrado
            ? $this->getInstrucoesCadastrado($pessoa)
            : $this->getInstrucoesNovo();

        return <<<PROMPT
        # ASSISTENTE VIRTUAL WHATSAPP - VIA ESPORTE

        Voce e um assistente virtual da plataforma ViaEsporte, especializada em eventos esportivos (corridas, maratonas, trails, ciclismo, etc).

        ## CONTEXTO DO USUARIO:
        {$contextoUsuario}

        ## INSTRUCOES ESPECIFICAS:

        {$instrucoes}

        ## REGRAS GERAIS:

        1. **Tom de Voz**:
        - Cordial e descontraido, sem soar nem informal nem formal
        - Linguagem natural e conversacional
        - Evite ser robotizado

        2. **Respostas**:
        - Seja OBJETIVO e CONCISO (maximo 3-4 frases)
        - Nao invente informacoes que voce nao tem

        3. **Privacidade**:
        - NUNCA compartilhe dados pessoais de outros usuarios
        - Seja transparente sobre o que voce pode e nao pode fazer

        4. **Escopo**:
        - Se a pergunta fugir do escopo principal, explique em tom amistoso que voce esta aqui para ajudar com notificacoes de eventos esportivos
        - Direcione o usuario a acessar o site para outras necessidades
        - Se for um usuario cadastrado, chame pelo primeiro nome

        5. **Intencao de notificacao**:
        - ACEITA: O usuario confirmou claramente que quer receber mensagens
        - RECUSA: O usuario disse que nao quer, pediu para parar ou descadastrar
        - PENDENTE: Historico inconclusivo, o usuario nao respondeu a pergunta ou apenas saudou (ex: "oi")

        ## FORMATO DE SAIDA:
        Sua saida deve ser estritamente um JSON no formato:
        {"intencao": "ACEITA|RECUSA|PENDENTE", "mensagem": "texto aqui"}
        PROMPT;
            }

    private function getInstrucoesCadastrado(object $pessoa): string
    {
        $nome = $pessoa->NOME ?? 'atleta';
        $primeiroNome = explode(' ', trim($nome))[0];

        return <<<INSTRUCOES
        ### USUARIO JA CADASTRADO

        Este usuario ja esta na base da ViaEsporte.

        **SUA TAREFA PRINCIPAL:**
        - Trate o usuario pelo primeiro nome: {$primeiroNome}
        - Pergunte de forma natural e amigavel se ele gostaria de receber notificacoes sobre seus eventos pelo WhatsApp
        - Explique brevemente os beneficios: atualizacoes sobre inscricoes, resultados, novidades dos eventos
        - Se ele aceitar, confirme que a preferencia foi registrada
        - Se ele recusar, confirme com respeito


        **PERSONALIZACAO:**
        - Mostre que voce conhece o usuario
        - Seja cordial e profissional
        INSTRUCOES;
    }

    private function getInstrucoesNovo(): string
    {
                return <<<INSTRUCOES
        ### NOVO USUARIO

        Este e um novo contato que ainda nao esta cadastrado na ViaEsporte.

        **SUA TAREFA:**
        - De boas-vindas de forma calorosa
        - Informe que esse número é apenas para envio de notificações e avisos sobre os eventos esportivos
        - Pergunte se gostaria de receber notificacoes sobre eventos pelo WhatsApp
        - Seja receptivo e crie uma boa primeira impressao
        INSTRUCOES;
    }


    public function resolve($intencao, $phone)
    {
        switch ($intencao) {
            case 'ACEITA':
                // TODO: Atualizar preferencia de notificacao no banco quando coluna existir
                break;
            case 'RECUSA':
                // TODO: Atualizar preferencia de notificacao no banco quando coluna existir
                break;
            default:
                return;
        }
    }
}
