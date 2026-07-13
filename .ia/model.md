# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Sempre responda em **português do Brasil**.

## Stack

- PHP 8.3 (MVC custom baseado em SpeedPHP)
- MariaDB 8 (Hostinger em prod, Docker local em dev)
- Bootstrap 5.3 + Material Design Icons
- Twig 3.x para templates
- JavaScript vanilla

## Comandos

```bash
make up             # Sobe Docker (MySQL + Mailpit + PhpMyAdmin)
make createmodel    # Cria Model + DAO (pergunta nome da tabela)
make createcontroller  # Cria Controller + template Twig (ex: /sistema/notafiscal)
make build          # Minifica CSS/JS (ou acesse /config/build)
```

## Banco de Dados

```bash
# Query via Docker (sem terminal interativo):
docker exec viaesporte-mysql-1 mysql -u root -proot viaesporte -e "SQL_QUERY"
```

Container: `viaesporte-mysql-1` | DB: `viaesporte` | user: `root` / senha: `root`

> O MCP MySQL (`mcp__mysql-server__mysql_query`) também está disponível para consultas SELECT diretas na conversa.

## Arquitetura de Roteamento

**Fluxo:** `public/index.php` → `AppCore2` → resolve controller/método/params da URL

**Padrão de URL:** `/controller/metodo/param1/param2`

- URLs começando com `/api/` são desviadas para `src/Api/` (REST com `RouterClass`)
- O router procura `src/Controllers/{Nome}Controller.php` pelo primeiro segmento da URL
- Se não encontrar controller, tenta como método do `HomeController`

## Dois Tipos de Controllers

### 1. Controllers principais (`src/Controllers/`)
Controlam áreas inteiras. Implementam `ControllerInterface`. Exemplos: `OrganizadorController`, `SistemaController`, `AtletaController`. Estes arquivos são grandes e contêm muitas ações. Ele foi descontinuado para novos controllers, mas os existentes ainda seguem esse padrão.

### 2. Module controllers (`src/Modules/`)
Controllers focados por funcionalidade. Implementam `ControllerModuleInterface`. Namespace: `App\Modules\{Area}\{Funcionalidade}`. Exemplo: `App\Modules\Organizador\Validacao\ValidacaoController`. Use `make createcontroller` para criá-los.

## Base: ControllerCore

Todos os controllers estendem `ControllerCore`. Métodos principais:

```php
$this->isLogged();                          // Redireciona se não autenticado
$this->checkPermission('LER|SALVAR|EXCLUIR'); // Verifica privilégio
$this->render(TemplateAbstract::LOGGED, 'pasta/template', $data); // Renderiza Twig
$this->postParams('CAMPO');                 // $_POST com htmlspecialchars
$this->getParams('CAMPO');                  // $_GET com htmlspecialchars
$this->filesParams('CAMPO');                // $_FILES
$this->validateRequestMethod('POST');       // Valida método HTTP
$this->getServico();                        // Carrega serviço/módulo (para módulos do sistema)
$this->getServicoColaborador();             // Idem, para colaboradores de eventos
$this->redirect('/url');                    // Redireciona
```

**AlertLib** — redireciona com mensagem flash e interrompe execução:
```php
(new AlertLib)->danger('mensagem', '/url');   // Erro grave
(new AlertLib)->warning('mensagem', '/url');  // Aviso
(new AlertLib)->success('mensagem', '/url');  // Sucesso
```

## Templates

`$this->render(TemplateAbstract::TIPO, 'caminho/template', $data)` renderiza `templates/caminho/template.twig`.

Tipos de layout disponíveis (`TemplateAbstract`):
- `LOGGED` — área logada padrão (sistema/organizador)
- `NOT_LOGGED` — páginas públicas
- `ATLETA` — área do atleta
- `BLANK`, `BLANK_ORGANIZADOR`, `CLEAN_ORGANIZADOR` — layouts limpos

## DAOs

Todos estendem `BMorais\Database\CrudBuilder`. PDO retorna objetos (`FETCH_OBJ`), acesse como `$obj->CAMPO`.

