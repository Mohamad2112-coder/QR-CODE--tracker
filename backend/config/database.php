<?php

declare(strict_types=1);

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$host = getenv('DB_HOST') ?: '127.0.0.1';
$sslCa = getenv('DB_SSL_CA') ?: '';
if ($sslCa === '' && !in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
    throw new RuntimeException('Set DB_SSL_CA to the database provider CA certificate for remote MySQL connections.');
}

if ($sslCa !== '') {
    if (!preg_match('/^(?:[A-Za-z]:[\\\\\/]|\\\\\\\\|\/)/', $sslCa)) {
        $sslCa = dirname(__DIR__) . DIRECTORY_SEPARATOR . $sslCa;
    }

    if (!is_file($sslCa)) {
        throw new RuntimeException('The DB_SSL_CA certificate file was not found.');
    }

    if (!defined('PDO::MYSQL_ATTR_SSL_CA') || !defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
        throw new RuntimeException('The installed PDO MySQL driver does not support verified TLS connections.');
    }

    $options[constant('PDO::MYSQL_ATTR_SSL_CA')] = $sslCa;
    $options[constant('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')] = true;
}

return [
    'driver' => getenv('DB_CONNECTION') ?: 'mysql',
    'host' => $host,
    'port' => (int)(getenv('DB_PORT') ?: 3306),
    'database' => getenv('DB_DATABASE') ?: 'qr_pager_saas',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset'  => 'utf8mb4',
    'options' => $options,
];
