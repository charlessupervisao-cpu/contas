<?php

declare(strict_types=1);

/** Equipes / integrantes persistidos em `Team` (cadastro prévio). Vínculo com cabos usa o `id`. */
final class Teams
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
                'SELECT * FROM `Team` ORDER BY memberNumber ASC, label ASC, sortOrder ASC'
            )->fetchAll();
            if (!$rows) {
                return self::$allCache = self::defaultsAsRows();
            }
            return self::$allCache = $rows;
        } catch (Throwable) {
            return self::$allCache = self::defaultsAsRows();
        }
    }

    /** @return list<array<string,mixed>> ativos, nomes A→Z */
    public static function active(): array
    {
        $rows = array_values(array_filter(self::all(), static fn ($r) => !empty($r['active'])));
        usort($rows, static fn ($a, $b) => strcasecmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? '')));
        return $rows;
    }

    /** @return array<string,string> id => label */
    public static function map(bool $onlyActive = true): array
    {
        $out = [];
        foreach ($onlyActive ? self::active() : self::all() as $row) {
            $id = (string) ($row['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $out[$id] = (string) ($row['label'] ?? $id);
        }
        return $out;
    }

    public static function label(string $id): string
    {
        if ($id === '') {
            return '';
        }
        $map = self::map(false);
        if (isset($map[$id])) {
            return $map[$id];
        }
        // Legado: valor antigo era o slug `code` — tenta resolver pelo nome do seed.
        if (defined('DEFAULT_TEAMS') && isset(DEFAULT_TEAMS[$id])) {
            return DEFAULT_TEAMS[$id];
        }
        return $id;
    }

    public static function isValid(string $id, bool $onlyActive = true): bool
    {
        return $id !== '' && isset(self::map($onlyActive)[$id]);
    }

    /** Próximo código numérico automático do integrante. */
    public static function nextMemberNumber(): int
    {
        try {
            $pdo = Database::pdo();
            if (!self::tableReady($pdo)) {
                return count(DEFAULT_TEAMS) + 1;
            }
            $max = (int) $pdo->query('SELECT COALESCE(MAX(memberNumber), 0) AS m FROM `Team`')->fetch()['m'];
            return $max + 1;
        } catch (Throwable) {
            return 1;
        }
    }

    private static function tableReady(PDO $pdo): bool
    {
        $st = $pdo->prepare(
            'SELECT COUNT(*) AS c FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $st->execute(['Team']);
        return (int) $st->fetch()['c'] > 0;
    }

    /** @return list<array<string,mixed>> */
    private static function defaultsAsRows(): array
    {
        $rows = [];
        $i = 0;
        $defaults = DEFAULT_TEAMS;
        asort($defaults, SORT_NATURAL | SORT_FLAG_CASE);
        $meta = DEFAULT_TEAM_PROFILES;
        foreach ($defaults as $seedKey => $label) {
            $profile = $meta[$seedKey] ?? [];
            $rows[] = [
                'id' => 'default-' . ($i + 1),
                'memberNumber' => $i + 1,
                'label' => $label,
                'phone' => $profile['phone'] ?? null,
                'city' => $profile['city'] ?? null,
                'cityRegion' => $profile['cityRegion'] ?? null,
                'active' => 1,
                'sortOrder' => $i,
            ];
            $i++;
        }
        return $rows;
    }
}
