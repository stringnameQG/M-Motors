<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CloudinaryService;
use App\Service\FileValidator;
use Doctrine\Common\Collections\Criteria;

#[Route('/vehicule')]
final class VehiculeController extends AbstractController
{
    private string $subFolder;
    private int $vehiculePerPage;

    public function __construct()
    {
        $this->subFolder = "/vehicules/images";
        $this->vehiculePerPage = 10;
    }

    #[Route('/{page<\d+>?1}', name: 'app_vehicule_index', methods: ['GET'])]
    public function index(
        VehiculeRepository $vehiculeRepository, 
        int $page = 1
    ): Response
    {
        if($page < 1) $page = 1;
        
        $criteria = Criteria::create()
            ->setFirstResult(($page - 1) * $this->vehiculePerPage)
            ->setMaxResults($this->vehiculePerPage);

        $vehicules = $vehiculeRepository->matching($criteria);

        $totalVehicule = count($vehiculeRepository->matching($criteria));

        $totalPages = ceil($totalVehicule / $this->vehiculePerPage);

        return $this->render('vehicule/index.html.twig', [
            'vehicules' => $vehicules,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    #[Route('/new', name: 'app_vehicule_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        FileValidator $fileValidator,
        CloudinaryService $cloudinary
    ): Response
    {

    $vehicule = new Vehicule();
    $form = $this->createForm(VehiculeType::class, $vehicule);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $photosFiles = $form->get('photosFiles')->getData();

        if (count($photosFiles) > 10 ) {
            $this->addFlash('error', 'Limite de 10 photos atteinte.');
            return $this->render('vehicule/new.html.twig', [
                'vehicule' => $vehicule,
                'form' => $form,
            ]);
        }

        if ($photosFiles) {
            foreach ($photosFiles as $file) {
                if (!$fileValidator->validateImage($file)) {
                    $this->addFlash('error', 'Seuls les fichiers JPEG, PNG ou WebP sont autorisés.');
                    return $this->render('vehicule/new.html.twig', [
                        'vehicule' => $vehicule,
                        'form' => $form,
                    ]);
                }
            }
            foreach ($photosFiles as $file) {
                $url = $cloudinary->upload($file, $this->subFolder);
                $vehicule->addCollectionPhotoLien($url);
            }
        }
        $entityManager->persist($vehicule);
        $entityManager->flush();

        return $this->redirectToRoute('app_vehicule_index', [], Response::HTTP_SEE_OTHER);
    }

        return $this->render('vehicule/new.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vehicule_show', methods: ['GET'])]
    public function show(Vehicule $vehicule): Response
    {
        return $this->render('vehicule/show.html.twig', [
            'vehicule' => $vehicule,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vehicule_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Vehicule $vehicule, 
        EntityManagerInterface $entityManager,
        CloudinaryService $cloudinary, 
        FileValidator $fileValidator
    ): Response
    {
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photosFiles = $form->get('photosFiles')->getData();

            if ((count($vehicule->getCollectionPhotoLien()) + count($photosFiles)) > 10) {
                $this->addFlash('error', 'Limite de 10 photos atteinte.');
                return $this->render('vehicule/edit.html.twig', [
                    'vehicule' => $vehicule,
                    'form' => $form,
                ]);
            }

            if ($photosFiles) {
                foreach ($photosFiles as $file) {
                    if (!$fileValidator->validateImage($file)) {
                        $this->addFlash('error', 'Seuls les fichiers JPEG, PNG ou WebP sont autorisés.');
                        return $this->render('vehicule/edit.html.twig', [
                            'vehicule' => $vehicule,
                            'form' => $form,
                        ]);
                    }
                }
                foreach ($photosFiles as $file) {
                    $url = $cloudinary->upload($file, $this->subFolder);
                    $vehicule->addCollectionPhotoLien($url);
                }
            }

            $entityManager->persist($vehicule);
            $entityManager->flush();

            return $this->redirectToRoute('app_vehicule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vehicule/edit.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vehicule_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        Vehicule $vehicule, 
        EntityManagerInterface $entityManager
    ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vehicule->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($vehicule);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_vehicule_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete-photo/{index}', name: 'app_vehicule_delete_photo', methods: ['POST'])]
    public function deletePhoto(
        Request $request,
        Vehicule $vehicule,
        int $index,
        EntityManagerInterface $entityManager,
        CloudinaryService $cloudinary
    ): Response {

        if (!$this->isCsrfTokenValid('delete_photo'.$vehicule->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_vehicule_index', [], Response::HTTP_SEE_OTHER);
        }
    
        $photos = $vehicule->getCollectionPhotoLien();
        if (!isset($photos[$index])) {
            $this->addFlash('error', 'Photo non trouvée.');
            return $this->redirectToRoute('app_vehicule_edit', ['id' => $vehicule->getId()]);
        }

        $photoUrl = $photos[$index];

        $publicId = $this->recoverId($photoUrl);

        $cloudinary->destroy($publicId);

        $vehicule->removeCollectionPhotoLien($photoUrl);

        $entityManager->persist($vehicule);
        $entityManager->flush();

        $this->addFlash('success', 'Photo supprimée avec succès.');
        return $this->redirectToRoute('app_vehicule_edit', ['id' => $vehicule->getId()]);
    }

    private function recoverId(string $photoUrl): string 
    {
        $folderIsolate = strstr($photoUrl, $this->subFolder);
    
        $ext = strstr($folderIsolate, ".");
    
        $publicId = str_replace($ext, "", $folderIsolate);

        return $publicId;
    }
}