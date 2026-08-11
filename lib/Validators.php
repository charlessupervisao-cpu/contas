<?php
declare(strict_types=1);

function is_valid_cpf(string $cpf): bool
{
    $digits = only_digits($cpf);
    if (strlen($digits) !== 11) {
        return false;
    }
    if (preg_match('/^(\d)\1+$/', $digits)) {
        return false;
    }

    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += (int) $digits[$i] * (10 - $i);
    }
    $rest = ($sum * 10) % 11;
    if ($rest === 10) {
        $rest = 0;
    }
    if ($rest !== (int) $digits[9]) {
        return false;
    }

    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
        $sum += (int) $digits[$i] * (11 - $i);
    }
    $rest = ($sum * 10) % 11;
    if ($rest === 10) {
        $rest = 0;
    }
    return $rest === (int) $digits[10];
}

function is_valid_cnpj(string $cnpj): bool
{
    $digits = only_digits($cnpj);
    if (strlen($digits) !== 14) {
        return false;
    }
    if (preg_match('/^(\d)\1+$/', $digits)) {
        return false;
    }

    $w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += (int) $digits[$i] * $w1[$i];
    }
    $r = $sum % 11;
    $d1 = $r < 2 ? 0 : 11 - $r;
    if ($d1 !== (int) $digits[12]) {
        return false;
    }
    $sum = 0;
    for ($i = 0; $i < 13; $i++) {
        $sum += (int) $digits[$i] * $w2[$i];
    }
    $r = $sum % 11;
    $d2 = $r < 2 ? 0 : 11 - $r;
    return $d2 === (int) $digits[13];
}

/** Detecta CPF/CNPJ a partir dos dígitos. */
function document_type_from_digits(string $doc): ?string
{
    $digits = only_digits($doc);
    $len = strlen($digits);
    if ($len === 11) {
        return 'CPF';
    }
    if ($len === 14) {
        return 'CNPJ';
    }
    return null;
}

function format_cpf_cnpj(?string $doc): string
{
    $digits = only_digits((string) $doc);
    if (strlen($digits) === 11) {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits) ?: $digits;
    }
    if (strlen($digits) === 14) {
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits) ?: $digits;
    }
    return (string) $doc;
}

function generate_valid_cnpj(int $seed): string
{
    $base = substr(str_pad((string) (100000000000 + ($seed * 17)), 12, '0', STR_PAD_LEFT), 0, 12);
    $w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += (int) $base[$i] * $w1[$i];
    }
    $r = $sum % 11;
    $d1 = $r < 2 ? 0 : 11 - $r;
    $with = $base . $d1;
    $sum = 0;
    for ($i = 0; $i < 13; $i++) {
        $sum += (int) $with[$i] * $w2[$i];
    }
    $r = $sum % 11;
    $d2 = $r < 2 ? 0 : 11 - $r;
    return $with . $d2;
}

/** @return array{valid:bool,reason?:string,parsed?:array} */
function validate_nfe_key(string $key): array
{
    $digits = only_digits($key);
    if (strlen($digits) !== 44) {
        return ['valid' => false, 'reason' => 'A chave da NF-e deve ter 44 dígitos.'];
    }

    $weights = [2, 3, 4, 5, 6, 7, 8, 9];
    $sum = 0;
    $weightIndex = 0;
    for ($i = 42; $i >= 0; $i--) {
        $sum += (int) $digits[$i] * $weights[$weightIndex];
        $weightIndex = ($weightIndex + 1) % count($weights);
    }
    $mod = $sum % 11;
    $expectedDv = ($mod === 0 || $mod === 1) ? 0 : 11 - $mod;
    $actualDv = (int) $digits[43];

    if ($expectedDv !== $actualDv) {
        return [
            'valid' => false,
            'reason' => "Dígito verificador inválido (esperado {$expectedDv}).",
        ];
    }

    $model = substr($digits, 20, 2);
    if ($model !== '55' && $model !== '65') {
        return [
            'valid' => false,
            'reason' => "Modelo fiscal inválido ({$model}). Esperado 55 (NF-e) ou 65 (NFC-e).",
        ];
    }

    return [
        'valid' => true,
        'parsed' => [
            'uf' => substr($digits, 0, 2),
            'yearMonth' => substr($digits, 2, 4),
            'cnpj' => substr($digits, 6, 14),
            'model' => $model,
            'series' => substr($digits, 22, 3),
            'number' => substr($digits, 25, 9),
        ],
    ];
}

function generate_valid_nfe_key(int $seed = 1): string
{
    $uf = '52';
    $aamm = '2608';
    $cnpj = substr(str_pad((string) (10000000000000 + $seed), 14, '0', STR_PAD_LEFT), 0, 14);
    $mod = '55';
    $serie = '001';
    $nnf = substr((string) (100000000 + $seed), 0, 9);
    $tpEmis = '1';
    $cnf = substr((string) (10000000 + $seed), 0, 8);
    $base = "{$uf}{$aamm}{$cnpj}{$mod}{$serie}{$nnf}{$tpEmis}{$cnf}";

    $weights = [2, 3, 4, 5, 6, 7, 8, 9];
    $sum = 0;
    $weightIndex = 0;
    for ($i = 42; $i >= 0; $i--) {
        $sum += (int) $base[$i] * $weights[$weightIndex];
        $weightIndex = ($weightIndex + 1) % count($weights);
    }
    $mod11 = $sum % 11;
    $dv = ($mod11 === 0 || $mod11 === 1) ? 0 : 11 - $mod11;
    return $base . $dv;
}

function generate_valid_cpf(int $seed): string
{
    $base = substr(str_pad((string) (100000000 + $seed), 9, '0', STR_PAD_LEFT), 0, 9);
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += (int) $base[$i] * (10 - $i);
    }
    $d1 = ($sum * 10) % 11;
    if ($d1 === 10) {
        $d1 = 0;
    }
    $withD1 = $base . $d1;
    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
        $sum += (int) $withD1[$i] * (11 - $i);
    }
    $d2 = ($sum * 10) % 11;
    if ($d2 === 10) {
        $d2 = 0;
    }
    return $withD1 . $d2;
}
