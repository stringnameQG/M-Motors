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
                $this->cloudinaryService->destroy($publicId);
            } catch (\Exception $e) { }
        }
        $this->uploadedPublicIds = [];
    }

    public function testUpload(): void
    {
        $file = new UploadedFile(
            __DIR__.'/../../Unit/fixtures/files/test_image.jpg',
            'test_image.jpg',
            'image/jpeg',
            null,
            true
        );

        $url = $this->cloudinaryService->upload($file, '/test_image_integration');
        $this->assertStringContainsString('cloudinary.com', $url);
        $this->assertStringContainsString('M-Motors/test_image_integration', $url);

        $this->uploadedPublicIds[] = 'test_image_integration';
    }

    public function testDelete(): void
    {
        $file = new UploadedFile(
            __DIR__.'/../../Unit/fixtures/files/test_image.jpg',
            'test_image.jpg',
            'image/jpeg',
            null,
            true
        );

        $publicId = 'test_delete_image';
        $this->cloudinaryService->upload($file, $publicId);
        $this->uploadedPublicIds[] = $publicId;

        $this->cloudinaryService->destroy($publicId);

    $this->assertTrue(true);
  }
}