<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/** Создаёт PDO-подключение к серверу MySQL или конкретной базе данных. */
final class Database
{
    /**
     * Открывает соединение с MySQL.
     *
     * @param array{host:string,port:int|string,database?:string,username:string,password:string} $config Параметры подключения.
     * @param bool $selectDatabase Нужно ли выбрать базу данных в DSN.
     * @return PDO Открытое PDO-соединение.
     * @throws \PDOException Если соединение установить не удалось.
     */
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


