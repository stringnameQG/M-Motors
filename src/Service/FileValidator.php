<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileValidator
{
    public function validateImage(UploadedFile $file): bool
    {
        return in_array($this->getRealMimeType($file), ['image/jpeg', 'image/png', 'image/webp']);
    }
    public function validateImagePDF(UploadedFile $file): bool
    {
        return in_array($this->getRealMimeType($file), ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);
    }

    private function getRealMimeType(UploadedFile $file): string
    {
        return (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
    }
}
