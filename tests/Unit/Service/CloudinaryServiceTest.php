<?php

namespace App\Tests\Service;

use App\Service\CloudinaryService;
use Cloudinary\Cloudinary;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Upload\UploadApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class CloudinaryServiceTest extends TestCase
{
  private $cloudinaryMock;
  private $uploadApiMock;
  private CloudinaryService $service;
  private $apiResponseMock;

  protected function setUp(): void
{
    $this->uploadApiMock = $this->createMock(UploadApi::class);
    $this->cloudinaryMock = $this->createMock(Cloudinary::class);
    $this->cloudinaryMock->method('uploadApi')->willReturn($this->uploadApiMock);

    $this->apiResponseMock = $this->createStub(ApiResponse::class);

    $this->service = new CloudinaryService(
            'derejrikc',
            '397213699967617',
            'XUy8QfE6MXiU_BKch0excfomIEQ',
            'cloudinary://397213699967617:XUy8QfE6MXiU_BKch0excfomIEQ@derejrikc');

    $reflection = new \ReflectionClass($this->service);
    $property = $reflection->getProperty('cloudinary');
    $property->setAccessible(true);
    $property->setValue($this->service, $this->cloudinaryMock);
}

  public function testUploadImage(): void
  {
    $file = new UploadedFile(
      __DIR__.'/../fixtures/files/test_image.jpg',
      'test_image.jpg',
      'image/jpeg',
      null,
      true
    );

    $this->apiResponseMock
    ->method('offsetGet')
    ->willReturnCallback(function ($key) {
    $mockData = [
        'secure_url' => 'https://mock.cloudinary.com/vehicules/images/test.jpg',
    ];
    return $mockData[$key] ?? null;
  });

    $this->uploadApiMock
        ->method('upload')
        ->willReturn($this->apiResponseMock);

    $url = $this->service->uploadImage($file);

    $this->assertStringContainsString('mock.cloudinary.com', $url);
  }

  public function testUploadPdf(): void
  {
      $file = new UploadedFile(
          __DIR__.'/../fixtures/files/valid_document.pdf',
          'valid_document.pdf',
          'application/pdf',
          null,
          true
      );

      $publicId = 'client_test_com';

      $this->apiResponseMock
          ->method('offsetGet')
          ->willReturnMap([
              ['secure_url', 'https://mock.cloudinary.com/clients/pdfs/' . $publicId . '.pdf']
          ]);

      $this->uploadApiMock
    ->expects($this->once())
    ->method('upload')
    ->with(
        $this->anything(),
        $this->callback(function ($options) use ($publicId) {
            return $options['folder'] === 'clients/pdfs'
                && $options['public_id'] === $publicId
                && $options['resource_type'] === 'raw'
                && $options['overwrite'] === true;
        })
    )
    ->willReturn($this->apiResponseMock);

      $url = $this->service->uploadPdf($file, 'client@test.com', $publicId);

      $this->assertStringContainsString('mock.cloudinary.com', $url);
      $this->assertStringContainsString($publicId, $url);
  }
  
  public function testDeleteImage(): void
  {
    $this->uploadApiMock->expects($this->once())
      ->method('destroy')
      ->with('vehicules/images/test_id');

    $this->service->deleteImage('test_id');
  }

  public function testDeletePdf(): void
  {
    $this->uploadApiMock->expects($this->once())
      ->method('destroy')
      ->with('clients/pdfs/test_pdf', ['resource_type' => 'raw']);

    $this->service->deletePdf('test_pdf');
  }
}
