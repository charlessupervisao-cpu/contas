<?php
require_once __DIR__ . '/bootstrap.php';
if (Auth::user()) {
    redirect('/admin/index.php');
}

$error = null;

if (request_method() === 'POST') {
    $email = strtolower(trim((string) post('email', '')));
    $password = (string) post('password', '');
    $user = Auth::login($email, $password);
    if ($user) {
        redirect('/admin/index.php');
    }
    $error = 'E-mail ou senha inválidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Entrar · <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
  <?php require __DIR__ . '/templates/pwa_head.php'; ?>
</head>
<body class="login-shell" data-base="<?= e(rtrim(env('APP_BASE', '') ?: '', '/')) ?>">
  <div class="login-card animate-rise">
    <div class="login-brand">
      <img src="<?= e(asset('assets/img/pollicontas-logo.png')) ?>" alt="<?= e(APP_NAME) ?>" width="52" height="52">
      <div>
        <div class="display"><?= e(APP_NAME) ?></div>
        <div class="muted"><?= e(APP_DOMAIN) ?></div>
      </div>
    </div>
    <?php
      $flash = flash_get();
      if ($flash):
    ?>
      <div class="alert alert-<?= e($flash['type'] === 'danger' ? 'danger' : ($flash['type'] === 'ok' ? 'ok' : 'info')) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="stack" autocomplete="on">
      <div class="field">
        <label class="label" for="email">E-mail</label>
        <input class="input" type="email" id="email" name="email" required autocomplete="username" value="<?= e((string) post('email', '')) ?>" placeholder="seu@e-mail.com">
      </div>
      <div class="field">
        <div style="display:flex;justify-content:space-between;align-items:baseline;gap:.75rem;margin-bottom:.3rem">
          <label class="label" for="password" style="margin:0">Senha</label>
          <a href="<?= e(url_path('esqueci-senha.php')) ?>" style="font-size:.78rem;font-weight:700;color:var(--accent)">Esqueci a senha</a>
        </div>
        <div class="password-field">
          <input class="input" type="password" id="password" name="password" required autocomplete="current-password" value="">
          <button type="button" class="password-toggle" data-password-toggle aria-label="Mostrar senha" title="Mostrar senha">
            <span class="pw-icon-show" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </span>
            <span class="pw-icon-hide" aria-hidden="true" hidden>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </span>
          </button>
        </div>
      </div>
      <button class="btn btn-primary" type="submit" style="width:100%">Entrar no dashboard</button>
    </form>
    <div style="margin-top:1.25rem;font-size:.85rem" class="muted">
      Sistema <strong>interno</strong>: use o e-mail e a senha cadastrados. Só o perfil <strong>Master</strong> altera dados.
    </div>
    <a class="login-synetiq" href="https://www.synetiq.com.br" target="_blank" rel="noopener noreferrer" title="SynetIQ — Soluções Digitais Inteligentes">
      <img src="<?= e(asset('assets/img/synetiq-wordmark-sm.png')) ?>" alt="SynetIQ — Soluções Digitais Inteligentes" width="160" height="52" loading="lazy">
    </a>
  </div>
  <?php require __DIR__ . '/templates/pwa_install.php'; ?>
  <?php require __DIR__ . '/templates/back_to_top.php'; ?>
  <script src="<?= e(asset('assets/js/app.js')) ?>"></script>
</body>
</html>
