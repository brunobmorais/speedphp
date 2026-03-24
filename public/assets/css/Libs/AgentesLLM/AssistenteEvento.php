<?php

namespace App\Libs\AgentesLLM;


use App\Libs\OpenAiApi;

use App\Daos\EventoDao;
use App\Daos\TipoEventoDao;
use App\Libs\SessionLib;

class AssistenteEvento
{
    private OpenAiApi $openAi;

      public function __construct()
    {
        $this->openAi = new OpenAiApi();
    }

    public function processarRegulamento($textoPDF)
    {
        $textoLimpo = preg_replace("/\s+/", " ", $textoPDF);
        $textoLimpo = trim($textoLimpo);
        $tiposEvento = (new TipoEventoDao)->getTipoEvento();
        $tiposEventoEnum = array_values(array_map(fn($tipo) => (int)$tipo->CODTIPO_EVENTO, $tiposEvento));
        $statusEnum = ["encontrado", "nao_encontrado", "inferido"];
        $urlUsadas = json_encode(array_column((new EventoDao)->getURLEventos(), 'URL'));

        $buildFieldSchema = function (array $valorSchema) use ($statusEnum): array {
            return [
                "type" => "object",
                "properties" => [
                    "valor" => $valorSchema,
                    "status" => [
                        "type" => "string",
                        "enum" => $statusEnum,
                        "description" => "Use 'encontrado' quando explícito, 'inferido' quando houver forte evidência contextual e 'nao_encontrado' quando ausente."
                    ],
                    "razao_ausencia" => [
                        "type" => ["string", "null"],
                        "description" => "Explique de forma objetiva quando o campo for inferido ou nao_encontrado."
                    ]
                ],
                "required" => ["valor", "status", "razao_ausencia"],
                "additionalProperties" => false
            ];
        };

        $jsonSchema = [
            "type" => "object",
            "properties" => [
                "NOME" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "description" => "Nome oficial do evento."
                ]),
                "CODTIPO_EVENTO" => $buildFieldSchema([
                    "type" => ["integer", "null"],
                    "enum" => $tiposEventoEnum,
                    "description" => "Código do tipo de evento. Opções: " . implode(', ', array_map(fn($t) => $t->CODTIPO_EVENTO . '=' . $t->NOME, $tiposEvento)) . ". Selecione o mais adequado ao esporte descrito no regulamento."
                ]),
                "URL" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "pattern" => "^[a-z0-9]+(?:-[a-z0-9]+)*$",
                    "description" => "Slug em minúsculas, sem acentos, usando apenas letras, números e hífens. Considere a lista de slugs já ocupados e sugira algum não existente: ".$urlUsadas,
                   
                ]),
                "TELEFONE" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "description" => "Telefone de contato no padrão brasileiro (XX) XXXXX-XXXX. caso não esteja claro no PDF, considere esse campo preenchido com o valor padrão do usuario: ".SessionLib::getValue('TELEFONE')
                ]),
                "EMAIL" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "description" => "E-mail principal de contato do evento, caso não esteja claro no PDF, considere esse campo preenchido com o valor padrão do usuario: ".SessionLib::getValue('EMAIL')
                ]),
                "QTD_MAX_INSCRITOS" => $buildFieldSchema([
                    "type" => ["integer", "null"],
                    "description" => "Quantidade máxima de inscritos do evento."
                ]),
                "DATA_EVENTO_DATE" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "pattern" => "^\\d{4}-\\d{2}-\\d{2}$",
                    "description" => "Data do evento no formato YYYY-MM-DD."
                ]),
                "DATA_EVENTO_TIME" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "pattern" => "^\\d{2}:\\d{2}$",
                    "description" => "Horário do evento no formato HH:MM."
                ]),
                "DATA_INSCRICAO_INICIO_DATE" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "pattern" => "^\\d{4}-\\d{2}-\\d{2}$",
                    "description" => "Data de início das inscrições no formato YYYY-MM-DD. Caso não exista, considere a data atual: ".date('Y-m-d')
                ]),
                "DATA_INSCRICAO_INICIO_TIME" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "pattern" => "^\\d{2}:\\d{2}$",
                    "description" => "Horário de início das inscrições no formato HH:MM. Use como padrao |00:00|"
                ]),
                "DATA_INSCRICAO_FIM_DATE" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "pattern" => "^\\d{4}-\\d{2}-\\d{2}$",
                    "description" => "Data final das inscrições no formato YYYY-MM-DD."
                ]),
                "DATA_INSCRICAO_FIM_TIME" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "pattern" => "^\\d{2}:\\d{2}$",
                    "description" => "Horário final das inscrições no formato HH:MM. Use como padrão |23:59|"
                ]),
                "CEP" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "pattern" => "^\\d{5}-?\\d{3}$",
                    "description" => "CEP do local do evento, caso Não encontre considere: |00000-000|"
                ]),
                "LOGRADOURO" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "description" => "Logradouro do evento."
                ]),
                "NUMERO" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "description" => "Número do endereço; se ausente, use s/n somente quando houver evidência de endereço sem número."
                ]),
                "COMPLEMENTO" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "description" => "Complemento do endereço."
                ]),
                "BAIRRO" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "description" => "Bairro onde ocorre o evento, caso não encontre considere: |não informado|"
                ]),
                "ESTADO" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "pattern" => "^[A-Z]{2}$",
                    "description" => "UF do estado em duas letras."
                ]),
                "NOME_CIDADE" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "description" => "Nome da cidade onde ocorre o evento."
                ]),
                "DESCRICAO" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "description" => "preciso que a partir desse documento, crie uma descrição incrível para esse evento, que tenha todas as informações essenciais para o atleta.  faça um texto para eu colar em um editor quill rich text, ou seja, não use emoticons, titulos e etc.
        quero um texto organizado, separado por topicos.
        preciso que fale da premiação, pois é algo que o atleta gosta muito de saber.  a saída deverá ser já pensando no resultado final a ser inserido no input,  utilize tag html <strong style='display:inline !important'>  para destacar partes importantes; 

        Descrição do evento,  use tag html <strong style='display:inline !important'> para realças palavras ou frases importantes para melhorar a leitura.
        Apenas a tag strong é aceita, caso precise construir listas, utilize apenas a quebra de linha.
         A partir do regulemanto,
                                crie um texto cativante para colocar na apresentação dessa corrida na via esporte.
                                organize bem os tópicos, de forma que responda as principais curiosidades dos atletas. exemplo, premiaçao, percurso, valores e etc.
                                O texto deverá ser interessante e voltado para o marketing do evento
                                Não use ícones, nem travessões."
                ]),
                "REGULAMENTO" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "description" => "Resumo das principais regras do evento em HTML limpo usando <p>, <ul>, <li>, <strong>."
                ]),
                "DESCRICAO_KIT" => $buildFieldSchema([
                    "type" => ["string", "null"],
                    "description" => "Descrição do kit e entrega em HTML limpo usando <p>, <ul>, <li>, <strong>."
                ]),
                "TIPO_INSCRICAO" => $buildFieldSchema([
                    "type" => ["integer", "null"],
                    "enum" => [1, 2],
                    "description" => "1 para Gratuita e 2 para Paga. Se não houver evidência clara que o evento será gratuito, opte por 2 ('Paga')."
                ]),
                "RESUMO_ANALISE" => [
                    "type" => "object",
                    "properties" => [
                        "confianca_geral" => [
                            "type" => "string",
                            "enum" => ["alta", "media", "baixa"],
                            "description" => "Avaliação geral da qualidade da extração."
                        ],
                        "campos_faltantes_criticos" => [
                            "type" => "array",
                            "items" => ["type" => "string"],
                            "description" => "Lista de campos críticos sem dados confiáveis."
                        ],
                        "observacoes_gerais" => [
                            "type" => ["string", "null"],
                            "description" => "Observações curtas sobre ambiguidades do documento."
                        ]
                    ],
                    "required" => ["confianca_geral", "campos_faltantes_criticos", "observacoes_gerais"],
                    "additionalProperties" => false
                ]
            ],
            "required" => [
                "TIPO_INSCRICAO",
                "NOME",
                "URL",
                "TELEFONE",
                "EMAIL",
                "QTD_MAX_INSCRITOS",
                "CODTIPO_EVENTO",
                "DATA_EVENTO_DATE",
                "DATA_EVENTO_TIME",
                "DATA_INSCRICAO_INICIO_DATE",
                "DATA_INSCRICAO_INICIO_TIME",
                "DATA_INSCRICAO_FIM_DATE",
                "DATA_INSCRICAO_FIM_TIME",
                "CEP",
                "LOGRADOURO",
                "NUMERO",
                "COMPLEMENTO",
                "BAIRRO",
                "ESTADO",
                "NOME_CIDADE",
                "DESCRICAO",
                "REGULAMENTO",
                "DESCRICAO_KIT",
                "RESUMO_ANALISE"
            ],
            "additionalProperties" => false
        ];

        $dataAtual = date('Y-m-d');
        $systemPrompt = "Você é um assistente especialista em extração estruturada de regulamentos esportivos.
                Sua saída deve ser estritamente um JSON compatível com o schema fornecido.

                Política de extração (conservadora):
                1. Use 'encontrado' apenas quando o dado estiver explícito no texto.
                2. Use 'inferido' somente quando houver forte evidência contextual no documento.
                3. Se não houver base suficiente, use valor null, status 'nao_encontrado' e explique objetivamente em razao_ausencia.
                4. Nunca invente dados externos e não faça buscas fora do texto enviado.
                5. Para CEP, telefone e e-mail, só preencha quando houver evidência textual.

                Regras de data e hora:
                - Data atual de referência: {$dataAtual}.
                - Normalize datas para YYYY-MM-DD e horas para HH:MM.
                - Se o texto trouxer dia/mês sem ano, use ano futuro coerente com a data atual.
                - Evento e prazos de inscrição devem ficar no futuro quando houver ambiguidade de ano.
                - Se houver data sem horário para início das inscrições, use 00:00.
                - Se houver data sem horário para fim das inscrições, use 23:59.

                Regras de texto rico:
                - DESCRICAO, REGULAMENTO e DESCRICAO_KIT podem conter somente HTML limpo com: <p>, <ul>, <li>, <strong>, <h5>.
                - Não use scripts, estilos inline, emojis ou markdown.";

        $model_name = "gpt-4o";
        $schemaString = json_encode($jsonSchema, JSON_UNESCAPED_UNICODE);

        $userPrompt = "Analise o texto abaixo e preencha o JSON seguindo exatamente o schema informado.\n\n" .
            "TEXTO DE ENTRADA:\n{$textoLimpo}\n\n" .
            "SCHEMA ALVO:\n{$schemaString}\n";

        $payload = [
            "model" =>  $model_name,
            "messages" => [
                [
                    "role" => "system",
                    "content" => $systemPrompt
                ],
                [
                    "role" => "user",
                    "content" => $userPrompt
                ]
            ],
            "temperature" => 0.1,
            "max_tokens" => 5000,
            'store' => true,
            'metadata' => [
                'codpessoa' => SessionLib::getValue('CODPESSOA'),
                'rota' => $_SERVER['REQUEST_URI'],
                'session_id' => session_id()
            ],
            "response_format" => ["type" => "json_object"]
        ];

        $response = $this->openAi->response($payload);
        return json_decode($response->choices[0]->message->content, true);
    }


    public function processarLotes($textoPDF)
    {
        $textoLimpo = preg_replace("/\s+/", " ", $textoPDF);
        $textoLimpo = trim($textoLimpo);


        $jsonSchema = [
            'type' => 'object',
            'properties' => [
                'lotes' => [
                    'type' => 'array',
                    'description' => 'Lista contendo todos os lotes de inscrição encontrados no regulamento.',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'Descricao' => [
                                'type' => 'string',
                                'description' => "Nome identificador do lote (ex: '1º Lote', 'Lote Promocional')."
                            ],
                            'DATAINICIO' => [
                                'type' => 'string',
                                'description' => "Data e hora de início no formato ISO 8601 'YYYY-MM-DD 00:00:00'. O horário padrão é 00:00:00 se não especificado."
                            ],
                            'DATAFIM' => [
                                'type' => 'string',
                                'description' => "Data e hora de término no formato ISO 8601 'YYYY-MM-DD 23:59:59'. O horário padrão é 23:59:59 se não especificado."
                            ],
                            'QTD_MAX' => [
                                // Note que em PHP usamos um array para múltiplos tipos
                                'type' => ['integer', 'null'],
                                'description' => "Quantidade máxima de inscrições permitidas neste lote. Se o texto mencionar 'a definir', 'ilimitado' ou apenas 'mínimo', deve retornar null."
                            ]
                        ],
                        // Campos obrigatórios dentro de cada lote
                        'required' => [
                            'Descricao',
                            'DATAINICIO',
                            'DATAFIM',
                            'QTD_MAX'
                        ]
                    ]
                ]
            ],
            // Campo obrigatório na raiz (o array de lotes)
            'required' => [
                'lotes'
            ]
        ];


        $systemPrompt = '# Contexto
        Você é um especialista em mineração de dados de documentos legais e regulamentos. Sua tarefa é ler o arquivo anexo e estruturar os dados de "Lotes de Inscrição" para um banco de dados SQL.

        # Instruções de Extração
        1. Localize a seção que define os prazos e regras dos lotes.
        2. Extraia APENAS os dados solicitados no Schema abaixo.
        3. Se houver divergência de preços (Adulto/Infantil) no mesmo período, considere como um único registro de lote, pois o prazo é o mesmo.
        4. Se a soma dos lotes existentes no arquivo não atingir o número de inscrições esperadas, deverá criar mais um lote com os ingressos restantes, caso o total de lotes atinja o numero maximo de inscrições, não precisar criar esse lote adicional.
        5. Os lotes com data proximas devem divergir em um minuto, exemplo, o primeiro lote encerra dia 10 as 23:59, o proximo lote entao deve comecar dia 11 as 00:00.

        # Regras de Formatação (RÍGIDAS)
        - **Descricao**: String com o nome do lote (ex: "1º LOTE").
        - **DATAINICIO**: Converta para formato ISO DateTime `YYYY-MM-DD 00:00:00`, se não houver menção ao horário utilize como padrao 00:00:00.
        - **DATAFIM**: Converta para formato ISO DateTime `YYYY-MM-DD 23:59:59`, se não houver menção ao horário, utilize como padrao 23:59:59.
        - **QTD_MAX**: Inteiro.
            - Se o texto disser "limitado a X inscritos", use X.
            - Se o texto disser "a definir" ou citar apenas "mínimo", retorne `null` (sem aspas).
            - Se houver um limite técnico global (ex: limite total da prova), NÃO use esse número nos lotes individuais, a menos que o texto especifique a divisão.';

        $model_name = "gpt-4o";

        $schemaString = json_encode($jsonSchema, JSON_UNESCAPED_UNICODE);

        $userPrompt = "Analise o texto fornecido abaixo e preencha o JSON Schema alvo.\n\n" .
            "TEXTO DE ENTRADA:\n" .
            $textoLimpo . "\n\n" . // Importante: O texto vem antes para ele ler primeiro
            "SCHEMA ALVO:\n" .
            $schemaString . "\n\n";



        // 3. Payload da Requisição
        $payload = [
            "model" => $model_name, // Use o modelo correto (gpt-4o-mini é excelente custo-benefício)
            "messages" => [
                [
                    "role" => "system",
                    "content" => $systemPrompt
                ],
                [
                    "role" => "user",
                    "content" => $userPrompt
                ]
            ],
            "temperature" => 0.1,
            "max_tokens" => 6000,
            'store' => true,
            'metadata' => [
                'codpessoa' => SessionLib::getValue('CODPESSOA'),
                'rota' => $_SERVER['REQUEST_URI'],
                'session_id' => session_id()
            ],
            "response_format" => ["type" => "json_object"]
        ];



        $response = $this->openAi->response($payload);


        return json_decode($response->choices[0]->message->content, true);
    }

    public function processarDescricao($textoPDF){
        $modelname = 'gpt-4o-mini';

        $instrucoesDoPrompt = " preciso que a partir desse documento, crie uma descrição incrível para esse evento, que tenha todas as informações essenciais para o atleta.  faça um texto para eu colar em um editor quill rich text, ou seja, não use emoticons, titulos e etc.
        quero um texto organizado, separado por topicos.
        preciso que fale da premiação, pois é algo que o atleta gosta muito de saber.  a saída deverá ser já pensando no resultado final a ser inserido no input,  utilize tag html <strong style='display:inline !important'>  para destacar partes importantes; 

        Descrição do evento,  use tag html <strong style='display:inline !important'> para realças palavras ou frases importantes para melhorar a leitura.
        Apenas a tag strong é aceita, caso precise construir listas, utilize apenas a quebra de linha.
         A partir do regulemanto,
                                crie um texto cativante para colocar na apresentação dessa corrida na via esporte.
                                organize bem os tópicos, de forma que responda as principais curiosidades dos atletas. exemplo, premiaçao, percurso, valores e etc.
                                O texto deverá ser interessante e voltado para o marketing do evento
                                Não use ícones, nem travessões.
   
";
       

        $payload = [
            'model' => $modelname,
            
            'messages' => [
                [
                    "role" => "system",
                    "content" => $instrucoesDoPrompt
                ],
                [
                    "role" => "user",
                    "content" => "\n\n" . $textoPDF
                ]
            ],
            
            'store' => true,
            
            'metadata' => [
                'codpessoa' => SessionLib::getValue('CODPESSOA'),
                'rota' => $_SERVER['REQUEST_URI'],
                'session_id' => session_id(),
                'prompt_referencia' => 'pmpt_6939cac041b8819080fc7c05bd5a8d8d0d714d78df021af6' 
            ],
            

            "max_completion_tokens" => 12000
        ];

        $response = $this->openAi->response($payload);
        return $response->choices[0]->message->content;

    }

     public function processarModalidades($textoPDF)
    {
        $textoLimpo = preg_replace("/\s+/", " ", $textoPDF);
        $textoLimpo = trim($textoLimpo);


        


        $systemPrompt = '# Contexto
                Você é um assistente especializado em extração de dados de regulamentos esportivos para alimentação de banco de dados via API.

                # Tarefa
                Analise o texto do regulamento fornecido e extraia as modalidades do evento.
                Retorne APENAS um objeto JSON contendo um array de objetos chamado `modalidades`.

                # Regras de Consolidação (IMPORTANTE)
                1. **Corridinha Infantil:** Se houver várias distâncias curtas para crianças (ex: 200m, 300m, etc.) separadas apenas por idade, **NÃO** crie um item para cada uma. Crie um ÚNICO item consolidado (Ex: "Corridinha Infantil").
                - Para o campo `DISTANCIA_KM` neste caso, use a **maior** distância encontrada no grupo (convertida para Km).
                - Para `IDADE_MINIMA` e `IDADE_MAXIMA`, use a menor e a maior idade de todo o grupo infantil.

                # Definição dos Campos (Schema)

                Para cada modalidade encontrada, preencha os campos seguindo estas regras estritas:

                - **NOME** (String): Nome da modalidade (Ex: "Corrida 5K", "Caminhada 3K", "Corridinha Infantil").
                - **TIPO_INSCRICAO** (Int):
                    - 1 = Gratuita (Procure termos como "isento", "grátis", "doação de alimentos" sem taxa).
                    - 2 = Paga (DEFAULT - Assuma 2 a menos que esteja explicitamente escrito que é gratuita).
                - **QTD_MAX** (Int | Null): Quantidade máxima de inscritos para aquela modalidade específica. Se o regulamento der um número geral (ex: "500 para todas as categorias infantis"), aplique esse número à modalidade consolidada.
                - **DISTANCIA_KM** (Float): Distância em Quilômetros (Ex: 5.0, 10.0, 0.2). Se o texto estiver em metros, converta (ex: 500m = 0.5).
                - **IDADE_MINIMA** (Int): Idade mínima permitida. Se não informado, retorne null.
                - **IDADE_MAXIMA** (Int): Idade máxima permitida. Se for "Livre" ou até o fim da vida, retorne 99.
                - **TIPO_GENERO** (String): "M" (Masculino), "F" (Feminino), "T" (Todos/Misto).
                - **DESCRICAO_PERCURSO** (String): Detalhes do trajeto se houver. Para a categoria infantil consolidada, liste as variações aqui (Ex: "Distâncias de 200m a 1km dependendo da idade").
                - **QUEM_PAGA_TAXA** (Int):
                    - 1 = Organizador (Taxa de serviço absorvida).
                    - 2 = Atleta (Taxa de serviço cobrada do atleta).
                    - DEFAULT = 2 (Assuma que o atleta paga a taxa do site).
                - **CORTESIA_POS_INSCRICAO** (Int):
                    - 1 = Sim (Gera cortesia automática).
                    - 0 = Não.
                    - DEFAULT = 0.

                # Formato de Saída (JSON Puro)
                ```json
                {
                "modalidades": [
                    {
                    "NOME": "string",
                    "TIPO_INSCRICAO": 2,
                    "QTD_MAX": 0,
                    "DISTANCIA_KM": 0.0,
                    "IDADE_MINIMA": 0,
                    "IDADE_MAXIMA": 99,
                    "TIPO_GENERO": "T",
                    "DESCRICAO_PERCURSO": "string",
                    "QUEM_PAGA_TAXA": 2,
                    "CORTESIA_POS_INSCRICAO": 0
                    }
                ]
                }';

        $model_name = "gpt-4o-mini";


        $userPrompt = "Analise o texto fornecido abaixo e preencha o JSON Schema alvo.\n\n" .
            "TEXTO DE ENTRADA:\n" .
            $textoLimpo . "\n\n" ;



        // 3. Payload da Requisição
        $payload = [
            "model" => $model_name, // Use o modelo correto (gpt-4o-mini é excelente custo-benefício)
            "messages" => [
                [
                    "role" => "system",
                    "content" => $systemPrompt
                ],
                [
                    "role" => "user",
                    "content" => $userPrompt
                ]
            ],
            "temperature" => 0.1,
            "max_tokens" => 6000,
            'store' => true,
            'metadata' => [
                'codpessoa' => SessionLib::getValue('CODPESSOA'),
                'rota' => $_SERVER['REQUEST_URI'],
                'session_id' => session_id()
            ],
            "response_format" => ["type" => "json_object"]
        ];



        $response = $this->openAi->response($payload);


        return json_decode($response->choices[0]->message->content, true);
    }
}
