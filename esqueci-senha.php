<?php
require_once __DIR__ . '/bootstrap.php';
if (Auth::user()) {
    redirect('/admin/index.php');
}

$sent = false;
$error = null;

if (request_method() === 'POST') {
    $email = strtolower(trim((string) post('email', '')));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Informe um e-mail válido.';
    } else {
        Auth::requestPasswordReset($email);
        $sent = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Recuperar senha · <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
  <?php require __DIR__ . '/templates/pwa_head.php'; ?>
</head>
<body class="login-shell" data-base="<?= e(rtrim(env('APP_BASE', '') ?: '', '/')) ?>">
  <div class="login-card animate-rise">
    <div class="login-brand">
      <img src="<?= e(asset('assets/img/pollicontas-logo.png')) ?>" alt="<?= e(APP_NAME) ?>" width="52" height="52">
      <div>
        <div class="display"><?= e(APP_NAME) ?></div>
        <div class="muted">Recuperar senha</div>
      </div>
    </div>

    <?php if ($sent): ?>
      <div class="alert alert-ok">
        Se o e-mail estiver cadastrado e ativo, enviamos um link para redefinir a senha.
        Verifique também a caixa de spam.
      </div>
      <a class="btn btn-primary" href="<?= e(url_path('login.php')) ?>" style="width:100%;margin-top:.5rem">Voltar ao login</a>
    <?php else: ?>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <p class="muted" style="margin:0 0 1rem;font-size:.9rem">
        Informe o e-mail da sua conta. Enviaremos um link válido por <strong>1 hora</strong> para criar uma nova senha.
      </p>
      <form method="post" class="stack">
        <div class="field">
          <label class="label" for="email">E-mail</label>
          <input class="input" type="email" id="email" name="email" required autocomplete="username"
                 value="<?= e((string) post('email', '')) ?>" placeholder="seu@e-mail.com">
        </div>
        <button class="btn btn-primary" type="submit" style="width:100%">Enviar link de recuperação</button>
      </form>
      <div style="margin-top:1.1rem;text-align:center">
        <a class="btn btn-ghost" href="<?= e(url_path('login.php')) ?>">Voltar ao login</a>
      </div>
    <?php endif; ?>
  </div>
  <script src="<?= e(asset('assets/js/app.js')) ?>"></script>
</body>
</html>
