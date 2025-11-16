<?php

namespace App\Support;

use PDO;
use RuntimeException;

class DatabaseFactory
{
    public static function make(): PDO
    {
        $dsn = getenv('DB_DSN') ?: self::defaultDsn();

        if (! str_starts_with($dsn, 'sqlite')) {
            throw new RuntimeException('Only sqlite DSN strings are supported in the demo application.');
        }

        if ($dsn !== 'sqlite::memory:' && $dsn !== 'sqlite::memory') {
            self::ensureSqlitePath($dsn);
        }

        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }

    protected static function defaultDsn(): string
    {
        $databasePath = __DIR__ . '/../../database/database.sqlite';
        self::ensureFileDirectory($databasePath);

        return 'sqlite:' . $databasePath;
    }

    protected static function ensureFileDirectory(string $filePath): void
    {
        $directory = dirname($filePath);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    protected static function ensureSqlitePath(string $dsn): void
    {
        if (! str_contains($dsn, 'sqlite:')) {
            return;
        }

        $path = substr($dsn, strlen('sqlite:'));

        if ($path === ':memory:') {
            return;
        }

        self::ensureFileDirectory($path);
    }
}
