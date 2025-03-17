<?php

declare(strict_types=1);

final class Config
{
    public static function fromEnv(): array
    {
        $driver = getenv('GS_DB_DRIVER') ?: 'pgsql';

        return [
            'driver' => $driver,
            'host' => getenv('GS_DB_HOST') ?: 'db-acupinir.groupscholar.com',
            'port' => getenv('GS_DB_PORT') ?: '23947',
            'name' => getenv('GS_DB_NAME') ?: 'postgres',
            'user' => getenv('GS_DB_USER') ?: 'ralph',
            'password' => getenv('GS_DB_PASSWORD') ?: '',
            'sslmode' => getenv('GS_DB_SSLMODE') ?: 'prefer',
            'sqlite_path' => getenv('GS_SQLITE_PATH') ?: ':memory:',
        ];
    }
}
