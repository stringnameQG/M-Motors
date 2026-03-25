<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Form\CommerceDossierType;
use App\Entity\Vehicule;
use App\Repository\VehiculeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\CloudinaryService;
use App\Service\FileValidator;

final class CommerceController extends AbstractController
{
    private string $subFolder;
    private int $vehiculePerPage;

    public function __construct() {
        $this->subFolder = "/clients/documents";
        $this->vehiculePerPage = 6;
    }

    #[Route('/vente/{page<\d+>?1}', name: 'app_vente')]
    public function vente(
        VehiculeRepository $vehiculeRepository, 
        int $page = 1
    ): Response
    {
        if($page < 1) $page = 1;
        
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('type', 'vente'))
            ->setFirstResult(($page - 1) * $this->vehiculePerPage)
            ->setMaxResults($this->vehiculePerPage);

        $vehicules = $vehiculeRepository->matching($criteria);

        $totalVehicule = count($vehiculeRepository->matching(Criteria::create()->where(Criteria::expr()->eq('type', 'vente'))));

        $totalPages = ceil($totalVehicule / $this->vehiculePerPage);
        return $this->render('commerce/index.html.twig', [
            'vehicules' => $vehicules,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'controller_name' => 'vente'
        ]);
    }

    #[Route('/location/{page<\d+>?1}', name: 'app_location')]
    public function location(
        VehiculeRepository $vehiculeRepository, 
        int $page = 1
    ): Response
    {
        if($page < 1) $page = 1;
        
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('type', 'location'))
            ->setFirstResult(($page - 1) * $this->vehiculePerPage)
            ->setMaxResults($this->vehiculePerPage);

        $vehicules = $vehiculeRepository->matching($criteria);

        $totalVehicule = count($vehiculeRepository->matching(Criteria::create()->where(Criteria::expr()->eq('type', 'location'))));

        $totalPages = ceil($totalVehicule / $this->vehiculePerPage);

        return $this->render('commerce/index.html.twig', [
            'vehicules' => $vehicules,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'controller_name' => 'location',
        ]);
    }

    #[Route('/commerce/vehicule/{id}', name: 'app_commerce_vehicule', methods: ['GET'])]
    public function vehicule(Vehicule $vehicule): Response
    {
        return $this->render('commerce/vehicule.html.twig', [
            'vehicule' => $vehicule,
        ]);
    }

    #[Route('/commerce/vehicule/dossier/{id}', name: 'app_commerce_vehicule_dossier', methods: ['GET', 'POST'])]
    public function dossier(
        Vehicule $vehicule,
        Request $request, 
        EntityManagerInterface $entityManager,
        FileValidator $fileValidator,
        CloudinaryService $cloudinary
    ): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $dossier = new Dossier();
        $form = $this->createForm(CommerceDossierType::class, $dossier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documentsFiles = $form->get('documentFiles')->getData();

            if (count($documentsFiles) > 10 ) {
                $this->addFlash('error', 'Limite de 10 documents atteinte.');
                return $this->render('commerce/dossier.html.twig', [
                    'vehicule' => $vehicule,
                    'form' => $form,
                ]);
            }

            if ($documentsFiles) {
                foreach ($documentsFiles as $file) {
                    if (!$fileValidator->validateImagePDF($file)) {
                        $this->addFlash('error', 'Seuls les fichiers JPEG, PNG ou WebP sont autorisés.');
                        return $this->render('commerce/dossier.html.twig', [
                            'vehicule' => $vehicule,
                            'form' => $form,
                        ]);
                    }
                }
                foreach ($documentsFiles as $file) {
                    $url = $cloudinary->upload($file, $this-> subFolder);
                    $dossier->addDocument($url);
                }
            }
            
            $dossier->setUser($user);
            $dossier->setVehicule($vehicule);
            $dossier->setType($vehicule->getType());
            $dossier->setStatut("en_cours");

            $entityManager->persist($dossier);
            $entityManager->flush();

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commerce/dossier.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form,
        ]);
    }
}
