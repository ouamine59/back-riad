<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/products')]
class ProductsController extends AbstractController
{
    #[Route('/listing', name: 'app_visitor_products_listing', methods:["GET"])]
    public function index(ProductsRepository $productsRepository, 
    CategoriesRepository $categoriesRepository): Response
    {
        try {
            $result = $productsRepository->findBy(["isActivied" => 1]);

            $adsData = array_map(function ($product) {
                $mediaObjects = $product->getMediaObjects();
            $imagePaths = [];

            // Si plusieurs media objects sont associés
            foreach ($mediaObjects as $media) {
                $imagePaths = $media->getFilePath(); // Assurez-vous que getFilePath() existe
            }
               
              //  $categorie = $categoriesRepository->findOneBy(['id' => $product->getCategories()]);
                return [
                    'id'            => $product->getId(),
                    'title'         => $product->getTitle(),
                    'price'         => $product->getPrice(),
                    'discount'      => $product->isDiscount(),
                    "priceDiscount" => $product->getPriceDiscount(),
                    "description"   => $product->getDescription(),
                    "image"         => $imagePaths,
                   // "categorie"     => $categorie
                ];
            }, $result);
            if ($result) {
                return new Response(
                    json_encode(['result' => $adsData]),
                    Response::HTTP_OK,
                    ['Content-Type' => 'application/json']
                );
            } else {
                return new JsonResponse(
                    ['result' => 'no product'],
                    Response::HTTP_BAD_REQUEST
                );
            }
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/admin/create', name: 'app_admin_products_create', methods:["POST"])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    public function create(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Request $request,
        CategoriesRepository $categoriesRepository
    ): Response {
        try {
            $data = $request->getContent();
            // Traite les données (par exemple, décoder le JSON si nécessaire)
            $jsonData = json_decode($data, true);
            if (!isset($jsonData['title']) or !isset($jsonData['price']) or !isset($jsonData['discount']) or !isset($jsonData['priceDiscount']) or !isset($jsonData['description']) or !isset($jsonData['isActivied']) or !isset($jsonData['categoriesId'])) {
                return new JsonResponse(
                    ['result' => 'data missing'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $categorie = $categoriesRepository->find($jsonData['categoriesId']);
            if (!$categorie) {
                return new JsonResponse(
                    ['result' => 'categories missing'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $product = new Products();
            $product->setTitle($jsonData['title']);
            $product->setPrice($jsonData['price']);
            $product->setDiscount($jsonData['discount']);
            $product->setPriceDiscount($jsonData['priceDiscount']);
            $product->setDescription($jsonData['description']);
            $product->setActivied($jsonData['isActivied']);
            $product->setCategories($categorie);
            $errors = $validator->validate($product);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                return new JsonResponse(
                    ['result' => $errorMessages],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $entityManager->persist($product);
            $entityManager->flush();
            return new Response(
                json_encode(['result' => 'product created successfully', 'id' => $product->getId()]),
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/admin/update/{productsId}', name: 'app_admin_products_update', methods:["PUT"])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    public function update(
        int $productsId,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Request $request,
        ProductsRepository $productsRepository,
        CategoriesRepository $categoriesRepository
    ): Response {
        try {
            $data = $request->getContent();
            // Traite les données (par exemple, décoder le JSON si nécessaire)
            $jsonData = json_decode($data, true);
            if (!isset($jsonData['title']) or !isset($jsonData['id']) or !isset($jsonData['price']) or !isset($jsonData['discount']) or !isset($jsonData['priceDiscount']) or !isset($jsonData['description']) or !isset($jsonData['isActivied']) or !isset($jsonData['categoriesId'])) {
                return new JsonResponse(
                    ['result' => 'data missing'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $categorie = $categoriesRepository->find($jsonData['categoriesId']);
if (!$categorie) {
    return new JsonResponse(
        ['result' => 'categories missing'],
        Response::HTTP_BAD_REQUEST
    );
}

$product = $productsRepository->findOneBy(['id' => $productsId]);
if (!$product) {
    return new JsonResponse(
        ['result' => 'product none find'],
        Response::HTTP_BAD_REQUEST
    );
}
            $product->setTitle($jsonData['title']);
            $product->setPrice($jsonData['price']);
            $product->setDiscount($jsonData['discount']);
            $product->setPriceDiscount($jsonData['priceDiscount']);
            $product->setDescription($jsonData['description']);
            $product->setActivied($jsonData['isActivied']);
            $product->setCategories($categorie);
            $errors = $validator->validate($product);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                return new JsonResponse(
                    ['result' => $errorMessages],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $entityManager->persist($product);
            $entityManager->flush();
            return new Response(
                json_encode(['result' => 'product updated successfully', 'id' => $product->getId()]),
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/admin/states/update/{productsId}/{states}', name: 'app_admin_products_states_update', methods:["PUT"])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    public function states(
        int $productsId,
        int $states,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        ProductsRepository $productsRepository,
    ): Response {
        try {
            // Traite les données (par exemple, décoder le JSON si nécessaire)
            $product = $productsRepository->findOneBy(['id' => $productsId]);
            if (!$product) {
                return new JsonResponse(
                    ['result' => 'product none find'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            if ($states != 0 and $states != 1) {
                return new JsonResponse(
                    ['result' => 'states none correct'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $product->setActivied($states);

            $errors = $validator->validate($product);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                return new JsonResponse(
                    ['result' => $errorMessages],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $entityManager->persist($product);
            $entityManager->flush();
            return new JsonResponse(
                ['result' => 'State on the product updated successfully', 'id' => $product->getId()],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/admin/listing', name: 'app_admin_products_listing', methods:["GET"])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    public function listing(
        ProductsRepository $productsRepository,
    ): Response {
        try {
            // Traite les données (par exemple, décoder le JSON si nécessaire)
            $result = $productsRepository->findAllForAdmin();
            if (empty($result)) {
                return new JsonResponse(
                    ['result' => 'product none find'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $productsData = array_map(function ($product) {
                return [
                    'id'         => $product->getId(),
                    'title'      => $product->getTitle(),
                    'isActivied' => $product->getActivied(),
                ];
            }, $result);
            return new Response(
                json_encode(['result' => $productsData]),
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
