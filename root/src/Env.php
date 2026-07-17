<?php

namespace App;

/**
 * Reads simple KEY=value pairs from a .env file one level above DOCUMENT_ROOT -
 * same location/convention as db_credentials.txt (see Database.php) - so secrets
 * that don't belong in source control (e.g. the JWT signing key) have somewhere
 * to live without a new deployment step of their own.
 */
class Env
{
    private static ?array $values = null;

    public static function get(string $key): ?string
    {
        self::load();
        return self::$values[$key] ?? null;
    }

    private static function load(): void
    {
        if(self::$values !== null){
            return;
        }
        self::$values = [];
        $path = dirname($_SERVER['DOCUMENT_ROOT']) . '/.env';
        if(!is_file($path)){
            return;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')){
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            self::$values[trim($key)] = trim($value);
        }
    }
}
