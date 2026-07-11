# SpeedPHP

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](composer.json)
[![Demo](https://img.shields.io/badge/demo-online-brightgreen.svg)](https://speedphp.bmorais.com)

> Framework PHP para desenvolvimento rápido e descomplicado

![SpeedPHP Screenshot](https://github.com/brunobmorais/speedphp/blob/main/public/assets/img/print/desktop1.png?raw=true)

SpeedPHP é um framework PHP MVC leve, com baixa curva de aprendizado, pensado para quem quer sair do zero ao funcionando sem a complexidade de frameworks maiores. Vem com bibliotecas prontas para as necessidades mais comuns do dia a dia: autenticação JWT, envio de e-mail, geração de PDF, integração com Pix e Mercado Pago, OpenAI, e muito mais.

---

## Demonstração

- **URL:** [https://speedphp.bmorais.com](https://speedphp.bmorais.com)
- **Login:** `000.000.000-00`
- **Senha:** `123@@123`

---

## Requisitos

- **PHP** >= 8.3
- **Extensões PHP:** `pdo`, `json`, `curl`, `gd`, `openssl`, `exif`, `fileinfo`, `intl`
- **Composer**
- **Docker** (recomendado para ambiente de desenvolvimento)

---

## Instalação

### Com Docker (recomendado)

1. Clone o repositório:
   ```bash
   git clone https://github.com/brunobmorais/speedphp.git
   cd speedphp
   ```

2. Copie o arquivo de configuração de desenvolvimento:
   ```bash
   cp config/developerConfig.example.php config/developerConfig.php
   ```

3. Edite o arquivo `config/developerConfig.php` com as informações do seu banco de dados e ambiente.

4. Instale as dependências:
   ```bash
   composer install
   ```

5. Suba os containers Docker:
   ```bash
   docker-compose up -d
   # ou
   make up
   ```

6. Acesse no navegador:
   - Aplicação: [http://speedphp.localhost](http://speedphp.localhost)
   - phpMyAdmin: [http://phpmyadmin.localhost:8080](http://phpmyadmin.localhost:8080)

### Sem Docker (servidor embutido PHP)

```bash
php -S localhost:8080 -t ./public
```

Acesse em [http://localhost:8080](http://localhost:8080).

---

## Como Funciona

### Roteamento Web (MVC)

```
https://seudominio.com/{controller}/{metodo}/{param1}/{param2}
```

Todo o tráfego passa pelo `public/index.php`. O framework faz autoload dos controllers em `src/Controllers/` e chama o método correspondente à URL. O método padrão (quando não especificado) é `index()`.

**Exemplo:**
- `https://seusite.com/home` → `HomeController::index()`
- `https://seusite.com/usuario/perfil/42` → `UsuarioController::perfil(42)`

### Roteamento API REST

```
https://seudominio.com/api/{rota}
```

As rotas da API são definidas em `src/Api/Routers/`.

### Exemplo de Controller

```php
<?php
namespace App\Controllers;

use App\Core\Controller\ControllerCore;

class HomeController extends ControllerCore
{
    public function index(): void
    {
        $this->view('home/index', ['titulo' => 'SpeedPHP']);
    }

    public function detalhe(int $id): void
    {
        $this->view('home/detalhe', ['id' => $id]);
    }
}
```

### Exemplo de View (Twig)

```twig
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ titulo }}</title>
</head>
<body>
    <h1>{{ titulo }}</h1>
</body>
</html>
```

As views ficam em `templates/` e usam a engine **Twig 3**.

---

## Estrutura de Arquivos

```
speedphp/
├── config/
│   ├── config.php                        # Configurações globais da aplicação
│   ├── developerConfig.php               # Configurações do ambiente local (não versionado)
│   └── developerConfig.example.php       # Modelo para configuração local
├── docker/                               # Configurações e scripts Docker
├── public/
│   ├── index.php                         # Entry point da aplicação
│   └── assets/
│       ├── css/style.css                 # CSS personalizado
│       ├── js/                           # JavaScript do projeto
│       ├── img/                          # Imagens estáticas
│       ├── plugin/                       # Plugins de terceiros
│       └── upload/                       # Arquivos enviados pelos usuários
├── src/
│   ├── Api/
│   │   ├── Controllers/                  # Controllers da API REST
│   │   ├── Lib/                          # Libs exclusivas da API
│   │   └── Routers/                      # Definição das rotas da API
│   ├── Controllers/                      # Controllers da aplicação web (MVC)
│   ├── Core/
│   │   ├── AppCore.php                   # Roteador principal (parse da URL)
│   │   └── Controller/
│   │       └── ControllerCore.php        # Classe base dos controllers
│   ├── Daos/                             # Camada de acesso ao banco de dados
│   ├── Enums/                            # Enumerações PHP
│   ├── Libs/                             # Bibliotecas utilitárias (ver seção abaixo)
│   ├── Models/                           # Entidades que representam tabelas do banco
│   └── Modules/                          # Módulos da aplicação (controllers agrupados)
├── templates/                            # Views Twig
├── docker-compose.yml
├── Makefile                              # Comandos de automação do projeto
├── manifest.json                         # Configuração do PWA
└── sw.js                                 # Service Worker para PWA
```

### Descrição dos diretórios principais

| Diretório | Responsabilidade |
|-----------|-----------------|
| `src/Controllers/` | Recebe requisições, orquestra resposta, chama DAOs e views |
| `src/Models/` | Entidades com getters/setters que mapeiam tabelas do banco |
| `src/Daos/` | Toda a lógica de interação com o banco de dados |
| `src/Libs/` | Bibliotecas prontas para uso (JWT, Email, PDF, etc.) |
| `src/Api/` | Rotas e controllers da API REST |
| `src/Core/` | Núcleo do framework: roteamento e classe base dos controllers |
| `src/Modules/` | Controllers organizados por módulo/domínio |
| `templates/` | Views Twig da aplicação |
| `config/` | Configurações de ambiente e da aplicação |

---

## Bibliotecas Disponíveis

Todas as bibliotecas ficam em `src/Libs/` e podem ser usadas diretamente nos controllers.

| Biblioteca | Descrição |
|------------|-----------|
| `JwtLib` | Geração e validação de tokens JWT (`firebase/php-jwt`) |
| `EmailLib` | Envio de e-mails via PHPMailer |
| `TemplateEmailLib` | Templates HTML para e-mails |
| `SessionLib` | Gerenciamento de sessões PHP |
| `CookieLib` | Manipulação de cookies |
| `FileLib` | Upload, validação e manipulação de arquivos |
| `DownloadLib` | Download forçado de arquivos |
| `Tcpdf/` | Geração de documentos PDF (`tecnickcom/tcpdf`) |
| `PlanilhaLib` | Exportação para Excel via PhpSpreadsheet |
| `PixLib` | Geração de QR Code e integração com Pix |
| `MercadoPagoLib` | Integração com a API do Mercado Pago |
| `OpenAiApi` | Integração com a API da OpenAI |
| `HttpLib` | Requisições HTTP com cURL |
| `LogLib` | Logging de eventos da aplicação |
| `CacheLib` | Cache de dados em arquivo |
| `AlertLib` | Sistema de alertas e flash messages |
| `CryptTextLib` | Criptografia e descriptografia de texto |
| `TimeLib` | Manipulação e formatação de datas e horários |
| `TableLib` | Renderização dinâmica de tabelas HTML |
| `ICalLib` | Geração de arquivos de calendário iCal |
| `LoginLimit` | Limitador de tentativas de login (brute-force protection) |
| `NotificacaoLib` | Notificações internas da aplicação |
| `PushNotification/` | Push notifications para PWA |
| `StravaLib` | Integração com a API do Strava |
| `UuidClass` | Geração de UUIDs (`ramsey/uuid`) |
| `LocalStorageClass` | Interação com localStorage do navegador |
| `Form/` | Helpers para criação e validação de formulários |
| `FuncoesLib` | Funções utilitárias gerais |

---

## Comandos

### Makefile

Execute `make help` para ver todos os comandos disponíveis.

| Comando | Descrição |
|---------|-----------|
| `make up` | Para todos os containers e sobe com `--force-recreate` |
| `make docker` | Menu interativo para gerenciar containers Docker |
| `make start` | Menu interativo completo: Docker, config, composer, backup, uploads |
| `make update` | Executa `composer update` dentro do container PHP |
| `make push MSG="mensagem"` | Commit + pull --rebase + push para o repositório |
| `make createmodel` | Cria Model e Dao a partir do nome de uma tabela do banco |
| `make createcontroller` | Cria Controller e template dentro de `src/Modules` |
| `make build` | Minifica JS e CSS do projeto |
| `make backup` | Executa backup/import do banco de dados de produção |
| `make download` | Baixa arquivos de uploads do servidor remoto |

### Geração de arquivos via navegador

```
# Criar controller e template
http://localhost/config/createcontroller/NOME_CONTROLLER/NOME_SERVICO

# Criar model e dao
http://localhost/config/createmodel/NOME_TABELA_BANCO

# Build (minificação de JS e CSS)
http://localhost/config/build
```

### Otimizar autoloader para produção

```bash
composer dump-autoload --optimize
```

---

## Configuração

### Ambiente de desenvolvimento local

Renomeie o arquivo de exemplo e edite com as informações do seu servidor:

```bash
cp config/developerConfig.example.php config/developerConfig.php
```

### Banco de dados — phpMyAdmin

| Campo | Valor |
|-------|-------|
| URL | http://phpmyadmin.localhost:8080/ |
| Usuário | `user` |
| Senha | `user` |
| Root | `root` / `root` |

### XDebug com PHPStorm

1. Em **Settings → PHP → Servers**, adicione o servidor com a URL do projeto
2. Marque **Use path mappings**
3. Configure o **Absolute path on the server** como `/var/www/html`

---

## PWA (Progressive Web App)

O SpeedPHP tem suporte nativo a PWA:

- `manifest.json` — configuração do app (nome, ícones, cores)
- `sw.js` — Service Worker para funcionamento offline

---

## Front-end

O framework utiliza [Bootstrap 5.3](https://getbootstrap.com/) como base de CSS.

Para gerar um favicon: [https://www.favicon-generator.org/](https://www.favicon-generator.org/)

---

## Pacotes Externos

| Pacote | Descrição | Link |
|--------|-----------|------|
| `robmorgan/phinx` | Migrations e Seeds para banco de dados | [phinx.org](https://phinx.org/) |
| `bmorais/database` | Conexão PDO ao banco de dados | [GitHub](https://github.com/brunobmorais/php-database) |
| `phpmailer/phpmailer` | Envio de e-mails | [GitHub](https://github.com/PHPMailer/PHPMailer) |
| `firebase/php-jwt` | Geração e validação de tokens JWT | [GitHub](https://github.com/firebase/php-jwt) |
| `twig/twig` | Engine de templates | [twig.symfony.com](https://twig.symfony.com/) |
| `tecnickcom/tcpdf` | Geração de PDFs | [tcpdf.org](https://tcpdf.org/) |
| `phpoffice/phpspreadsheet` | Leitura e escrita de planilhas Excel | [GitHub](https://github.com/PHPOffice/PhpSpreadsheet) |
| `ramsey/uuid` | Geração de UUIDs | [GitHub](https://github.com/ramsey/uuid) |
| `mpdf/qrcode` | Geração de QR Codes | [Packagist](https://packagist.org/packages/mpdf/qrcode) |
| `spatie/ignition` | Debug e tratamento de erros (dev) | [GitHub](https://github.com/spatie/ignition) |

---

## Autores

- **Bruno Morais** — [github.com/brunobmorais](https://github.com/brunobmorais)
- **Athus Felipe** — [github.com/AthusFelipe](https://github.com/AthusFelipe)

---

## Licença

Este projeto está licenciado sob a [MIT License](LICENSE).
