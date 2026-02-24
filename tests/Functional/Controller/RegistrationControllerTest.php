<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
  public function testRegistrationPageLoads(): void
  {
    $client = static::createClient();
    $client->request('GET', '/register');

    $this->assertResponseIsSuccessful();
  }

  public function testUserCanRegister(): void
  {
    $client = static::createClient();

    $entityManager = $client->getContainer()->get('doctrine')->getManager();
    $entityManager->createQuery('DELETE FROM App\Entity\User')->execute();

    $crawler = $client->request('GET', '/register');

    $form = $crawler->selectButton('Register')->form([
      'registration_form[email]' => 'functional@example.com',
      'registration_form[plainPassword]' => 'password123',
      'registration_form[agreeTerms]' => true,
    ]);

    $client->submit($form);

    $this->assertResponseRedirects('/login');
  }
}
