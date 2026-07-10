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
