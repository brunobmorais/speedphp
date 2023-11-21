<?php
if (strpos($_SERVER['SERVER_NAME'],"localhost") || $_SERVER['SERVER_NAME'] == "localhost"){
    require_once(dirname(__DIR__,1).'/config/developerConfig.php');
} else {
    require_once(dirname(__DIR__,1).'/config/productionConfig.php');

}
/*ALTERE ESSA VARIAVEL TODA VEZ QUE QUISER ATUALIZAR O CSS E JAVASCRIPT*/
const CONFIG_VERSION_CODE = "1.0.0";

const CONFIG_MAINTENANCE = false;

const CONFIG_SECURITY = [
    "domain" => 'seudominio.com',
    "token" => 'suaChavetoken',
    "permission_domains" => ['dev.seudominio.com', 'seudominio.com', 'www.seudominio.com']
];

const CONFIG_SITE = [
    "color-primary" => "#035E96",
    "color-primary-hover" => "#024670",
    "color-secondary" => "#676767",
    "name" => "App",
    "nameFull" => "Nome Site Completo",
    "email" => "naoresponda@framework.bmorais.com",
    "phone" => "+55 63 0000-00000",
    "url" => "https://framework.bmorais.com",
    "domain" => "https://framework.bmorais.com",
    "andress" => "Cidade-ESTADO",
    "cnpj" => ""
];

const CONFIG_DEVELOPER = [
    "name" => "bmorais.com",
    "nameFull" => "bmorais.com",
    "email" => "emaildesenvolvedor@dominio.com",
    "url" => "https://bmorais.com"
];

// CONFIGURAÇÃO HEADER
const CONFIG_HEADER = [
    "author" => 'bmorais.com',
    "title" => 'Framework',
    "description" => 'Descricao completa do site',
    "image" => 'https://framework.bmorais.com/assets/img/ic_logosocial.png',
    "keywords" => "palavras, chaves, site",
    "color" => CONFIG_SITE['color-primary'],
    "fbAppId" => "0"
];

// CONFIGURAÇÃO EMAIL
const CONFIG_EMAIL = [
    "host" => 'smtp.gmail.com',
    "userName" => "BM Tecnlogia",
    "password" => '',
    "port" => '465',
    "smtpAuth" => true,
    "smtpSecure" => 'ssl',
    "from" => "contato@bmorais.com",
    "reply" => "contato@bmorais.com"
];

// CONFIGURAÇÃO HEADER
// URL: CHAVES https://www.google.com/u/1/recaptcha/admin
// EMAIL: email@gmail.com
const CONFIG_RECAPTCHA = [
    "chaveSite" => 'chavesite',
    "chaveSecreta" => 'chavesecreta',
];

const CONFIG_KEY_API_GOOGLE = "ChaveKeyGoolge";