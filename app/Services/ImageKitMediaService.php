<?php

namespace App\Services;

use App\Models\MediaAsset;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ImageKitMediaService
{
    /**
     * @throws RequestException
     */
    public function upload(UploadedFile $file, string $purpose = 'general'): MediaAsset
    {
        $privateKey = (string) config('services.imagekit.private_key');

        if ($privateKey === '') {
            throw new RuntimeException('ImageKit belum dikonfigurasi.');
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $fileName = Str::uuid()->toString().'.'.$extension;
        $folder = rtrim((string) config('services.imagekit.folder', '/180dc'), '/').'/'.$purpose;

        $response = Http::acceptJson()
            ->withBasicAuth($privateKey, '')
            ->timeout(60)
            ->attach('file', $file->get(), $fileName)
            ->post((string) config('services.imagekit.upload_url'), [
                'fileName' => $fileName,
                'folder' => $folder,
                'useUniqueFileName' => 'true',
            ])
            ->throw();

        $fileId = $response->json('fileId');
        $url = $response->json('url');

        if (! is_string($fileId) || ! is_string($url)) {
            throw new RuntimeException('Respons ImageKit tidak lengkap.');
        }

        $this->assertAllowedUrl($url);

        return MediaAsset::query()->create([
            'file_id' => $fileId,
            'url' => $url,
        ]);
    }

    private function assertAllowedUrl(string $url): void
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $allowed = array_map('strtolower', (array) config('services.imagekit.allowed_url_hosts', []));

        if ($host === '' || ($allowed !== [] && ! in_array($host, $allowed, true))) {
            throw new RuntimeException('URL media tidak termasuk whitelist.');
        }
    }
}
