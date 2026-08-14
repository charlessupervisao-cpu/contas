<?php
declare(strict_types=1);

/**
 * Upload de comprovantes PDF Conta+JE (receitas, despesas, extratos bancários).
 * Armazena em /uploads/comprovantes/{tipo}/
 */
final class DocumentProofUpload
{
    public const MAX_BYTES = 10_000_000; // Conta+JE §8.10: 10 MB

    public static function uploadDir(string $kind = 'geral'): string
    {
        $safe = preg_replace('/[^a-z0-9_-]/i', '', $kind) ?: 'geral';
        $dir = dirname(__DIR__) . '/uploads/comprovantes/' . $safe;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Não foi possível criar a pasta de comprovantes.');
        }
        return $dir;
    }

    /**
     * @return string|null caminho web /uploads/comprovantes/…
     */
    public static function save(array $file, string $kind, string $entityId, ?string $previousPath = null): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Falha no upload do comprovante (código ' . (int) $file['error'] . ').');
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException('O comprovante PDF deve ter no máximo 10 MB (Conta+JE).');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Arquivo de comprovante inválido.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        $name = strtolower((string) ($file['name'] ?? ''));
        $isPdf = $mime === 'application/pdf'
            || $mime === 'application/x-pdf'
            || str_ends_with($name, '.pdf');
        if (!$isPdf) {
            throw new RuntimeException('Envie apenas PDF como comprovante (Conta+JE §8.10 / §9).');
        }

        $safeKind = preg_replace('/[^a-z0-9_-]/i', '', $kind) ?: 'geral';
        $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $entityId) ?: 'doc';
        $dir = self::uploadDir($safeKind);
        $filename = $safeKind . '-' . $safeId . '-' . time() . '.pdf';
        $destAbs = $dir . '/' . $filename;
        $destWeb = '/uploads/comprovantes/' . $safeKind . '/' . $filename;

        if (!move_uploaded_file($tmp, $destAbs)) {
            throw new RuntimeException('Não foi possível salvar o comprovante PDF.');
        }
        @chmod($destAbs, 0644);
        self::deletePrevious($previousPath);
        return $destWeb;
    }

    public static function deletePrevious(?string $webPath): void
    {
        if (!$webPath || !str_starts_with($webPath, '/uploads/comprovantes/')) {
            return;
        }
        $abs = dirname(__DIR__) . $webPath;
        if (is_file($abs)) {
            @unlink($abs);
        }
    }

    public static function absolutePath(?string $webPath): ?string
    {
        if (!$webPath || !str_starts_with($webPath, '/uploads/comprovantes/')) {
            return null;
        }
        $abs = dirname(__DIR__) . $webPath;
        return is_file($abs) ? $abs : null;
    }
}
