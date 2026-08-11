<?php

declare(strict_types=1);

/**
 * Compatibilidade: o espelho interno foi substituído pelo visualizador
 * que abre o DANFE real via ConsultaDANFE (chave de acesso).
 */
require_once dirname(__DIR__) . '/bootstrap.php';

$id = trim((string) ($_GET['id'] ?? ''));
$chave = only_digits((string) ($_GET['chave'] ?? ''));

if ($id !== '') {
    redirect('/admin/nfe-visualizar.php?id=' . rawurlencode($id));
}
if ($chave !== '') {
    redirect('/admin/nfe-visualizar.php?chave=' . rawurlencode($chave));
}
redirect('/admin/despesas.php');
