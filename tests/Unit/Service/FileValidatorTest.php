<?php

<<<<<<< HEAD
=======
// tests/Unit/Service/FileValidatorTest.php
>>>>>>> 1fbb4c78672f830759cfa07fa9cdb969b405f70a
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
      'valid_document.pdf',
      'application/pdf',
      null,
      true
    );

    $result = $this->fileValidator->validateImage($file);
    $this->assertFalse($result);
  }

  public function testValidateImagePDFWithValidMimeType(): void
  {
    $filePath = __DIR__.'/../fixtures/files/valid_document.pdf';
    if (!file_exists($filePath)) {
      $this->markTestSkipped('Le fichier de test est introuvable.');
    }

    $file = new UploadedFile (
      $filePath,
      'valid_document.pdf',
      'application/pdf',
      null,
      true
    );

    $result = $this->fileValidator->validateImagePDF($file);
    $this->assertTrue($result);
  }

  public function testValidateImagePDFWithInvalidMimeType(): void
  {
    $filePath = __DIR__.'/../fixtures/files/test_json.json';
    if (!file_exists($filePath)) {
      $this->markTestSkipped('Le fichier de test est introuvable.');
    }
        
    $file = new UploadedFile (
      $filePath,
      'test_json.json',
      'application/json',
      null,
      true
    );

    $result = $this->fileValidator->validateImagePDF($file);
    $this->assertFalse($result);
  }
}
