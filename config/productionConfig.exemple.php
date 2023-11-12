<?php
// CONFIGURAÇÃO DO BANCO DE DADOS
const CONFIG_DATA_LAYER = [
    "driver" => "mysql",
    "host" => "localhost",
    "port" => "3306",
    "dbname" => "",
    "username" => "",
    "passwd" => '',
    "options" => [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8, lc_time_names = 'pt_BR'",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::MYSQL_ATTR_FOUND_ROWS => true,
        PDO::ATTR_STRINGIFY_FETCHES => true
    ],
    "homologation" => "homologacao",
    "directory_models" => "App\\Models\\",
    "return_error_json" => false,
    "display_errors_details" => false
];

const CONFIG_DISPLAY_ERROR_DETAILS = false;

const CONFIG_URL = "https://seusite.com";

