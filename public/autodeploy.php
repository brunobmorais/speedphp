<?php
/**
 * Deploy automático: https://dominio.com/autodeploy.php?flag=SEU_TOKEN
 *
 * O token fica em config/productionConfig.php (não versionado):
 * const CONFIG_DEPLOY_TOKEN = 'token-aleatorio-com-pelo-menos-32-caracteres';
 */

$configFile = dirname(__DIR__) . '/config/productionConfig.php';
if (is_file($configFile)) {
    require_once $configFile;
}

$token = defined('CONFIG_DEPLOY_TOKEN') ? (string) CONFIG_DEPLOY_TOKEN : '';
$flag = (string) ($_GET['flag'] ?? '');

if (strlen($token) < 32 || !hash_equals($token, $flag)) {
    http_response_code(404);
    exit;
}

/**
 * &1 means value reference to file descriptor 1 stdout
 * 2>&1 saying Redirect the stderr to the same place we are redirecting the stdout
 */
$output = shell_exec('git reset --hard && git pull origin main 2>&1');

echo "<pre>" . htmlspecialchars((string) $output) . "</pre>";
