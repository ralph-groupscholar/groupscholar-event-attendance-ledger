<?php

declare(strict_types=1);

final class Database
{
    public static function connect(array $config): PDO
    {
        $driver = $config['driver'];

        if ($driver === 'sqlite') {
            $dsn = 'sqlite:' . $config['sqlite_path'];
            $pdo = new PDO($dsn);
        } else {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
                $config['host'],
                $config['port'],
                $config['name'],
                $config['sslmode']
            );
            $pdo = new PDO($dsn, $config['user'], $config['password']);
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }
}
