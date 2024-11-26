<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
