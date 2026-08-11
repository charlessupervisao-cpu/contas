<?php

declare(strict_types=1);

/**
 * Compatibilidade: chave de acesso NF-e foi removida do lançamento.
 * Mantém a rota para links antigos e redireciona à lista de despesas.
 */
require_once dirname(__DIR__) . '/bootstrap.php';

Auth::requireLogin('despesas');
flash_set('warn', 'A visualização por chave de acesso NF-e foi descontinuada. Use o Nº NF no lançamento.');
redirect('/admin/despesas.php');
