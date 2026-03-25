<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CGUContoller extends AbstractController
{
    #[Route('/cgu', name: 'app_cgu_contoller')]
    public function index(): Response
    {
        return $this->render('cgu_contoller/index.html.twig', [
            'controller_name' => 'CGU',
        ]);
    }
}
