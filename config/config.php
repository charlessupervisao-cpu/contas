<?php
declare(strict_types=1);

/**
 * Carrega .env (chave=valor) do diretório raiz do projeto.
 */
function load_env(string $root): void
{
    $file = $root . '/.env';
    if (!is_file($file)) {
        return;
    }
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

function env(string $key, ?string $default = null): ?string
{
    $v = $_ENV[$key] ?? getenv($key);
    if ($v === false || $v === null || $v === '') {
        return $default;
    }
    return (string) $v;
}

/**
 * Aceita DATABASE_URL=mysql://user:pass@host:3306/db
 * ou variáveis DB_HOST / DB_USER / DB_PASS / DB_NAME.
 */
function db_config(): array
{
    $url = env('DATABASE_URL');
    if ($url && str_starts_with($url, 'mysql://')) {
        $parts = parse_url($url);
        if ($parts === false) {
            throw new RuntimeException('DATABASE_URL inválida.');
        }
        $db = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
        return [
            'host' => $parts['host'] ?? 'localhost',
            'port' => (string) ($parts['port'] ?? 3306),
            'user' => urldecode($parts['user'] ?? ''),
            'pass' => urldecode($parts['pass'] ?? ''),
            'name' => $db,
            'charset' => 'utf8mb4',
        ];
    }

    return [
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', '3306'),
        'user' => env('DB_USER', ''),
        'pass' => env('DB_PASS', ''),
        'name' => env('DB_NAME', ''),
        'charset' => 'utf8mb4',
    ];
}
