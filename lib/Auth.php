<?php
declare(strict_types=1);

final class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $cookiePath = app_base_path() !== '' ? app_base_path() . '/' : '/';
        session_name('pollicontas_session');
        session_set_cookie_params([
            'lifetime' => 60 * 60 * 24 * 7,
            'path' => $cookiePath,
            'secure' => is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function user(): ?array
    {
        if (empty($_SESSION['user']) || !is_array($_SESSION['user'])) {
            return null;
        }
        $u = $_SESSION['user'];
        $u['role'] = normalize_role((string) ($u['role'] ?? 'CONSULTA'));
        return $u;
    }

    public static function login(string $email, string $password): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM `User` WHERE email = ? LIMIT 1');
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch();
        if (!$row || !(int) $row['active']) {
            return null;
        }
        if (!password_verify($password, $row['passwordHash'])) {
            return null;
        }
        $user = [
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'role' => normalize_role($row['role']),
        ];
        // Regenera ANTES de gravar o usuário — evita sessão vazia em alguns hosts cPanel.
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        return $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', (bool) $p['secure'], (bool) $p['httponly']);
        }
        session_destroy();
    }

    public static function requireLogin(?string $area = null): array
    {
        $user = self::user();
        if (!$user) {
            redirect('/login.php');
        }
        if ($area !== null && !can_view($user['role'], $area)) {
            flash_set('danger', 'Você não tem permissão para acessar esta área.');
            redirect('/admin/index.php');
        }
        return $user;
    }

    public static function requireMaster(): array
    {
        $user = self::requireLogin();
        if (!can_launch($user['role'])) {
            flash_set('danger', 'Somente o perfil Master pode realizar esta ação.');
            redirect('/admin/index.php');
        }
        return $user;
    }

    /**
     * Solicita recuperação de senha. Sempre retorna true para não revelar se o e-mail existe.
     * Quando o usuário ativo existe, gera token (1h) e envia e-mail.
     */
    public static function requestPasswordReset(string $email): bool
    {
        $email = strtolower(trim($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare('SELECT id, name, email, active FROM `User` WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if (!$user || !(int) $user['active']) {
                return true;
            }

            // Invalida tokens anteriores não usados
            $pdo->prepare(
                'UPDATE `PasswordResetToken` SET usedAt = ? WHERE userId = ? AND usedAt IS NULL'
            )->execute([date('Y-m-d H:i:s'), $user['id']]);

            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);
            $expires = (new DateTimeImmutable('now'))->modify('+1 hour')->format('Y-m-d H:i:s');
            $now = date('Y-m-d H:i:s');

            $pdo->prepare(
                'INSERT INTO `PasswordResetToken` (id, userId, tokenHash, expiresAt, usedAt, createdAt)
                 VALUES (?,?,?,?,NULL,?)'
            )->execute([cuid(), $user['id'], $tokenHash, $expires, $now]);

            $link = absolute_url('redefinir-senha.php?token=' . urlencode($rawToken));
            $name = (string) $user['name'];
            $subject = APP_NAME . ' — redefinir senha';
            $text = "Olá, {$name}.\n\n"
                . "Recebemos um pedido para redefinir a senha da sua conta em " . APP_NAME . ".\n\n"
                . "Acesse o link abaixo (válido por 1 hora):\n{$link}\n\n"
                . "Se você não solicitou, ignore este e-mail.\n";
            $html = '<p>Olá, <strong>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</strong>.</p>'
                . '<p>Recebemos um pedido para redefinir a senha da sua conta em <strong>' . htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') . '</strong>.</p>'
                . '<p><a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:12px 18px;background:#0d9488;color:#fff;text-decoration:none;border-radius:10px;font-weight:700">Redefinir senha</a></p>'
                . '<p style="color:#666;font-size:13px">O link é válido por 1 hora. Se você não solicitou, ignore este e-mail.</p>'
                . '<p style="color:#999;font-size:12px;word-break:break-all">' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '</p>';

            Mailer::send((string) $user['email'], $subject, $text, $html);
        } catch (Throwable) {
            // Silencioso — resposta genérica na UI
        }

        return true;
    }

    /** Retorna o usuário do token válido ou null. */
    public static function userFromResetToken(string $rawToken): ?array
    {
        $rawToken = trim($rawToken);
        if (strlen($rawToken) < 32) {
            return null;
        }
        $tokenHash = hash('sha256', $rawToken);
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT t.id AS tokenId, t.expiresAt, t.usedAt, u.id, u.name, u.email, u.active
             FROM `PasswordResetToken` t
             INNER JOIN `User` u ON u.id = t.userId
             WHERE t.tokenHash = ? LIMIT 1'
        );
        $stmt->execute([$tokenHash]);
        $row = $stmt->fetch();
        if (!$row || !(int) $row['active'] || $row['usedAt']) {
            return null;
        }
        if (strtotime((string) $row['expiresAt']) < time()) {
            return null;
        }
        return [
            'tokenId' => $row['tokenId'],
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
        ];
    }

    public static function resetPasswordWithToken(string $rawToken, string $newPassword): bool
    {
        if (strlen($newPassword) < 6) {
            return false;
        }
        $user = self::userFromResetToken($rawToken);
        if (!$user) {
            return false;
        }
        $pdo = Database::pdo();
        $now = date('Y-m-d H:i:s');
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE `User` SET passwordHash = ?, updatedAt = ? WHERE id = ?')
                ->execute([$hash, $now, $user['id']]);
            $pdo->prepare('UPDATE `PasswordResetToken` SET usedAt = ? WHERE id = ?')
                ->execute([$now, $user['tokenId']]);
            $pdo->prepare(
                'UPDATE `PasswordResetToken` SET usedAt = ? WHERE userId = ? AND usedAt IS NULL AND id <> ?'
            )->execute([$now, $user['id'], $user['tokenId']]);
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }
}
