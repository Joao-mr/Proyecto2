<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImagenRequest;
use App\Http\Requests\UpdateImagenRequest;
use App\Http\Requests\UploadImagenRequest;
use App\Models\Imagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImagenController extends Controller
{
    public function index(Request $request)
    {
        $query = Imagen::with('categoria');

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->boolean('random')) {
            $query->inRandomOrder();
        } else {
            $query->latest('id');
        }

        $imagenes = $query
            ->paginate(min((int) $request->get('per_page', 10), 1000))
            ->through(fn (Imagen $imagen) => $this->formatImagenPayload($imagen));

        return response()->json([
            'success' => true,
            'data' => $imagenes->items(),
            'pagination' => [
                'total' => $imagenes->total(),
                'per_page' => $imagenes->perPage(),
                'current_page' => $imagenes->currentPage(),
                'last_page' => $imagenes->lastPage(),
            ]
        ]);
    }

    public function store(StoreImagenRequest $request)
    {
        $imagen = Imagen::create($request->validated());
        $imagen->load('categoria');
        return response()->json([
            'success' => true,
            'data' => $this->formatImagenPayload($imagen),
        ], 201);
    }

    public function show(Imagen $imagen)
    {
        $imagen->load('categoria');

        return response()->json([
            'success' => true,
            'data' => array_merge(
                $this->formatImagenPayload($imagen),
                [
                    'all_media' => $imagen->getMedia('imagenes')->map(
                        fn ($mediaItem) => $this->formatMediaPayload($mediaItem)
                    )->values(),
                ]
            ),
        ]);
    }

    public function update(UpdateImagenRequest $request, Imagen $imagen)
    {
        $validated = $request->validated();

        if (array_key_exists('url', $validated) && $validated['url'] === null) {
            unset($validated['url']);
        }

        if (array_key_exists('respuesta_correcta', $validated) && $validated['respuesta_correcta'] === null) {
            unset($validated['respuesta_correcta']);
        }

        $imagen->update($validated);
        $imagen->load('categoria');
        return response()->json([
            'success' => true,
            'data' => $this->formatImagenPayload($imagen),
        ]);
    }

    public function destroy(Imagen $imagen)
    {
        $imagen->delete();
        return response()->json(null, 204);
    }

    public function getList()
    {
        $imagenes = Imagen::with('categoria')
            ->get()
            ->map(fn (Imagen $imagen) => $this->formatImagenPayload($imagen))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $imagenes,
            'count' => $imagenes->count()
        ]);
    }

    public function uploadImage(UploadImagenRequest $request, Imagen $imagen)
    {
        try {
            $mediaItem = $imagen->addMediaFromRequest('image')
                ->toMediaCollection('imagenes');

            return response()->json([
                'success' => true,
                'message' => 'Imagen subida exitosamente',
                'data' => [
                    'imagen_id' => $imagen->id,
                    'media_id' => $mediaItem->id,
                    'original_url' => $mediaItem->getUrl(),
                    'thumb_url' => $mediaItem->getUrl('thumb'),
                    'preview_url' => $mediaItem->getUrl('preview'),
                    'file_name' => $mediaItem->file_name,
                    'file_size' => $mediaItem->size,
                    'mime_type' => $mediaItem->mime_type
                ]
            ], 201);

        } catch (Throwable $exception) {
            return $this->mediaUploadErrorResponse($exception, 'Error al subir la imagen');
        }
    }

    public function storeWithUpload(UploadImagenRequest $request)
    {
        DB::beginTransaction();

        try {
            $imagen = Imagen::create([
                'url' => '',
                'respuesta_correcta' => $request->input('respuesta_correcta', ''),
                'categoria_id' => $request->input('categoria_id')
            ]);

            $mediaItem = $imagen->addMediaFromRequest('image')
                ->toMediaCollection('imagenes');

            $imagen->update([
                'url' => $mediaItem->getUrl()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Imagen creada y subida exitosamente',
                'data' => [
                    'imagen' => $this->formatImagenPayload($imagen->fresh(['categoria'])),
                    'media' => $this->formatMediaPayload($mediaItem),
                ]
            ], 201);

        } catch (Throwable $exception) {
            DB::rollBack();
            return $this->mediaUploadErrorResponse($exception, 'Error al crear y subir la imagen');
        }
    }

    public function getMediaInfo(Imagen $imagen)
    {
        $media = $imagen->getFirstMedia('imagenes');

        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'No hay imagen asociada',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $media->id,
                'imagen_id' => $imagen->id,
                'file_name' => $media->file_name,
                'file_path' => $media->file_path,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'size_formatted' => $this->formatBytes($media->size),
                'urls' => [
                    'original' => $media->getUrl(),
                    'thumb' => $media->getUrl('thumb'),
                    'preview' => $media->getUrl('preview'),
                ],
                'created_at' => $media->created_at,
                'updated_at' => $media->updated_at,
            ]
        ]);
    }

    public function getAllMedia(Imagen $imagen)
    {
        $allMedia = $imagen->getMedia('imagenes');

        return response()->json([
            'success' => true,
            'data' => [
                'imagen_id' => $imagen->id,
                'total_media' => $allMedia->count(),
                'media' => $allMedia->map(fn($m) => [
                    'id' => $m->id,
                    'file_name' => $m->file_name,
                    'size' => $m->size,
                    'size_formatted' => $this->formatBytes($m->size),
                    'mime_type' => $m->mime_type,
                    'urls' => [
                        'original' => $m->getUrl(),
                        'thumb' => $m->getUrl('thumb'),
                        'preview' => $m->getUrl('preview'),
                    ],
                    'created_at' => $m->created_at,
                ]),
            ]
        ]);
    }

    private function mediaUploadErrorResponse(Throwable $exception, string $message)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $exception->getMessage(),
        ], 500);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function formatImagenPayload(Imagen $imagen): array
    {
        $originalUrl = $imagen->getFirstMediaUrl('imagenes') ?: $imagen->url;

        return [
            'id' => $imagen->id,
            'url' => $imagen->url,
            'respuesta_correcta' => $imagen->respuesta_correcta,
            'categoria_id' => $imagen->categoria_id,
            'categoria_nombre' => $imagen->categoria?->nombre,
            'urls' => [
                'original' => $originalUrl,
                'thumb' => $imagen->getFirstMediaUrl('imagenes', 'thumb') ?: $originalUrl,
                'preview' => $imagen->getFirstMediaUrl('imagenes', 'preview') ?: $originalUrl,
            ],
            'has_media' => $imagen->hasMedia('imagenes'),
            'created_at' => $imagen->created_at,
            'updated_at' => $imagen->updated_at,
        ];
    }

    private function formatMediaPayload($media): array
    {
        return [
            'id' => $media->id,
            'file_name' => $media->file_name,
            'file_size' => $media->size,
            'size' => $media->size,
            'mime_type' => $media->mime_type,
            'urls' => [
                'original' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
                'preview' => $media->getUrl('preview'),
            ],
            'original_url' => $media->getUrl(),
            'thumb_url' => $media->getUrl('thumb'),
            'preview_url' => $media->getUrl('preview'),
            'created_at' => $media->created_at ?? null,
            'updated_at' => $media->updated_at ?? null,
        ];
    }
}
