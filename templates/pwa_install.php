<?php
/** Banner "Instalar app" (PWA). */
?>
<div class="pwa-install" data-pwa-install hidden>
  <div class="pwa-install-inner">
    <div class="pwa-install-copy">
      <strong>Instalar <?= e(APP_NAME) ?></strong>
      <span>Acesse mais rápido, como aplicativo</span>
    </div>
    <div class="pwa-install-actions">
      <button type="button" class="btn btn-primary" data-pwa-install-btn>Instalar</button>
      <button type="button" class="btn btn-ghost" data-pwa-install-dismiss>Agora não</button>
    </div>
  </div>
</div>
