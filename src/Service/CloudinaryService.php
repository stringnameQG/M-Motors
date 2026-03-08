<?php

namespace App\Service;

use Cloudinary\Cloudinary;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CloudinaryService
{
  private $cloudinary;

  public function __construct(
    string $cloudinaryCloudName,
    string $cloudinaryApiKey,
    string $cloudinaryApiSecret
  ) {
    $cloudinaryUrl = sprintf(
      'cloudinary://%s:%s@%s',
      $cloudinaryApiKey,
      $cloudinaryApiSecret,
      $cloudinaryCloudName
    );
      $this->cloudinary = new Cloudinary($cloudinaryUrl);
  }
    
  public function uploadImage(UploadedFile $file, ?string $publicId = null): string
  {
    $result = $this->cloudinary->uploadApi()->upload(
      $file->getPathname(),
      [
        'public_id' => $publicId,
        'folder' => 'vehicules/images'
      ]
    );

    return $result['secure_url'];
  }

  public function uploadPdf(UploadedFile $file, string $clientEmail, ?string $publicId = null): string
  {
    $pdfPublicId = $publicId ?? uniqid('pdf_') . '_' . str_replace(['@', '.'], '_', $clientEmail);
    $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
      'folder' => 'clients/pdfs',
      'public_id' => $pdfPublicId,
      'resource_type' => 'raw',
      'overwrite' => true,
    ]);
    return $result['secure_url'];
  }

  public function deleteImage(string $publicId): void
  {
    $this->cloudinary->uploadApi()->destroy('vehicules/images/' . $publicId);
  }

  public function deletePdf(string $publicId): void
  {
    $this->cloudinary->uploadApi()->destroy('clients/pdfs/' . $publicId, ['resource_type' => 'raw']);
  }
}