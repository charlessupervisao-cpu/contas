<?php

declare(strict_types=1);

/** Categorias de despesa persistidas em `ExpenseCategory`. */
final class Categories
{
    /** @var list<array<string,mixed>>|null */
    private static ?array $allCache = null;

    public static function resetCache(): void
    {
        self::$allCache = null;
    }

    /** @return list<array<string,mixed>> */
    public static function all(): array
    {
        if (self::$allCache !== null) {
            return self::$allCache;
        }
        try {
            $pdo = Database::pdo();
            if (!self::tableReady($pdo)) {
                return self::$allCache = self::defaultsAsRows();
            }
            $rows = $pdo->query(
                'SELECT * FROM `ExpenseCategory` ORDER BY sortOrder ASC, label ASC'
            )->fetchAll();
            if (!$rows) {
                return self::$allCache = self::defaultsAsRows();
            }
            return self::$allCache = $rows;
        } catch (Throwable) {
            return self::$allCache = self::defaultsAsRows();
        }
    }

    /** @return list<array<string,mixed>> */
    public static function active(): array
    {
        return array_values(array_filter(self::all(), static fn ($r) => !empty($r['active'])));
    }

    /** @return array<string,string> code => label */
    public static function map(bool $onlyActive = true): array
    {
        $out = [];
        foreach ($onlyActive ? self::active() : self::all() as $row) {
            $code = (string) ($row['code'] ?? '');
            if ($code === '') {
                continue;
            }
            $out[$code] = (string) ($row['label'] ?? $code);
        }
        return $out ?: DEFAULT_EXPENSE_CATEGORIES;
    }

    /** @return array<string,string> code => color */
    public static function colors(bool $onlyActive = true): array
    {
        $out = [];
        foreach ($onlyActive ? self::active() : self::all() as $row) {
            $code = (string) ($row['code'] ?? '');
            if ($code === '') {
                continue;
            }
            $out[$code] = (string) ($row['color'] ?? (DEFAULT_EXPENSE_CATEGORY_COLORS[$code] ?? '#0d9488'));
        }
        return $out ?: DEFAULT_EXPENSE_CATEGORY_COLORS;
    }

    public static function label(string $code): string
    {
        $map = self::map(false);
        return $map[$code] ?? (DEFAULT_EXPENSE_CATEGORIES[$code] ?? $code);
    }

    public static function color(string $code): string
    {
        $colors = self::colors(false);
        return $colors[$code] ?? (DEFAULT_EXPENSE_CATEGORY_COLORS[$code] ?? '#0d9488');
    }

    public static function isValid(string $code, bool $onlyActive = true): bool
    {
        return isset(self::map($onlyActive)[$code]);
    }

    public static function normalizeCode(string $labelOrCode): string
    {
        $raw = trim($labelOrCode);
        if ($raw === '') {
            return '';
        }
        $upper = strtoupper($raw);
        if (preg_match('/^[A-Z0-9_]+$/', $upper)) {
            return $upper;
        }
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $raw);
        $ascii = $ascii !== false ? $ascii : $raw;
        $code = strtoupper((string) preg_replace('/[^A-Za-z0-9]+/', '_', $ascii));
        return trim($code, '_');
    }

    private static function tableReady(PDO $pdo): bool
    {
        $st = $pdo->prepare(
            'SELECT COUNT(*) AS c FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $st->execute(['ExpenseCategory']);
        return (int) $st->fetch()['c'] > 0;
    }

    /** @return list<array<string,mixed>> */
    private static function defaultsAsRows(): array
    {
        $rows = [];
        $i = 0;
        foreach (DEFAULT_EXPENSE_CATEGORIES as $code => $label) {
            $rows[] = [
                'id' => 'default-' . $code,
                'code' => $code,
                'label' => $label,
                'color' => DEFAULT_EXPENSE_CATEGORY_COLORS[$code] ?? '#0d9488',
                'active' => 1,
                'sortOrder' => $i++,
            ];
        }
        return $rows;
    }
}
