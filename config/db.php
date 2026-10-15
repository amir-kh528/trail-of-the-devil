<?php

declare(strict_types=1);

$host = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'test_project_db';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

function initializeDatabase(): void
{
    global $host, $dbName, $dbUser, $dbPass;

    $rootConnection = new PDO(
        'mysql:host=' . $host . ';charset=utf8mb4',
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $rootConnection->exec(
        'CREATE DATABASE IF NOT EXISTS ' . $dbName . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );

    $databaseConnection = new PDO(
        'mysql:host=' . $host . ';dbname=' . $dbName . ';charset=utf8mb4',
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $databaseConnection->exec(
        'CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    try {
        $databaseConnection->exec(
            'ALTER TABLE contact_messages ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0'
        );
    } catch (PDOException $e) {
        // Column already exists or cannot be added.
    }

    $databaseConnection->exec(
        'DELETE t1 FROM contact_messages t1
        INNER JOIN contact_messages t2
            ON t1.id > t2.id
           AND t1.name = t2.name
           AND t1.email = t2.email
           AND t1.message = t2.message'
    );

    try {
        $databaseConnection->exec(
            'CREATE UNIQUE INDEX unique_contact_message ON contact_messages (name, email, message)'
        );
    } catch (PDOException $e) {
        // Index may already exist; ignore the duplicate-key guard.
    }
}

function getDb(): PDO
{
    global $host, $dbName, $dbUser, $dbPass;

    static $pdo = null;

    if ($pdo === null) {
        initializeDatabase();

        $pdo = new PDO(
            'mysql:host=' . $host . ';dbname=' . $dbName . ';charset=utf8mb4',
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    return $pdo;
}
