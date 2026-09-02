<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadMediaRequest;
use App\Http\Resources\MediaAssetResource;
use App\Services\ImageKitMediaService;
use Illuminate\Http\JsonResponse;
use Throwable;

class AdminMediaController extends Controller
{
    public function __construct(
        private readonly ImageKitMediaService $mediaService,
    ) {}

    public function store(UploadMediaRequest $request): JsonResponse
    {
        if (! config('services.imagekit.private_key')) {
            return response()->json([
                'status' => 'error',
                'message' => 'ImageKit belum dikonfigurasi pada server.',
                'data' => null,
            ], 503);
        }

        try {
            $media = $this->mediaService->upload(
                $request->file('file'),
                $request->validated('purpose', 'general'),
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => 'Gambar gagal diunggah. Silakan coba kembali.',
                'data' => null,
            ], 502);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Gambar berhasil diunggah.',
            'data' => new MediaAssetResource($media),
        ], 201);
    }
}
