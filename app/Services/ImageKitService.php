<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageKitService
{
    /**
     * Upload file atau image URL ke ImageKit.io.
     * Mengembalikan URL CDN publik (contoh: https://ik.imagekit.io/p0v1tjsucu/pos/...)
     * atau null jika gagal.
     */
    public static function upload(UploadedFile|string $file, ?string $fileName = null, ?string $folderName = null): ?string
    {
        $result = self::uploadDetailed($file, $fileName, $folderName);
        return $result['url'] ?? null;
    }

    /**
     * Upload file atau image URL ke ImageKit.io dengan metadata lengkap.
     *
     * @return array{success: bool, url: ?string, file_id?: ?string, file_name?: ?string, file_path?: ?string, thumbnail_url?: ?string, error?: string, data?: array}
     */
    public static function uploadDetailed(UploadedFile|string $file, ?string $fileName = null, ?string $folderName = null): array
    {
        $rawKey = config('services.imagekit.private_key') ?? env('IMAGEKIT_PRIVATE_KEY');
        $privateKey = trim((string) $rawKey, "\"'\t\n\r ");
        $folder = $folderName ?? config('services.imagekit.folder', 'pos');

        if (empty($privateKey)) {
            Log::warning('ImageKit Private Key is empty. Please check .env or config/services.php.');
            return [
                'success' => false,
                'url' => null,
                'error' => 'ImageKit private key tidak dikonfigurasi di file .env (IMAGEKIT_PRIVATE_KEY) atau config cache belum di-refresh (jalankan php artisan optimize:clear).'
            ];
        }

        try {
            $name = $fileName ?? ('prod_' . Str::random(8) . '_' . time());

            if ($file instanceof UploadedFile) {
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                if (!str_ends_with(strtolower($name), '.' . strtolower($ext))) {
                    $name .= '.' . $ext;
                }

                $response = Http::timeout(25)
                    ->withBasicAuth($privateKey, '')
                    ->attach('file', file_get_contents($file->getRealPath()), $name)
                    ->post('https://upload.imagekit.io/api/v1/files/upload', [
                        'fileName' => $name,
                        'folder' => '/' . trim($folder, '/'),
                        'useUniqueFileName' => 'true',
                    ]);
            } else {
                // Berupa URL gambar atau base64
                $response = Http::timeout(25)
                    ->withBasicAuth($privateKey, '')
                    ->asMultipart()
                    ->post('https://upload.imagekit.io/api/v1/files/upload', [
                        'file' => $file,
                        'fileName' => $name,
                        'folder' => '/' . trim($folder, '/'),
                        'useUniqueFileName' => 'true',
                    ]);
            }

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'url' => $data['url'] ?? null,
                    'file_id' => $data['fileId'] ?? null,
                    'file_name' => $data['name'] ?? $name,
                    'file_path' => $data['filePath'] ?? null,
                    'thumbnail_url' => $data['thumbnailUrl'] ?? null,
                    'data' => $data,
                ];
            }

            Log::warning('ImageKit Upload Failed with status ' . $response->status(), [
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'url' => null,
                'error' => 'ImageKit menolak upload dengan status ' . $response->status() . ': ' . $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('ImageKit Exception: ' . $e->getMessage());

            return [
                'success' => false,
                'url' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
