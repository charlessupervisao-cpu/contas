<?php
declare(strict_types=1);

/** Envio simples de e-mail (cPanel / mail()). */
final class Mailer
{
    public static function send(string $to, string $subject, string $textBody, ?string $htmlBody = null): bool
    {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $fromEmail = env('MAIL_FROM', 'noreply@' . (parse_url((string) env('APP_URL', 'https://localhost'), PHP_URL_HOST) ?: 'localhost'));
        $fromName = env('MAIL_FROM_NAME', APP_NAME);
        $fromHeader = sprintf('%s <%s>', self::encodeHeader((string) $fromName), $fromEmail);

        $boundary = 'b_' . bin2hex(random_bytes(8));
        $headers = [
            'From: ' . $fromHeader,
            'Reply-To: ' . $fromEmail,
            'MIME-Version: 1.0',
            'X-Mailer: CONTAS',
        ];

        if ($htmlBody) {
            $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
            $body = "--{$boundary}\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
                . $textBody . "\r\n"
                . "--{$boundary}\r\n"
                . "Content-Type: text/html; charset=UTF-8\r\n\r\n"
                . $htmlBody . "\r\n"
                . "--{$boundary}--\r\n";
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $body = $textBody;
        }

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $ok = @mail($to, $encodedSubject, $body, implode("\r\n", $headers));

        // Fallback de diagnóstico em data/mail.log (não bloqueia o fluxo)
        self::log($to, $subject, $textBody, $ok);

        return (bool) $ok;
    }

    private static function encodeHeader(string $value): string
    {
        if (preg_match('/^[\x20-\x7E]+$/', $value)) {
            return $value;
        }
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private static function log(string $to, string $subject, string $body, bool $ok): void
    {
        $dir = dirname(__DIR__) . '/data';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $line = sprintf(
            "[%s] %s to=%s subject=%s\n%s\n---\n",
            date('c'),
            $ok ? 'SENT' : 'FAIL',
            $to,
            $subject,
            $body
        );
        @file_put_contents($dir . '/mail.log', $line, FILE_APPEND);
    }
}
