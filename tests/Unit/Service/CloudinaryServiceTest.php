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
    $this->apiResponseMock = $this->createStub(ApiResponse::class);

    $this->service = new CloudinaryService(
        'derejrikc',
        '397213699967617',
        'XUy8QfE6MXiU_BKch0excfomIEQ',
        $this->uploadApiMock // Injection du mock
    );
}

  public function testUpload(): void
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

    $url = $this->service->upload($file);

    $this->assertStringContainsString('mock.cloudinary.com', $url);
  }
  
  public function testDelete(): void
  {
    $this->uploadApiMock->expects($this->once())
      ->method('destroy')
      ->with('M-Motors/vehicules/images/test_id');

    $this->service->destroy('/vehicules/images/test_id');
  }
}
