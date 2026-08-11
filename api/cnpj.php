<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

if (!Auth::user()) {
    json_response(['ok' => false, 'error' => 'Sessão expirada. Faça login novamente.'], 401);
}

$cnpj = only_digits((string) ($_GET['cnpj'] ?? ''));
if (strlen($cnpj) !== 14 || !is_valid_cnpj($cnpj)) {
    json_response(['ok' => false, 'error' => 'CNPJ inválido.'], 400);
}

$brasil = http_get_json('https://brasilapi.com.br/api/cnpj/v1/' . rawurlencode($cnpj), 12);
$ws = http_get_json('https://publica.cnpj.ws/cnpj/' . rawurlencode($cnpj), 12);

if (($brasil === null || empty($brasil['razao_social'])) && ($ws === null || empty($ws['razao_social']))) {
    json_response(['ok' => false, 'error' => 'CNPJ não encontrado.'], 404);
}

$est = is_array($ws['estabelecimento'] ?? null) ? $ws['estabelecimento'] : [];

$name = (string) (($brasil['razao_social'] ?? null) ?: ($ws['razao_social'] ?? ''));
$tradeName = (string) (($brasil['nome_fantasia'] ?? null) ?: ($est['nome_fantasia'] ?? ''));
$email = (string) (($brasil['email'] ?? null) ?: ($est['email'] ?? ''));

$phone = '';
if (!empty($brasil['ddd_telefone_1'])) {
    $phone = format_phone_digits((string) $brasil['ddd_telefone_1']);
} elseif (!empty($est['ddd1']) && !empty($est['telefone1'])) {
    $phone = format_phone_digits((string) $est['ddd1'] . (string) $est['telefone1']);
}

$cepRaw = only_digits((string) (($brasil['cep'] ?? null) ?: ($est['cep'] ?? '')));
$cepFmt = strlen($cepRaw) === 8 ? (preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cepRaw) ?: $cepRaw) : '';

if (!empty($brasil['logradouro'])) {
    $address = trim((string) ($brasil['descricao_tipo_de_logradouro'] ?? '') . ' ' . (string) $brasil['logradouro']);
    $addressNumber = (string) ($brasil['numero'] ?? '');
    $addressComplement = (string) ($brasil['complemento'] ?? '');
    $neighborhood = (string) ($brasil['bairro'] ?? '');
    $city = (string) ($brasil['municipio'] ?? '');
    $state = (string) ($brasil['uf'] ?? '');
} else {
    $address = trim((string) ($est['tipo_logradouro'] ?? '') . ' ' . (string) ($est['logradouro'] ?? ''));
    $addressNumber = (string) ($est['numero'] ?? '');
    $addressComplement = (string) ($est['complemento'] ?? '');
    $neighborhood = (string) ($est['bairro'] ?? '');
    $city = (string) (($est['cidade']['nome'] ?? '') ?: '');
    $state = (string) (($est['estado']['sigla'] ?? '') ?: '');
}

$stateRegistration = pick_inscricao_estadual($est['inscricoes_estaduais'] ?? [], $state);

json_response([
    'ok' => true,
    'document' => format_cpf_cnpj($cnpj),
    'name' => $name,
    'tradeName' => $tradeName,
    'email' => $email,
    'phone' => $phone,
    'zipCode' => $cepFmt,
    'address' => $address,
    'addressNumber' => $addressNumber,
    'addressComplement' => $addressComplement,
    'neighborhood' => $neighborhood,
    'city' => $city,
    'state' => $state,
    'stateRegistration' => $stateRegistration,
]);
