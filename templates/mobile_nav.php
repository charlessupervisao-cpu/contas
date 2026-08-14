<?php
/**
 * Navegação inferior (tablet/celular) + painel Configurações.
 * @var array $user
 * @var string $activeModule
 */
$activeModule = $activeModule ?? '';
$user = $user ?? Auth::user();
if (!$user) {
    return;
}

$tabMods = [];
foreach (MODULES as $mod) {
    $group = $mod['group'] ?? '';
    if (!in_array($group, ['primary', 'ops'], true)) {
        continue;
    }
    if (!in_array($user['role'], $mod['roles'], true)) {
        continue;
    }
    // Barra compacta: atalhos principais
    if (in_array($mod['id'], ['dashboard', 'receitas', 'despesas', 'lancamento', 'movimentacoes'], true)) {
        $tabMods[] = $mod;
    }
}

$configMods = array_values(array_filter(
    MODULES,
    static fn ($m) => ($m['group'] ?? '') === 'config' && in_array($user['role'], $m['roles'], true)
));
usort($configMods, static function (array $a, array $b): int {
    return ((int) ($a['setupOrder'] ?? 999)) <=> ((int) ($b['setupOrder'] ?? 999));
});
$opsExtra = array_values(array_filter(
    MODULES,
    static fn ($m) => ($m['group'] ?? '') === 'ops'
        && in_array($user['role'], $m['roles'], true)
        && !in_array($m['id'], array_column($tabMods, 'id'), true)
));
?>
<nav class="mobile-tabbar" aria-label="Atalhos">
  <?php foreach ($tabMods as $mod): ?>
    <a class="mobile-tab<?= $activeModule === ($mod['id'] ?? '') ? ' mobile-tab-active' : '' ?>"
       href="<?= e(url_path(ltrim($mod['href'], '/'))) ?>"
       data-mobile-key="<?= e($mod['shortcut'] ?? '') ?>">
      <span class="mobile-tab-key"><?= e($mod['shortcut'] ?? mb_substr($mod['label'], 0, 1)) ?></span>
      <span class="mobile-tab-label"><?= e($mod['label']) ?></span>
    </a>
  <?php endforeach; ?>
  <?php if ($configMods || $opsExtra): ?>
    <button type="button" class="mobile-tab" data-config-open aria-label="Abrir configurações">
      <span class="mobile-tab-key">⚙</span>
      <span class="mobile-tab-label">Config</span>
    </button>
  <?php endif; ?>
</nav>

<div class="mobile-config-overlay" data-config-overlay hidden></div>
<aside class="mobile-config-sheet" data-config-sheet hidden aria-label="Configurações">
  <div class="mobile-config-head">
    <div>
      <div class="mobile-config-kicker"><?= e(APP_NAME) ?></div>
      <h2 class="mobile-config-title">Configurações</h2>
    </div>
    <button type="button" class="mobile-config-close" data-config-close aria-label="Fechar">×</button>
  </div>
  <div class="mobile-config-body">
    <?php if ($opsExtra): ?>
      <div class="mobile-config-section">
        <div class="mobile-config-section-label">Operação</div>
        <?php foreach ($opsExtra as $mod): ?>
          <a class="mobile-config-link<?= $activeModule === ($mod['id'] ?? '') ? ' is-active' : '' ?>"
             href="<?= e(url_path(ltrim($mod['href'], '/'))) ?>">
            <span><?= e($mod['label']) ?></span>
            <span class="mobile-config-chevron" aria-hidden="true">›</span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if ($configMods): ?>
      <div class="mobile-config-section">
        <div class="mobile-config-section-label">Cadastro na ordem (1 → 12)</div>
        <?php foreach ($configMods as $mod): ?>
          <a class="mobile-config-link<?= $activeModule === ($mod['id'] ?? '') ? ' is-active' : '' ?>"
             href="<?= e(url_path(ltrim($mod['href'], '/'))) ?>">
            <span><?= e($mod['label']) ?></span>
            <span class="mobile-config-chevron" aria-hidden="true">›</span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <div class="mobile-config-foot">
    <a class="mobile-config-brand" href="<?= e(APP_VENDOR_URL) ?>" target="_blank" rel="noopener noreferrer" title="<?= e(APP_VENDOR) ?>">
      <img src="<?= e(asset('assets/img/synetiq-wordmark-sm.png')) ?>" alt="<?= e(APP_VENDOR . ' — ' . APP_VENDOR_TAGLINE) ?>" width="160" height="52" loading="lazy">
    </a>
    <a class="mobile-config-logout" href="<?= e(url_path('logout.php')) ?>">Sair da conta</a>
  </div>
</aside>
