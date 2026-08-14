<?php
declare(strict_types=1);

$ROOT = __DIR__;

require_once $ROOT . '/config/config.php';
load_env($ROOT);

require_once $ROOT . '/lib/Helpers.php';
require_once $ROOT . '/lib/Constants.php';
require_once $ROOT . '/lib/ElectoralRules.php';
require_once $ROOT . '/lib/Reports.php';
require_once $ROOT . '/lib/Validators.php';
require_once $ROOT . '/lib/Permissions.php';
require_once $ROOT . '/lib/Database.php';
require_once $ROOT . '/lib/Auth.php';
require_once $ROOT . '/lib/Mailer.php';
require_once $ROOT . '/lib/Categories.php';
require_once $ROOT . '/lib/Teams.php';
require_once $ROOT . '/lib/Lancamento.php';
require_once $ROOT . '/lib/LancamentoCrud.php';
require_once $ROOT . '/lib/Metrics.php';
require_once $ROOT . '/lib/Demo.php';
require_once $ROOT . '/lib/PhotoUpload.php';
require_once $ROOT . '/lib/ContractPdfUpload.php';
require_once $ROOT . '/lib/Schema.php';
require_once $ROOT . '/lib/TseFornecedores.php';
require_once $ROOT . '/lib/TseNfeCsv.php';

Auth::startSession();

date_default_timezone_set(env('APP_TIMEZONE', 'America/Sao_Paulo') ?: 'America/Sao_Paulo');

if (!defined('APP_BOOTSTRAPPED')) {
    define('APP_BOOTSTRAPPED', true);
}

// Migra colunas novas em bancos já instalados (ex.: fornecedor TSE)
try {
    Schema::ensure();
} catch (Throwable) {
    // ignore se .env/banco ainda não configurados
}
