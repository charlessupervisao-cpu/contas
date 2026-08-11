<?php
declare(strict_types=1);

/** Upload do PDF do contrato do cabo (armazenado em /uploads/contracts). */
final class ContractPdfUpload
{
    public const MAX_BYTES = 8_000_000;

    public static function uploadDir(): string
    {
        $dir = dirname(__DIR__) . '/uploads/contracts';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Não foi possível criar a pasta uploads/contracts.');
        }
        return $dir;
    }

    /** @return string|null caminho relativo web (/uploads/contracts/…) */
    public static function save(array $file, string $contractId, ?string $previousPath = null): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Falha no upload do PDF (código ' . (int) $file['error'] . ').');
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException('O PDF do contrato deve ter no máximo 8 MB.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Arquivo de contrato inválido.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        $name = strtolower((string) ($file['name'] ?? ''));
        $isPdf = $mime === 'application/pdf'
            || $mime === 'application/x-pdf'
            || str_ends_with($name, '.pdf');
        if (!$isPdf) {
            throw new RuntimeException('Envie apenas arquivo PDF do contrato.');
        }

        $dir = self::uploadDir();
        $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $contractId) ?: 'contrato';
        $filename = 'contrato-' . $safeId . '-' . time() . '.pdf';
        $destAbs = $dir . '/' . $filename;
        $destWeb = '/uploads/contracts/' . $filename;

        if (!move_uploaded_file($tmp, $destAbs)) {
            throw new RuntimeException('Não foi possível salvar o PDF do contrato.');
        }
        @chmod($destAbs, 0644);
        self::deletePrevious($previousPath);
        return $destWeb;
    }

    public static function deletePrevious(?string $webPath): void
    {
        if (!$webPath || !str_starts_with($webPath, '/uploads/contracts/')) {
            return;
        }
        $abs = dirname(__DIR__) . $webPath;
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
}
