<?php
declare(strict_types=1);

function can_launch(?string $role): bool
{
    return $role === ROLES['MASTER'];
}

function can_view(?string $role, string $area): bool
{
    if (!$role) {
        return false;
    }
    $allowed = VIEW_ROLES[$area] ?? [];
    return in_array($role, $allowed, true);
}

function normalize_role(string $role): string
{
    if ($role === 'ADMIN' || $role === 'OPERADOR' || $role === ROLES['MASTER']) {
        return ROLES['MASTER'];
    }
    if ($role === ROLES['FINANCEIRO']) {
        return ROLES['FINANCEIRO'];
    }
    if ($role === ROLES['RH']) {
        return ROLES['RH'];
    }
    return ROLES['CONSULTA'];
}
