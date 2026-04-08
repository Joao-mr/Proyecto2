<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImagenRequest;
use App\Http\Requests\UpdateImagenRequest;
use App\Http\Requests\UploadImagenRequest;
use App\Http\Resources\ImagenResource;
use App\Models\Imagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImagenController extends Controller
{
    
     //obtiene lista de imagenes con URLs de Spatie Media Library 
    public function index()
    {
        $imagenes = Imagen::paginate(10)->through(fn($imagen) => [
            'id' => $imagen->id,
            'respuesta_correcta' => $imagen->respuesta_correcta,
            'urls' => [
                'original' => $imagen->getFirstMediaUrl('imagenes'),
                'thumb' => $imagen->getFirstMediaUrl('imagenes', 'thumb'),
                'preview' => $imagen->getFirstMediaUrl('imagenes', 'preview'),
            ],
            'has_media' => $imagen->hasMedia('imagenes'),
            'created_at' => $imagen->created_at,
            'updated_at' => $imagen->updated_at,
        ]);

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
        return response()->json($imagen, 201);
    }

    
     //obtener una imagen espec├¡fica con todas sus URLs y datos
    public function show(Imagen $imagen)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $imagen->id,
                'respuesta_correcta' => $imagen->respuesta_correcta,
                'urls' => [
                    'original' => $imagen->getFirstMediaUrl('imagenes'),
                    'thumb' => $imagen->getFirstMediaUrl('imagenes', 'thumb'),
                    'preview' => $imagen->getFirstMediaUrl('imagenes', 'preview'),
                ],
                'has_media' => $imagen->hasMedia('imagenes'),
                'all_media' => $imagen->getMedia('imagenes')->map(fn($m) => [
                    'id' => $m->id,
                    'file_name' => $m->file_name,
                    'size' => $m->size,
                    'mime_type' => $m->mime_type,
                    'urls' => [
                        'original' => $m->getUrl(),
                        'thumb' => $m->getUrl('thumb'),
                        'preview' => $m->getUrl('preview'),
                    ]
                ]),
                'created_at' => $imagen->created_at,
                'updated_at' => $imagen->updated_at,
            ]
        ]);
    }

    public function update(UpdateImagenRequest $request, Imagen $imagen)
    {
        $imagen->update($request->validated());
        return response()->json($imagen);
    }

    public function destroy(Imagen $imagen)
    {
        $imagen->delete();
        return response()->json(null, 204);
    }

     //obtener lista simple de todas las im├ígenes con URLs
    public function getList()
    {
        $imagenes = Imagen::all()->map(fn($imagen) => [
            'id' => $imagen->id,
            'respuesta_correcta' => $imagen->respuesta_correcta,
            'url' => $imagen->getFirstMediaUrl('imagenes'),
            'thumb_url' => $imagen->getFirstMediaUrl('imagenes', 'thumb'),
            'preview_url' => $imagen->getFirstMediaUrl('imagenes', 'preview'),
            'has_media' => $imagen->hasMedia('imagenes'),
            'created_at' => $imagen->created_at,
        ]);

        return response()->json([
            'success' => true,
            'data' => $imagenes,
            'count' => $imagenes->count()
        ]);
    }

    //sube imagen a un modelo de imagen existente con spatie media library
    public function uploadImage(UploadImagenRequest $request, Imagen $imagen)
    {
        try {
            // coge el archivo del request, lo guarda en el disco, lo registra en la tabla media, lo asocia con el modelo imagen
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

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

     //crea la imagen y sube archivo en una sola petici├│n
    public function storeWithUpload(UploadImagenRequest $request)
    {
        DB::beginTransaction();

        try {
            // crea la imagen con la respuesta correcta
            $imagen = Imagen::create([
                'respuesta_correcta' => $request->respuesta_correcta
            ]);

            // sube la imagen
            $mediaItem = $imagen->addMediaFromRequest('image')
                ->toMediaCollection('imagenes');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Imagen creada y subida exitosamente',
                'data' => [
                    'imagen' => [
                        'id' => $imagen->id,
                        'respuesta_correcta' => $imagen->respuesta_correcta,
                        'created_at' => $imagen->created_at
                    ],
                    'media' => [
                        'id' => $mediaItem->id,
                        'original_url' => $mediaItem->getUrl(),
                        'thumb_url' => $mediaItem->getUrl('thumb'),
                        'preview_url' => $mediaItem->getUrl('preview'),
                        'file_name' => $mediaItem->file_name,
                        'file_size' => $mediaItem->size,
                        'mime_type' => $mediaItem->mime_type
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al crear y subir la imagen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

     //obtiene informacion completa de media de una imagen
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

     //Obtiene todas las im├ígenes con informacion detallada
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

     //formatea bytes a formato legible
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
