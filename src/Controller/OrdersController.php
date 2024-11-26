<?php

namespace App\Controller;

use App\Repository\OrdersRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
#[Route('/api/orders')]
class OrdersController extends AbstractController
{
    #[Route('/listing/{idUser}', name: 'app_client_orders_listing')]
    #[IsGranted(new Expression('is_granted("ROLE_CLIENT")'))]
    public function index(int $idUser, OrdersRepository $ordersRepository): Response
{
    try {
        $user = $ordersRepository->find( $idUser);
         if($user!=null){
             return new JsonResponse(
                 ['result' => "no user"],
                 Response::HTTP_BAD_REQUEST
             );
         }
        $result = $ordersRepository->findAllByIdUser( $idUser);

        $adsData = array_map(function ($order) {
            return [
                'id' => $order->getId(),
                'states' => $order->getStates(),
                'userId' => $order->getUser(),
                'isCreatedAt' => $order->getIsCreatedAt()
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
                ['result' => 'no orders'],
                Response::HTTP_BAD_REQUEST
            );
        }
    } catch (\Exception $e) {
        return new JsonResponse(
            ['result' => 'Database error', 'error' => $e->getMessage()],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
}