<?php
declare(strict_types=1);

function cuid(): string
{
    $bytes = random_bytes(12);
    return 'c' . bin2hex($bytes) . substr(str_replace('.', '', uniqid('', true)), -8);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function only_digits(string $value): string
{
    return preg_replace('/\D+/', '', $value) ?? '';
}

/** Formata chave NF-e de 44 dígitos em blocos de 4 (exibição). */
function format_nfe_key(string $key): string
{
    $digits = only_digits($key);
    if ($digits === '') {
        return '';
    }
    return trim(chunk_split($digits, 4, ' '));
}

/** True quando há chave de acesso (44 dígitos) — condição para exibir “Visualizar nota”. */
function nfe_has_access_key(?string $key): bool
{
    return strlen(only_digits((string) $key)) === 44;
}

/**
 * URL interna que redireciona para o DANFE (ConsultaDANFE) pela chave de acesso.
 * Só use na UI quando nfe_has_access_key() for verdadeiro.
 */
function nfe_nota_url(?string $expenseId = null, string $key = ''): string
{
    if ($expenseId) {
        return url_path('admin/nfe-visualizar.php?id=' . rawurlencode($expenseId));
    }
    $digits = only_digits($key);
    return url_path('admin/nfe-visualizar.php?chave=' . rawurlencode($digits));
}

/** Atualiza quantidadeNfes / valorTotalNfes do emitente a partir dos lançamentos (Expense). */
function refresh_supplier_nfe_totals(PDO $pdo, string $supplierId, string $campaignId, ?string $now = null): void
{
    $now = $now ?: now_sql();
    $agg = $pdo->prepare(
        "SELECT COUNT(*) AS qtd, COALESCE(SUM(amount),0) AS valor FROM `Expense`
         WHERE supplierId = ? AND status <> 'CANCELADA'"
    );
    $agg->execute([$supplierId]);
    $row = $agg->fetch() ?: ['qtd' => 0, 'valor' => 0];
    try {
        $pdo->prepare(
            'UPDATE `Supplier` SET quantidadeNfes=?, valorTotalNfes=?, updatedAt=? WHERE id=? AND campaignId=?'
        )->execute([(int) $row['qtd'], (float) $row['valor'], $now, $supplierId, $campaignId]);
    } catch (Throwable) {
        // colunas podem ainda não existir
    }
}

function format_phone_br(?string $phone): string
{
    $d = only_digits((string) $phone);
    if (strlen($d) === 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $d) ?: $d;
    }
    if (strlen($d) === 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $d) ?: $d;
    }
    return (string) $phone;
}

function format_cep_br(?string $cep): string
{
    $d = only_digits((string) $cep);
    if (strlen($d) === 8) {
        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $d) ?: $d;
    }
    return (string) $cep;
}

/** Converte valor monetário BR/US digitado em float. */
function parse_money_input(string $value): float
{
    $value = trim($value);
    if ($value === '') {
        return 0.0;
    }
    if (str_contains($value, ',') && str_contains($value, '.')) {
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
    } elseif (str_contains($value, ',')) {
        $value = str_replace(',', '.', $value);
    }
    return is_numeric($value) ? (float) $value : 0.0;
}

function nfe_consultadanfe_url(string $key): string
{
    $digits = only_digits($key);
    if (!nfe_has_access_key($digits)) {
        return '#';
    }
    return 'https://consultadanfe.com/?chave=' . rawurlencode($digits);
}

function money_br(float|int|string|null $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function percent_br(float $ratio, int $decimals = 1): string
{
    return number_format($ratio * 100, $decimals, ',', '.') . '%';
}

function date_br(?string $value): string
{
    if (!$value) {
        return '—';
    }
    $ts = strtotime($value);
    return $ts ? date('d/m/Y', $ts) : $value;
}

function datetime_br(?string $value): string
{
    if (!$value) {
        return '—';
    }
    $ts = strtotime($value);
    return $ts ? date('d/m/Y H:i', $ts) : $value;
}

function app_base_path(): string
{
    return rtrim((string) (env('APP_BASE', '') ?: ''), '/');
}

/** Detecta HTTPS atrás de proxy/cPanel/Cloudflare. */
function is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443') {
        return true;
    }
    $fwd = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    return $fwd === 'https';
}

/**
 * Redireciona para caminho interno (respeita APP_BASE) ou URL absoluta.
 * Preferimos caminhos relativos ao host + APP_BASE para não quebrar sessão/cookie no cPanel.
 */
function redirect(string $path): never
{
    if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
        $base = app_base_path();
        $path = $base . '/' . ltrim($path, '/');
        // Normaliza // → /
        $path = '/' . ltrim($path, '/');
    }
    if (!headers_sent()) {
        header('Location: ' . $path, true, 302);
    }
    exit;
}