```php
class ExemploDao extends Crud {
    public function __construct() {
        $this->setTableName("NOME_TABELA");
        $this->setClassModel("exemploModel");
    }
}
```

## API REST (`src/Api/`)

Rotas definidas em `src/Api/Routers/`. Usa `RouterClass` com suporte a middlewares:
- `BearerAuthMiddleware` — autenticação JWT
- `OriginMiddleware` — controle de origem
- `MaintenanceMiddleware` — modo manutenção

## Autenticação e Sessão

- `SessionLib::getValue('CODUSUARIO')` / `SessionLib::setValue('CHAVE', valor)`
- `CookieLib::getValue('CODEVENTO')` — fallback de sessão para cookies
- `SessionLib::getValue('CODEVENTO')` — evento selecionado no painel do organizador

## Configurações de Ambiente

- **Dev:** `config/developerConfig.php`
- **Prod:** `config/productionConfig.php`
- Detecção automática por `$_SERVER['SERVER_NAME']`

## Integrações Externas

- **Pagamentos:** MercadoPago (`src/Libs/MercadoPagoLib.php`)
- **Auth social:** Google OAuth via HybridAuth
- **Email:** PHPMailer (Mailpit em dev)
- **AI:** OpenAI GPT client
- **PDF:** TCPDF + FPDI (`src/Libs/Tcpdf/`)
- **Planilhas:** PhpSpreadsheet (`src/Libs/PlanilhaLib.php`)
- **Push:** Firebase (`src/Libs/PushNotification/`)

## Regras Gerais

- Quando o usuário corrigir sua abordagem ou disser que algo está errado, NÃO repita o mesmo erro. Releia a correção com atenção antes de continuar. Se tiver dúvida, confirme o entendimento antes de implementar.

## Correção de Bugs

Sempre analise o contexto completo (impactos proporcionais, registros relacionados, casos extremos) antes de propor uma solução. Faça perguntas de esclarecimento sobre regras de negócio em vez de assumir.

## Convenções de UI

Elementos de UI devem usar elementos HTML de exibição padrão (`<p>`, `<span>`) para dados somente leitura — não campos de input. Ao exibir endereço ou informações de múltiplas partes, combine em uma única linha/campo, salvo instrução contrária.

### Modais

Toda modal deve ter o atributo `data-hash` com o valor começando por `#` seguido do nome/ID da modal (ex.: `<div class="modal fade" id="modalExemplo" data-hash="#modalExemplo" ...>`).

## MCP MySQL

MCP MySQL está configurado neste projeto (`mcp__mcp_server_mysql__mysql_query`). Se a conexão falhar, verifique as credenciais em `.claude/settings.json` antes de tentar alternativas como Docker exec.

Add as a new ## Deployment section near the top of CLAUDE.md\n\n## Deployment
- 'subir producao' or 'deploy' means: run tests, commit, AND deploy to the production server (not just git push). Always complete the full deployment pipeline.
  Add as a new ## Communication section in CLAUDE.md\n\n## Communication
- When the user corrects you, re-read their message carefully before attempting another fix. Do NOT assume you understood — restate the requirement back to the user first.
- Pay attention to Portuguese instructions: 'subir producao' = deploy to production, 'nota fiscal' = invoice.
  Add as a new ## Database / MCP MySQL section in CLAUDE.md\n\n## Database / MCP MySQL
- MCP MySQL config location: check both project and global settings files.
- Required config fields: host, port, user, password (MYSQL_PASS), database (MYSQL_DB). Verify ALL fields before attempting queries.
- If MCP connection fails, check credentials first — do not attempt Docker workarounds.
  Add as a new ## Approach section in CLAUDE.md\n\n## Approach
- Before implementing a fix, validate the approach with the user if the problem has multiple possible interpretations (e.g., rounding behavior, refund logic, sort priority).
- For UI work: use read-only elements (paragraphs, spans) for display data, not input fields. Combine address fields on one line unless told otherwise.
