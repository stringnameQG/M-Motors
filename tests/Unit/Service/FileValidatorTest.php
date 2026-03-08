<?php

// tests/Unit/Service/FileValidatorTest.php
namespace App\Tests\Unit\Service;

use App\Service\FileValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileValidatorTest extends KernelTestCase
{
  private FileValidator $fileValidator;

  protected function setUp(): void
  {
    $this->fileValidator = new FileValidator();
  }

  public function testValidateImageWithValidMimeType(): void
  {
    $filePath = __DIR__.'/../fixtures/files/test_image.jpg';
    if (!file_exists($filePath)) {
      $this->markTestSkipped('Le fichier de test est introuvable.');
    }

    $file = new UploadedFile (
      $filePath,
      'test_image.jpg',
      'image/jpeg',
      null,
      true
    );

    $result = $this->fileValidator->validateImage($file);
    $this->assertTrue($result);
  }

  public function testValidateImageWithInvalidMimeType(): void
  {
    $filePath = __DIR__.'/../fixtures/files/valid_document.pdf';
    if (!file_exists($filePath)) {
      $this->markTestSkipped('Le fichier de test est introuvable.');
    }
        
    $file = new UploadedFile (
      $filePath,
      'test_image.jpg',
      'image/jpeg',
      null,
      true
    );

    $result = $this->fileValidator->validateImage($file);
    $this->assertFalse($result);
  }
}
