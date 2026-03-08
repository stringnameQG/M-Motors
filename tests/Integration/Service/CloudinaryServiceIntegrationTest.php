<?php

namespace App\Tests\Integration\Service;

use App\Service\CloudinaryService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Dotenv\Dotenv;

class CloudinaryServiceIntegrationTest extends KernelTestCase
{
    private CloudinaryService $cloudinaryService;
    private array $uploadedPublicIds = [];

    protected function setUp(): void
    {
        self::bootKernel();
        $this->cloudinaryService = self::$kernel
            ->getContainer()
            ->get(CloudinaryService::class);
    }

    protected function tearDown(): void
    {
        foreach ($this->uploadedPublicIds as $publicId) {
            try {
                $this->cloudinaryService->deleteImage($publicId);
            } catch (\Exception $e) { }
        }
        $this->uploadedPublicIds = [];
    }

    public function testUploadImage(): void
    {
        $file = new UploadedFile(
            __DIR__.'/../../Unit/fixtures/files/test_image.jpg',
            'test_image.jpg',
            'image/jpeg',
            null,
            true
        );

        $url = $this->cloudinaryService->uploadImage($file, 'test_image_integration');
        $this->assertStringContainsString('cloudinary.com', $url);
        $this->assertStringContainsString('vehicules/images/test_image_integration', $url);

        $this->uploadedPublicIds[] = 'test_image_integration';
    }

    public function testUploadPdf(): void
    {
        $file = new UploadedFile(
            __DIR__.'/../../Unit/fixtures/files/valid_document.pdf',
            'valid_document.pdf',
            'application/pdf',
            null,
            true
        );

        $email = 'test.client@example.com';
        $url = $this->cloudinaryService->uploadPdf($file, $email, 'test_pdf_integration');
        $this->assertStringContainsString('cloudinary.com', $url);
        $this->assertStringContainsString('clients/pdfs/test_pdf_integration', $url);
        $url = $this->cloudinaryService->uploadPdf($file, $email);
        $this->assertStringContainsString('test_client_example_com', $url);
        
        $this->uploadedPublicIds[] = 'test_pdf_integration';
    }

    public function testDeleteImage(): void
    {
        $file = new UploadedFile(
            __DIR__.'/../../Unit/fixtures/files/test_image.jpg',
            'test_image.jpg',
            'image/jpeg',
            null,
            true
        );

        $publicId = 'test_delete_image';
        $this->cloudinaryService->uploadImage($file, $publicId);
        $this->uploadedPublicIds[] = $publicId;

        $this->cloudinaryService->deleteImage($publicId);

    $this->assertTrue(true);
  }

  public function testDeletePdf(): void
  {
    $file = new UploadedFile(
      __DIR__.'/../../Unit/fixtures/files/valid_document.pdf',
      'valid_document.pdf',
      'application/pdf',
      null,
      true
    );

    $publicId = 'test_delete_pdf';
    $this->cloudinaryService->uploadPdf($file, 'test.client@example.com', $publicId);
    $this->uploadedPublicIds[] = $publicId;

    $this->cloudinaryService->deletePdf($publicId);
    $this->assertTrue(true);
  }
}