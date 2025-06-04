<?php

namespace App\Controller;

use App\Entity\Document;
use App\Form\DocumentForm;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[Route('/document')]
final class DocumentController extends AbstractController
{
    #[Route(name: 'app_document_index', methods: ['GET'])]
    public function index(Request $request, DocumentRepository $documentRepository): Response
    {
        $user = $this->getUser();
        $searchTerm = $request->query->get('search', '');

        if (!empty($searchTerm)) {
            $documents = $documentRepository->findByTitleAndUser($searchTerm, $user);
        } else {
            $documents = $documentRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
        }

        // If this is an AJAX request, return only the document cards
        if ($request->isXmlHttpRequest()) {
            return $this->render('document/_search_results.html.twig', [
                'documents' => $documents,
                'searchTerm' => $searchTerm,
            ]);
        }

        return $this->render('document/index.html.twig', [
            'documents' => $documents,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/new', name: 'app_document_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $document = new Document();
        $form = $this->createForm(DocumentForm::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('filePath')->getData();
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                try {
                    $uploadedFile->move(
                        $this->getParameter('documents_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle error (e.g., add a flash message or log)
                }

                $document->setFilePath($newFilename);
            }

            $document->setUser($this->getUser());
            $document->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($document);
            $entityManager->flush();

            return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('document/new.html.twig', [
            'document' => $document,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_document_show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        if ($document->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('document/show.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_document_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Document $document, EntityManagerInterface $entityManager): Response
    {
        if ($document->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(DocumentForm::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('document/edit.html.twig', [
            'document' => $document,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_document_delete', methods: ['POST'])]
    public function delete(Request $request, Document $document, EntityManagerInterface $entityManager): Response
    {
        if ($document->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($document);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id<\d+>}/share', name: 'app_document_share', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function share(Request $request, Document $document, EntityManagerInterface $em): Response
    {
        if ($document->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            $token = Uuid::v4()->toRfc4122();
            $expiresAt = new \DateTimeImmutable('+24 hours');
            $maxUses = (int) $request->request->get('max_uses', 1);
            $document->setSharedToken($token);
            $document->setSharedTokenExpiresAt($expiresAt);
            $document->setSharedTokenMaxUses($maxUses);
            $document->setSharedTokenUses(0);
            $em->flush();
            $this->addFlash('success', 'Sharing link generated!');
            return $this->redirectToRoute('app_document_show', ['id' => $document->getId()]);
        }
        return $this->render('document/share.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route('/shared/{token}', name: 'app_document_shared', methods: ['GET'])]
    public function sharedDownload(string $token, EntityManagerInterface $em): Response
    {
        $document = $em->getRepository(Document::class)->findOneBy(['sharedToken' => $token]);
        if (!$document) {
            return $this->render('document/shared_error.html.twig', [
                'message' => 'Invalid or expired link.'
            ]);
        }
        $now = new \DateTimeImmutable();
        if (
            !$document->getSharedTokenExpiresAt() ||
            $document->getSharedTokenExpiresAt() < $now ||
            ($document->getSharedTokenMaxUses() !== null && $document->getSharedTokenUses() >= $document->getSharedTokenMaxUses())
        ) {
            return $this->render('document/shared_error.html.twig', [
                'message' => 'This sharing link has expired or reached its usage limit.'
            ]);
        }
        $document->setSharedTokenUses($document->getSharedTokenUses() + 1);
        $em->flush();
        $filePath = $this->getParameter('documents_directory') . DIRECTORY_SEPARATOR . $document->getFilePath();
        return $this->file($filePath, $document->getTitle());
    }

    #[Route('/{id<\d+>}/revoke-share', name: 'app_document_revoke_share', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function revokeShare(Document $document, EntityManagerInterface $em): Response
    {
        if ($document->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $document->setSharedToken(null);
        $document->setSharedTokenExpiresAt(null);
        $document->setSharedTokenMaxUses(null);
        $document->setSharedTokenUses(null);
        $em->flush();
        $this->addFlash('success', 'Sharing link revoked.');
        return $this->redirectToRoute('app_document_show', ['id' => $document->getId()]);
    }
}
