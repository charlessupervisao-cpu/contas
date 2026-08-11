<?php
declare(strict_types=1);

/**
 * Upload da foto do deputado (retrato 3:4).
 * Tamanho alvo de armazenamento: 600 × 800 px.
 */
final class PhotoUpload
{
    public const TARGET_W = 600;
    public const TARGET_H = 800;
    public const MAX_BYTES = 2_500_000;
    public const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function uploadDir(): string
    {
        $dir = dirname(__DIR__) . '/uploads/candidates';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Não foi possível criar a pasta uploads/candidates.');
        }
        return $dir;
    }

    /** @return string|null caminho relativo web (/uploads/candidates/…) */
    public static function saveCandidatePhoto(array $file, string $campaignId, ?string $previousPath = null): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Falha no upload da foto (código ' . (int) $file['error'] . ').');
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException('A foto deve ter no máximo 2,5 MB.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Arquivo de foto inválido.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        if (!isset(self::ALLOWED[$mime])) {
            throw new RuntimeException('Use JPG, PNG ou WebP.');
        }
        $ext = self::ALLOWED[$mime];

        $dir = self::uploadDir();
        $filename = 'candidato-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $campaignId) . '-' . time() . '.' . $ext;
        $destAbs = $dir . '/' . $filename;
        $destWeb = '/uploads/candidates/' . $filename;

        if (!extension_loaded('gd')) {
            if (!move_uploaded_file($tmp, $destAbs)) {
                throw new RuntimeException('Não foi possível salvar a foto.');
            }
        } else {
            self::resizeAndSave($tmp, $mime, $destAbs);
        }

        @chmod($destAbs, 0644);
        self::deletePrevious($previousPath);
        return $destWeb;
    }

    public static function deletePrevious(?string $webPath): void
    {
        if (!$webPath || !str_starts_with($webPath, '/uploads/candidates/')) {
            return;
        }
        $abs = dirname(__DIR__) . $webPath;
        if (is_file($abs)) {
            @unlink($abs);
        }
    }

    private static function resizeAndSave(string $tmp, string $mime, string $destAbs): void
    {
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmp),
            'image/png' => @imagecreatefrompng($tmp),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmp) : false,
            default => false,
        };
        if ($src === false) {
            if (!move_uploaded_file($tmp, $destAbs)) {
                throw new RuntimeException('Não foi possível processar a foto.');
            }
            return;
        }

        $sw = imagesx($src);
        $sh = imagesy($src);
        $tw = self::TARGET_W;
        $th = self::TARGET_H;

        // Crop central na proporção 3:4 e redimensiona para 600×800
        $targetRatio = $tw / $th;
        $srcRatio = $sw / max(1, $sh);
        if ($srcRatio > $targetRatio) {
            $cropH = $sh;
            $cropW = (int) round($sh * $targetRatio);
            $sx = (int) max(0, ($sw - $cropW) / 2);
            $sy = 0;
        } else {
            $cropW = $sw;
            $cropH = (int) round($sw / $targetRatio);
            $sx = 0;
            $sy = (int) max(0, ($sh - $cropH) / 2);
        }

        $dst = imagecreatetruecolor($tw, $th);
        if ($dst === false) {
            imagedestroy($src);
            throw new RuntimeException('Falha ao criar imagem redimensionada.');
        }
        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $tw, $th, $cropW, $cropH);

        $ok = match ($mime) {
            'image/jpeg' => imagejpeg($dst, $destAbs, 88),
            'image/png' => imagepng($dst, $destAbs, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($dst, $destAbs, 86) : imagejpeg($dst, preg_replace('/\.webp$/', '.jpg', $destAbs) ?: $destAbs, 88),
            default => false,
        };

        imagedestroy($src);
        imagedestroy($dst);

        if (!$ok) {
            throw new RuntimeException('Não foi possível gravar a foto redimensionada.');
        }
    }
}
