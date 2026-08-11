<?php
declare(strict_types=1);

/**
 * Instalação one-shot no cPanel:
 * 1) Configura .env
 * 2) Aplica sql/schema.sql
 * 3) Carrega usuários + dados demo
 *
 * Remova ou proteja este arquivo após instalar.
 */
require_once __DIR__ . '/bootstrap.php';

$messages = [];
$errors = [];
$done = false;

function run_sql_file(PDO $pdo, string $path): void
{
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException('Não foi possível ler ' . $path);
    }
    // Remove comments and split by semicolon carefully enough for our schema
    $sql = preg_replace('/^--.*$/m', '', $sql) ?? $sql;
    $parts = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($parts as $stmt) {
        if ($stmt === '') {
            continue;
        }
        $pdo->exec($stmt);
    }
}

if (request_method() === 'POST') {
    $host = trim((string) post('db_host', 'localhost'));
    $port = trim((string) post('db_port', '3306'));
    $name = trim((string) post('db_name', ''));
    $user = trim((string) post('db_user', ''));
    $pass = (string) post('db_pass', '');
    $authSecret = trim((string) post('auth_secret', bin2hex(random_bytes(16))));
    $appUrl = trim((string) post('app_url', 'https://pollicontas.synetiq.com.br'));
    $loadDemo = (bool) post('load_demo', '1');

    if ($name === '' || $user === '') {
        $errors[] = 'Informe nome do banco e usuário MySQL do cPanel.';
    } else {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $urlUser = rawurlencode($user);
            $urlPass = rawurlencode($pass);
            $env = <<<ENV
DATABASE_URL="mysql://{$urlUser}:{$urlPass}@{$host}:{$port}/{$name}"
DB_HOST="{$host}"
DB_PORT="{$port}"
DB_NAME="{$name}"
DB_USER="{$user}"
DB_PASS="{$pass}"
AUTH_SECRET="{$authSecret}"
APP_URL="{$appUrl}"
APP_BASE=""
APP_TIMEZONE="America/Sao_Paulo"
ENV;
            if (file_put_contents(__DIR__ . '/.env', $env) === false) {
                throw new RuntimeException('Não foi possível gravar o arquivo .env');
            }
            load_env(__DIR__);
            $messages[] = '.env gravado com sucesso.';

            // reconnect via app Database
            $ref = new ReflectionClass(Database::class);
            $prop = $ref->getProperty('pdo');
            $prop->setAccessible(true);
            $prop->setValue(null, null);

            run_sql_file(Database::pdo(), __DIR__ . '/sql/schema.sql');
            $messages[] = 'Schema MySQL aplicado.';

            Demo::ensureDefaultUsers();
            $messages[] = 'Usuários padrão criados (senha admin123).';

            if ($loadDemo) {
                $summary = Demo::loadDemoData();
                $messages[] = 'Dados demo carregados: ' . json_encode($summary, JSON_UNESCAPED_UNICODE);
            } else {
                Demo::clearOperationalData();
                $messages[] = 'Campanha base criada (sem movimentações).';
            }

            $done = true;
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$db = db_config();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Instalação · <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="login-shell">
  <div class="login-card animate-rise" style="width:min(100%,560px)">
    <div class="display" style="font-size:1.6rem;font-weight:800;margin-bottom:.25rem"><?= e(APP_NAME) ?></div>
    <p class="muted">Instalação PHP + MySQL (cPanel) · <?= e(APP_DOMAIN) ?></p>

    <?php foreach ($errors as $err): ?>
      <div class="alert alert-danger"><?= e($err) ?></div>
    <?php endforeach; ?>
    <?php foreach ($messages as $msg): ?>
      <div class="alert alert-ok"><?= e($msg) ?></div>
    <?php endforeach; ?>

    <?php if ($done): ?>
      <div class="stack">
        <a class="btn btn-primary" href="<?= e(url_path('login.php')) ?>">Ir para o login</a>
        <a class="btn btn-secondary" href="<?= e(url_path('api/health.php')) ?>">Testar /api/health.php</a>
        <p class="muted" style="font-size:.85rem">Por segurança, remova ou proteja <code>install.php</code> após concluir.</p>
      </div>
    <?php else: ?>
      <form method="post" class="stack">
        <div class="field"><label class="label">Host MySQL</label><input class="input" name="db_host" value="<?= e($db['host'] ?: 'localhost') ?>" required></div>
        <div class="grid grid-2">
          <div class="field"><label class="label">Porta</label><input class="input" name="db_port" value="<?= e($db['port'] ?: '3306') ?>"></div>
          <div class="field"><label class="label">Banco</label><input class="input" name="db_name" value="<?= e($db['name'] ?: '') ?>" required placeholder="usuario_pollicontas"></div>
        </div>
        <div class="field"><label class="label">Usuário MySQL</label><input class="input" name="db_user" value="<?= e($db['user'] ?: '') ?>" required></div>
        <div class="field"><label class="label">Senha MySQL</label><input class="input" type="password" name="db_pass" value="" required></div>
        <div class="field"><label class="label">URL do site</label><input class="input" name="app_url" value="https://pollicontas.synetiq.com.br"></div>
        <div class="field"><label class="label">AUTH_SECRET</label><input class="input" name="auth_secret" value="<?= e(bin2hex(random_bytes(16))) ?>"></div>
        <label class="row-actions"><input type="checkbox" name="load_demo" value="1" checked> Carregar dados demonstrativos</label>
        <button class="btn btn-primary" type="submit">Instalar schema + seed</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
