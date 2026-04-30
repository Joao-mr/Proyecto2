<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class GameImageCatalog
{
    public const ROOT_DIRECTORY = 'images/Imagenes sala';

    private const CATEGORY_BY_FOLDER = [
        'deportes' => 'Deportes',
        'geografia' => 'Geografia',
        'peliculas' => 'Peliculas',
        'videojuegos' => 'Videojuegos',
    ];

    public static function records(): array
    {
        $records = [];

        foreach (self::CATEGORY_BY_FOLDER as $folder => $categoryName) {
            $folderPath = public_path(self::ROOT_DIRECTORY . '/' . $folder);

            if (! File::isDirectory($folderPath)) {
                continue;
            }

            $files = File::files($folderPath);
            usort($files, static fn ($left, $right) => strnatcasecmp($left->getFilename(), $right->getFilename()));

            foreach ($files as $file) {
                $filename = $file->getFilename();

                $records[] = [
                    'folder' => $folder,
                    'category_name' => $categoryName,
                    'url' => self::buildPublicUrl($folder, $filename),
                    'respuesta_correcta' => pathinfo($filename, PATHINFO_FILENAME),
                ];
            }
        }

        return $records;
    }

    private static function buildPublicUrl(string $folder, string $filename): string
    {
        $segments = ['images', 'Imagenes sala', $folder, $filename];

        return '/' . implode('/', array_map(static fn (string $segment) => rawurlencode($segment), $segments));
    }
}