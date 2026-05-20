<?php

namespace App\Support;

use App\Models\School;
use Illuminate\Http\UploadedFile;

class SchoolLogoStorage
{
    public static function store(School $school, UploadedFile $file): void
    {
        $contents = file_get_contents($file->getRealPath());
        if ($contents === false) {
            throw new \RuntimeException('Impossible de lire le fichier logo.');
        }

        $school->update([
            'logo_data' => base64_encode($contents),
            'logo_mime' => $file->getMimeType() ?: 'image/png',
            'logo_path' => null,
        ]);
    }

    public static function clear(School $school): void
    {
        $school->update([
            'logo_data' => null,
            'logo_mime' => null,
            'logo_path' => null,
        ]);
    }

    public static function dataUri(School $school): ?string
    {
        if (! $school->logo_data || ! $school->logo_mime) {
            return null;
        }

        return 'data:'.$school->logo_mime.';base64,'.$school->logo_data;
    }
}
