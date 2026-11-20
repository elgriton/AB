<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

// Подключаемся к базе. Имя базы добавляется в только по запросу.
final class Database
{
    
    public static function connect(array $config, bool $selectDatabase = true): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $config['host'], (int) $config['port']);
        if ($selectDatabase) {
            $dsn .= ';dbname=' . $config['database'];
        }

        return new PDO($dsn, (string) $config['username'], (string) $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}


