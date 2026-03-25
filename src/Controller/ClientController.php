<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Repository\DossierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientController extends AbstractController
{
    #[Route('/client/dossier', name: 'app_client_dossier_index')]
    public function indexDossier(DossierRepository $dossierRepository): Response
    {
        $user = $this->getUser();
    
        if (!$user) return $this->redirectToRoute('app_login');

        return $this->render('client/dossier_index.html.twig', [
            'dossiers' => $dossierRepository->findBy(['user' => $user]),
        ]);
    }
    
    #[Route('/client/dossier/{id}', name: 'app_client_dossier')]
    public function dossier(Dossier $dossier): Response
    {
        $user = $this->getUser();
    
        if (!$user) return $this->redirectToRoute('app_login');

        return $this->render('client/dossier_show.html.twig', [
            'dossier' => $dossier,
        ]);
    }
}
