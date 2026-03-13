<?php

namespace App\Controller;

use App\Entity\Dossier;
use App\Form\DossierType;
use App\Repository\DossierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CloudinaryService;
use App\Service\FileValidator;

#[Route('/dossier')]
final class DossierController extends AbstractController
{
    private string $subFolder;

    public function __construct()
    {
        $this->subFolder = "/clients/documents";
    }

    #[Route(name: 'app_dossier_index', methods: ['GET'])]
    public function index(DossierRepository $dossierRepository): Response
    {
        return $this->render('dossier/index.html.twig', [
            'dossiers' => $dossierRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_dossier_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        FileValidator $fileValidator,
        CloudinaryService $cloudinary
    ): Response
    {
        $dossier = new Dossier();
        $form = $this->createForm(DossierType::class, $dossier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documentsFiles = $form->get('documentFiles')->getData();
            $user = $this->getUser();
    
            if (!$user) {
                return $this->redirectToRoute('app_login');
            }

            if (count($documentsFiles) > 10 ) {
                $this->addFlash('error', 'Limite de 10 documents atteinte.');
                return $this->render('dossier/new.html.twig', [
                    'dossier' => $dossier,
                    'form' => $form,
                ]);
            }

            if ($documentsFiles) {
                foreach ($documentsFiles as $file) {
                    if (!$fileValidator->validateImagePDF($file)) {
                        $this->addFlash('error', 'Seuls les fichiers JPEG, PNG ou WebP sont autorisés.');
                        return $this->render('dossier/new.html.twig', [
                            'dossier' => $dossier,
                            'form' => $form,
                        ]);
                    }
                }
                foreach ($documentsFiles as $file) {
                    $url = $cloudinary->upload($file, $this->subFolder);
                    $dossier->addDocument($url);
                }
            }
            
            $dossier->setUser($user);
            $dossier->setType($dossier->getVehicule()->getType());
            $dossier->setStatut("en_cours");

            $entityManager->persist($dossier);
            $entityManager->flush();

            return $this->redirectToRoute('app_dossier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dossier/new.html.twig', [
            'dossier' => $dossier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_dossier_show', methods: ['GET'])]
    public function show(Dossier $dossier): Response
    {
        return $this->render('dossier/show.html.twig', [
            'dossier' => $dossier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_dossier_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Dossier $dossier, 
        EntityManagerInterface $entityManager,
        CloudinaryService $cloudinary, 
        FileValidator $fileValidator
    ): Response
    {
        $form = $this->createForm(DossierType::class, $dossier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documentsFiles = $form->get('documentFiles')->getData();

            if ((count($dossier->getDocuments()) + count($documentsFiles)) > 10) {
                $this->addFlash('error', 'Limite de 10 documents atteinte.');
                return $this->render('dossier/edit.html.twig', [
                    'dossier' => $dossier,
                    'form' => $form,
                ]);
            }

            if ($documentsFiles) {
                foreach ($documentsFiles as $file) {
                    if (!$fileValidator->validateImagePDF($file)) {
                        $this->addFlash('error', 'Seuls les fichiers JPEG, PNG WebP ou PDF sont autorisés.');
                        return $this->render('dossier/edit.html.twig', [
                            'dossier' => $dossier,
                            'form' => $form,
                        ]);
                    }
                }
                foreach ($documentsFiles as $file) {
                    $url = $cloudinary->upload($file, $this->subFolder);
                    $dossier->addDocument($url);
                }
            }
            $entityManager->persist($dossier);
            $entityManager->flush();

            return $this->redirectToRoute('app_dossier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dossier/edit.html.twig', [
            'dossier' => $dossier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_dossier_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        Dossier $dossier, 
        EntityManagerInterface $entityManager,
        CloudinaryService $cloudinary
    ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dossier->getId(), $request->getPayload()->getString('_token'))) {
            foreach( $dossier->getDocuments() as $documentUrl) {
                $publicId = $this->recoverId($documentUrl);
                $cloudinary->destroy($publicId);
            }
            $entityManager->remove($dossier);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_dossier_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete-document/{index}', name: 'app_dossier_delete_document', methods: ['POST'])]
    public function deleteDocument(
        Request $request,
        Dossier $dossier,
        int $index,
        EntityManagerInterface $entityManager,
        CloudinaryService $cloudinary
    ): Response {

        if (!$this->isCsrfTokenValid('delete_document'.$dossier->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_dossier_index', [], Response::HTTP_SEE_OTHER);
        }
    
        $documents = $dossier->getDocuments();
        if (!isset($documents[$index])) {
            $this->addFlash('error', 'Photo non trouvée.');
            return $this->redirectToRoute('app_dossier_edit', ['id' => $dossier->getId()]);
        }

        $documentUrl = $documents[$index];
        $publicId = $this->recoverId($documentUrl);
        $cloudinary->destroy($publicId);
        $dossier->removeDocument($documentUrl);

        $entityManager->persist($dossier);
        $entityManager->flush();

        $this->addFlash('success', 'Document supprimée avec succès.');
        return $this->redirectToRoute('app_dossier_edit', ['id' => $dossier->getId()]);
    }

    private function recoverId(string $documentUrl): string 
    {
        $folderIsolate = strstr($documentUrl, $this->subFolder);
    
        $ext = strstr($folderIsolate, ".");
    
        $publicId = str_replace($ext, "", $folderIsolate);

        return $publicId;
    }
}
