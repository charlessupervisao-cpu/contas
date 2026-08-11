<?php
require_once __DIR__ . '/bootstrap.php';
if (Auth::user()) {
    redirect('/admin/index.php');
}

$token = trim((string) (get('token', '') ?: post('token', '')));
$user = $token !== '' ? Auth::userFromResetToken($token) : null;
$errors = [];
$done = false;

if (request_method() === 'POST') {
    $token = trim((string) post('token', ''));
    $password = (string) post('password', '');
    $confirm = (string) post('password_confirm', '');
    $user = Auth::userFromResetToken($token);

    if (!$user) {
        $errors[] = 'Link inválido ou expirado. Solicite uma nova recuperação.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($password !== $confirm) {
        $errors[] = 'A confirmação não confere com a senha.';
    } elseif (!Auth::resetPasswordWithToken($token, $password)) {
        $errors[] = 'Não foi possível redefinir a senha. Tente novamente.';
    } else {
        $done = true;
        flash_set('ok', 'Senha redefinida. Entre com a nova senha.');
        redirect('/login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Redefinir senha · <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
  <?php require __DIR__ . '/templates/pwa_head.php'; ?>
</head>
<body class="login-shell" data-base="<?= e(rtrim(env('APP_BASE', '') ?: '', '/')) ?>">
  <div class="login-card animate-rise">
    <div class="login-brand">
      <img src="<?= e(asset('assets/img/pollicontas-logo.png')) ?>" alt="<?= e(APP_NAME) ?>" width="52" height="52">
      <div>
        <div class="display"><?= e(APP_NAME) ?></div>
        <div class="muted">Nova senha</div>
      </div>
    </div>

    <?php if (!$user && !$errors): ?>
      <div class="alert alert-danger">Este link é inválido ou já expirou.</div>
      <a class="btn btn-primary" href="<?= e(url_path('esqueci-senha.php')) ?>" style="width:100%">Solicitar novo link</a>
      <div style="margin-top:.75rem;text-align:center">
        <a class="btn btn-ghost" href="<?= e(url_path('login.php')) ?>">Voltar ao login</a>
      </div>
    <?php else: ?>
      <?php if ($errors): ?>
        <div class="alert alert-danger"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
      <?php endif; ?>
      <?php if ($user): ?>
        <p class="muted" style="margin:0 0 1rem;font-size:.9rem">
          Conta: <strong style="color:var(--ink)"><?= e($user['email']) ?></strong>
        </p>
      <?php endif; ?>
      <form method="post" class="stack" autocomplete="off">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="field">
          <label class="label" for="password">Nova senha</label>
          <div class="password-field">
            <input class="input" type="password" id="password" name="password" required minlength="6" autocomplete="new-password">
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
        <div class="field">
          <label class="label" for="password_confirm">Confirmar senha</label>
          <div class="password-field">
            <input class="input" type="password" id="password_confirm" name="password_confirm" required minlength="6" autocomplete="new-password">
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
        <button class="btn btn-primary" type="submit" style="width:100%" <?= $user ? '' : 'disabled' ?>>Salvar nova senha</button>
      </form>
      <div style="margin-top:1.1rem;text-align:center">
        <a class="btn btn-ghost" href="<?= e(url_path('login.php')) ?>">Voltar ao login</a>
      </div>
    <?php endif; ?>
  </div>
  <script src="<?= e(asset('assets/js/app.js')) ?>"></script>
</body>
</html>
