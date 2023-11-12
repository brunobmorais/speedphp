# SpeedPHP
![print1](https://github.com/brunobmorais/php-mvc-framework/blob/master/public/assets/img/print/img1.png?raw=true)

SpeedPHP: The Rapid Development Framework for PHP

## DEMONSTRAÇÃO
<a href="https://framework.bmorais.com" target="_blank">https://speedphp.bmorais.com</a>
login: 000.000.000-00
senha: 123456

## COMO FUNCIONA O FRAMEWORK?
https://seudominio.com/controller/metodo-controle/parametro1/parametro2/param....

## COMO FUNCIONA A API?
https://seudominio.com/api/nome_da_rota

Todo tráfego passo pelo index principal, a aplicação faz um autoloader dentro da pasta Application e chama o controler específico com o método index, a qual é o método padrão, caso a pessoa não especifique.

## ESTRUTURA

    FRAMEWORK
    ├── config/
    ├── docker/
    ├── public/
    │   ├── index.php
    │   └── assets/
    │       ├── css/
    │       │   └── style.css -> Arquivo de personalização do CSS
    │       ├── img/
    │       │   ├── ic_logonavegador.png
    │       │   ├── logo-branco.png
    │       │   └── logo.png
    │       ├── js/
    │       │   └── login.js -> Funções globais javascript
    │       ├── plugin/
    │       └── upload/   
    ├── src/
    │   ├── Api/
    |        ├── Controller/ 
    |        ├── Lib/ 
    │        └── Routers/
    │   ├── Components/
    │   ├── Controllers/
    │   │   └── HomeController.php
    │   ├── Core/
    │   │   ├── App.php
    │   │   ├── Controller.php
    │   │   ├── Page.php
    │   │   └── View.php
    │   ├── Dao/
    │   │   ├──
    │   │   └── UsuarioDao.php
    │   ├── Lib/
    │   │   └── AlertaLib.php
    │   │   └── CookieLib.php
    │   │   └── FuncoesLib.php
    │   │   └── PushNotificationClass.php
    │   │   └── SessionLib.php
    │   │   └── VariavelClass.php
    │   └── Models/
    │       └── UsuarioModel.php
    │
    ├── template/
    │
    ├── .htaccess
    ├── index.php
    ├── manifest.json -> Arquivo de configuração do PWA
    └── sw.js -> Service Worke para funcionamento do PWA
    
    #config/ → 
    Pasta de configurações e de informações do site

    #controllers/ → 
    Este diretório armazenará todos os controladores da aplicação que recebe os dados informados pelo usuário e decide o que fazer com eles e cada método deve realizar uma ação ou chamar view. Além disso, toda classe criada herda os métodos da classe Controller do arquivo armazenado em Application/core/Controller.php que será discutido em breve.
    
    #core/ → 
    Neste diretório será armazenado três arquivos: App.php que é responsavel por tratar a URL decidindo qual controlador e qual método deve ser executado; Controller.php responsável por chamar o model, view e pageNotFound que são herdados para as classes no diretório Application/controllers; E por último, o arquivo Database.php que armazena a conexão com o banco de dados.
    
    #models/ → 
    Aqui fica a entidade que corresponde a tabela do banco com os campos GET e SET de cada campo.
    
    #dao/  →  
    Aqui fica a lógica das suas entidades, no nosso caso usaremos classes que irá interagir com o banco de dados e fornecer tais dados para o controle a qual passará para view.

    views/ → 
    As views serão responsável por interagir com o usuário. Uma das suas principais características é que cada view sabe como exibir um model.
    
    #.htaccess → 
    Neste arquivo, apenas negaremos a navegação no diretório com a opção Options -Indexes.

## GERAR FAVICON
https://www.favicon-generator.org/

## TESTAR
    Configure php como variavel de ambiente e execute o comando abaixo
        php -S localhost:8080 -t ./public

## GERAR ARQUIVOS MINIFICADOS CSS E JS
    Acesse seu site http://seusite.com/config/build

## FRAMEWORK FRONT-END
    https://getbootstrap.com/ -> versão 5.2

## COMANDOS
    Otimizar autoloader para produção
        composer dump-autoload --optimize

## INSTALAÇÃO E FUNCIONAMENTO
    1 - Preencha as informações da pasta config 
    2 - Execute o comando no terminal
        composer update
    3 - Inicie o docker
        docker-compose up
    4 - Acesse localhost ou framework.localhost no seu navegador

## CREDENCIAIS DE ACESSO
    login: 000.000.000-00
    senha: 123456

## ACESSAR BANCO DE DADOS
    Url: http://phpmyadmin.localhost:8080/
    Usuario: user
    Senha: user

    usuario e senha root: root

