<?php

namespace App\Service;

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class CloudinaryService
{
  private UploadApi $upload;
  private string $baseFolder;

  public function __construct(
    string $cloudinaryCloudName,
    string $cloudinaryApiKey,
    string $cloudinaryApiSecret,
    ?UploadApi $uploadApi = null
  ) {
    $config = Configuration::instance();
    $config->cloud->cloudName = $cloudinaryCloudName;
    $config->cloud->apiKey = $cloudinaryApiKey;
    $config->cloud->apiSecret = $cloudinaryApiSecret;
    $config->url->secure = true;

    $this->baseFolder = "M-Motors";
    $this->upload = $uploadApi ?? new UploadApi();
  }

  public function upload(string $path, string $subFolder = ""): string
  {
    $result = $this->upload->upload($path, [
      'folder' => $this->baseFolder . $subFolder
    ]);

    return $result['secure_url'];
  }

  public function destroy(string $publicId): void
  {
    $this->upload->destroy($this->baseFolder . $publicId);
  }
}
