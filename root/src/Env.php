<?php

namespace App;

/**
 * Reads simple KEY=value pairs from a .env file one level above DOCUMENT_ROOT -
 * outside the web root and out of source control - so configuration that varies
 * per environment or must stay secret (base URL, cookie domain, mail sender,
 * DB credentials, the JWT signing key) lives in one place. Replaces the former
 * db_credentials.txt (see Database.php).
 */
class Env
{
    private static ?array $values = null;

    public static function get(string $key): ?string
    {
        self::load();
        return self::$values[$key] ?? null;
    }

    /**
     * Like get(), but throws when the key is missing or empty - for values the
     * application cannot run correctly without (secrets, DB credentials, the
     * public base URL). Fails loudly at the point of use instead of silently
     * degrading (e.g. unsigned tokens, cookies on the wrong domain).
     */
    public static function getRequired(string $key): string
    {
        $value = self::get($key);
        if($value === null || $value === ''){
            throw new \RuntimeException("Environment variable {$key} is not configured - see .env");
        }
        return $value;
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
