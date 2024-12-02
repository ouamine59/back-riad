<?php

namespace App\Controller;

use App\Entity\Ads;
use App\Entity\MediaObject;
use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

class MediaObjectController extends AbstractController
{
    #[Route('/api/upload', name: 'app_upload_image', methods: ['POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    public function upload(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        ManagerRegistry $doctrine,
        UploadHandler $uploadHandler
    ): Response {
        try {
            // Récupérer l'ID de l'annonce depuis le formulaire
            $productsId = $request->request->get('productsId');
            if (!$productsId) {
                return new JsonResponse(['error' => 'L\'ID du product est requis.'], 
                Response::HTTP_BAD_REQUEST);
            }

            // Récupérer l'entité Ads correspondante
            $product = $doctrine->getRepository(Products::class)->find($productsId);
            if (!$product) {
  
              return new JsonResponse(['error' => 'products none find'], 
                Response::HTTP_NOT_FOUND);
            }

            // Récupérer le fichier téléchargé
            $file = $request->files->get('filePath');
            if (!$file) {
                return new JsonResponse(['error' => 'Fichier non fourni'], 
                Response::HTTP_BAD_REQUEST);
            }

            $mediaObject = new MediaObject();
            $mediaObject->setFile($file);
            $mediaObject->setProducts($product);

            // Valider l'objet
            $errors = $validator->validate($mediaObject);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            // Enregistrer l'objet MediaObject
            $uploadHandler->upload($mediaObject, 'file');
            $entityManager->persist($mediaObject);
            $entityManager->flush();
            return new JsonResponse(
                ['message' => 'Image associée avec succès à l\'annonce.'],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => 
            $e->getMessage()],
             Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
