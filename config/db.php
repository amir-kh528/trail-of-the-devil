<?php

declare(strict_types=1);

function getDatabaseConfig(): array
{
    $environment = getenv('APP_ENV') ?: 'development';
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_NAME') ?: 'test_project_db';
    $dbUser = getenv('DB_USER');
    $dbPass = getenv('DB_PASS');

    if ($environment === 'production') {
        if (getenv('DB_HOST') === false || getenv('DB_NAME') === false
            || $dbUser === false || $dbUser === '' || $dbUser === 'root'
            || $dbPass === false || $dbPass === '') {
            throw new RuntimeException('Production database credentials are not configured safely.');
        }
    } else {
        $dbUser = $dbUser === false ? 'root' : $dbUser;
        $dbPass = $dbPass === false ? '' : $dbPass;
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbName)) {
        throw new RuntimeException('Database name contains unsupported characters.');
    }

    return [
        'environment' => $environment,
        'host' => $host,
        'name' => $dbName,
        'user' => $dbUser,
        'password' => $dbPass,
    ];
}

function getDb(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = getDatabaseConfig();
    $pdo = new PDO(
        'mysql:host=' . $config['host'] . ';dbname=' . $config['name'] . ';charset=utf8mb4',
        $config['user'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    return $pdo;
}