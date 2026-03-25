<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CGUContollerTest extends WebTestCase {
  private KernelBrowser $client;

  protected function setUp(): void {
    $this->client    = static::createClient();
  }

  public function testPage(): void {
    $this->client->followRedirects();
    $this->client->request('GET', "/cgu");

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Conditions Générales d\'Utilisation');
  }
}