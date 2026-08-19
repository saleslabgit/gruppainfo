<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\UploadedFile;

final class UploadedDocumentMime
{
    /** @var list<string> */
    public const ALLOWED = ['application/pdf', 'image/jpeg', 'image/png'];

    public static function detect(UploadedFile $file): string
    {
        $detector = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $detector->file($file->getRealPath());

        return is_string($mimeType) ? $mimeType : 'application/octet-stream';
    }
}
