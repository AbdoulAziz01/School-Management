<?php

namespace App\Traits;

use finfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

trait SecureUpload
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/gif'       => 'gif',
        'image/webp'      => 'webp',
        'application/pdf' => 'pdf',
    ];

    protected function storeSecurely(UploadedFile $file, string $path): string
    {
        $realMimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());

        if (! array_key_exists($realMimeType, self::ALLOWED_MIME_TYPES)) {
            throw new RuntimeException("Forbidden file type: {$realMimeType}");
        }

        $filename = Str::uuid()->toString().'.'.self::ALLOWED_MIME_TYPES[$realMimeType];

        return $file->storeAs($path, $filename, ['disk' => 'local']);
    }
}