function flash_set(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (empty($_SESSION['_flash'])) {
        return null;
    }
    $f = $_SESSION['_flash'];
    unset($_SESSION['_flash']);
    return $f;
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function post(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $default;
}

function get(string $key, mixed $default = null): mixed
{
    return $_GET[$key] ?? $default;
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function now_sql(): string
{
    return date('Y-m-d H:i:s');
}

/** Registra ação do usuário no AuditLog. */
function audit_log(?string $userId, string $action, string $entity, ?string $entityId = null, ?string $details = null): void
{
    try {
        Database::pdo()->prepare(
            'INSERT INTO `AuditLog` (id, userId, action, entity, entityId, details, createdAt)
             VALUES (?,?,?,?,?,?,?)'
        )->execute([cuid(), $userId, $action, $entity, $entityId, $details, now_sql()]);
    } catch (Throwable) {
        // não bloqueia a operação principal
    }
}

function parse_date_input(?string $value): string
{
    if (!$value) {
        return date('Y-m-d H:i:s');
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value . ' 12:00:00';
    }
    $ts = strtotime($value);
    return $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
}

/**
 * GET HTTP com cURL (preferido) e fallback file_get_contents — útil no cPanel.
 * Retorna o corpo bruto ou null em falha.
 */
function http_get(string $url, int $timeout = 10, array $headers = []): ?string
{
    $headers = array_values(array_filter($headers, static fn ($h) => is_string($h) && $h !== ''));
    if (!in_array('Accept: application/json', $headers, true)) {
        $headers[] = 'Accept: application/json';
    }
    $hasUa = false;
    foreach ($headers as $h) {
        if (stripos($h, 'User-Agent:') === 0) {
            $hasUa = true;
            break;
        }
    }
    if (!$hasUa) {
        $headers[] = 'User-Agent: POLLICONTAS/1.0';
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch !== false) {
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (is_string($body) && $body !== '' && $code >= 200 && $code < 400) {
                return $body;
            }
            // 404 ainda pode ter JSON útil
            if (is_string($body) && $body !== '' && $code === 404) {
                return $body;
            }
        }
    }

    $ctx = stream_context_create([
        'http' => [
            'timeout' => $timeout,
            'header' => implode("\r\n", $headers) . "\r\n",
            'ignore_errors' => true,
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    return is_string($raw) && $raw !== '' ? $raw : null;
}

/** GET + decode JSON. */
function http_get_json(string $url, int $timeout = 10, array $headers = []): ?array
{
    $raw = http_get($url, $timeout, $headers);
    if ($raw === null) {
        return null;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

/**
 * Escolhe inscrição estadual ativa (preferindo a UF informada).
 *
 * @param mixed $ies
 */
function pick_inscricao_estadual(mixed $ies, string $ufPreferida = ''): string
{
    if (!is_array($ies) || $ies === []) {
        return '';
    }
    $ufPreferida = strtoupper(trim($ufPreferida));
    $fallback = '';
    foreach ($ies as $row) {
        if (!is_array($row)) {
            continue;
        }
        $ie = trim((string) ($row['inscricao_estadual'] ?? ''));
        if ($ie === '') {
            continue;
        }
        $ativo = !array_key_exists('ativo', $row) || (bool) $row['ativo'];
        $sigla = strtoupper((string) (($row['estado']['sigla'] ?? '') ?: ''));
        if ($ativo && $ufPreferida !== '' && $sigla === $ufPreferida) {
            return $ie;
        }
        if ($ativo && $fallback === '') {
            $fallback = $ie;
        } elseif ($fallback === '') {
            $fallback = $ie;
        }
    }
    return $fallback;
}

function format_phone_digits(string $digits): string
{
    $d = only_digits($digits);
    if (strlen($d) === 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $d) ?: $d;
    }
    if (strlen($d) === 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $d) ?: $d;
    }
    return $d;
}

function asset(string $path): string
{
    $base = app_base_path();
    $url = ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
    $full = dirname(__DIR__) . '/' . ltrim($path, '/');
    if (is_file($full)) {
        $url .= (str_contains($url, '?') ? '&' : '?') . 'v=' . filemtime($full);
    }
    return $url;
}

function url_path(string $path): string
{
    $base = app_base_path();
    return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
}

/** URL absoluta (e-mails / links externos). Usa APP_URL do .env. */
function absolute_url(string $path = ''): string
{
    $root = rtrim((string) env('APP_URL', ''), '/');
    if ($root === '') {
        $https = is_https();
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $root = ($https ? 'https://' : 'http://') . $host . app_base_path();
        $root = rtrim($root, '/');
    }
    if ($path === '' || $path === '/') {
        return $root . '/';
    }
    return $root . '/' . ltrim($path, '/');
}

/** Home autenticada — sempre aponta para o arquivo, evita 403 em /admin/ com Options -Indexes. */
function admin_home_path(): string
{
    return url_path('admin/index.php');
}
