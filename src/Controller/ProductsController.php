<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
#[Route('/api/products')]
class ProductsController extends AbstractController
{
    #[Route('/listing', name: 'app_visitor_products_listing', methods:["GET"])]
    public function index(ProductsRepository $productsRepository): Response
    {
        try {
            $result = $productsRepository->findBy(array("isActivied"=>1));

            $adsData = array_map(function ($product) {
                return [
                    'id' => $product->getId(),
                    'title' => $product->getTitle(),
                    'price' => $product->getPrice(),
                    'discount' => $product->isDiscount(),
                    "priceDiscount"=>$product->getPriceDiscount(),
                    "description"=>$product->getDescription(),
                    "image" =>$product->getMediaObjects()
                ];
            }, $result);
            if ($result ) {
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
    public function create(EntityManagerInterface $entityManager,
    ValidatorInterface $validator, Request $request,
    CategoriesRepository $categoriesRepository): Response
    {
        try {
            $data = $request->getContent();
            // Traite les données (par exemple, décoder le JSON si nécessaire)
            $jsonData = json_decode($data, true);
            if (!isset($jsonData['title']) or 
            !isset($jsonData['price']) or 
            !isset($jsonData['discount']) or 
            !isset($jsonData['priceDiscount']) or 
            !isset($jsonData['description']) or 
            !isset($jsonData['isActivied']) or 
            !isset($jsonData['categoriesId'])) {
                return new JsonResponse(
                    ['result' => 'data missing'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $categorie = $categoriesRepository->find($jsonData['categoriesId']);
            if(!$categorie){
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
                json_encode(['result' => 'Ad created successfully', 'id'=>$product->getId()]),
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
