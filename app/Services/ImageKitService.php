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
    public static function upload(UploadedFile|string $file, ?string $fileName = null): ?string
    {
        $privateKey = config('services.imagekit.private_key') ?? env('IMAGEKIT_PRIVATE_KEY');
        $folder = config('services.imagekit.folder', 'pos');

        if (empty($privateKey)) {
            return null;
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
                return $data['url'] ?? null;
            }

            Log::warning('ImageKit Upload Failed with status ' . $response->status(), [
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('ImageKit Exception: ' . $e->getMessage());
        }

        return null;
    }
}
