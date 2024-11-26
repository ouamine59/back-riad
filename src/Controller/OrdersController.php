<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Repository\OrdersRepository;
use App\Repository\ProductsRepository;
use App\Repository\RowsOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $ordersData = array_map(function ($order) {
            return [
                'id' => $order->getId(),
                'states' => $order->getStates(),
                'userId' => $order->getUser(),
                'isCreatedAt' => $order->getIsCreatedAt()
            ];
        }, $result);

        if ($result) {
            return new Response(
                json_encode(['result' => $ordersData]),
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
#[Route('/detail/{idUser}/{idOrder}', name: 'app_client_orders_detail', methods: ["GET"])]
#[IsGranted(new Expression('is_granted("ROLE_CLIENT")'))]
public function detailClient(
    int $idUser, 
    int $idOrder, 
    OrdersRepository $ordersRepository,
    ProductsRepository $productsRepository,
    RowsOrderRepository $rowsOrderRepository): Response
{
    try {
        $user = $ordersRepository->findOneBy(array("user" => $idUser));
        if (!$user) {
            return new JsonResponse(
                ['result' => 'no user'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $result = $ordersRepository->findOneByUser($idUser, $idOrder);

        if ($result == null) {
            return new JsonResponse(
                ['result' => "no order"],
                Response::HTTP_BAD_REQUEST
            );
        }

        $orderData = array_map(function ($order) {
            return [
                'id' => $order->getId(),
                'states' => $order->getStates(),
                'userId' => $order->getUser(),
                'isCreatedAt' => $order->getIsCreatedAt()
            ];
        }, $result); // Conversion de PersistentCollection en tableau

        // Récupération des produits dans la commande
        $products = $ordersRepository->find($idOrder)->getRowsOrders()->toArray(); // Conversion ici également
        foreach ($products as $product) {
            $row = $rowsOrderRepository->findOneBy(array("orders"=>$idOrder, "products"=>$product->getId()));
            $prod = $productsRepository->findOneBy(['id' => $product->getId()]);
            if ($prod) {
                $orderData['products'][] = [
                    "title" => $prod->getTitle(),
                    "description" => $prod->getDescription(),
                    "amount"=>$row->getAmount(),
                    "price"=>$row->getPrice()
                ];
            }
        }
        return new Response(
            json_encode(['result' => $orderData]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    } catch (\Exception $e) {
        return new JsonResponse(
            ['result' => 'Database error', 'error' => $e->getMessage()],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}


#[Route('/delete/{idUser}/{idOrder}', name: 'app_client_orders_delete', methods: ["DELETE"])]
#[IsGranted(new Expression('is_granted("ROLE_CLIENT")'))]
public function deleteByClient(
    int $idUser,
    int $idOrder,
    OrdersRepository $ordersRepository,
    EntityManagerInterface $entityManager,
    RowsOrderRepository $rowsOrderRepository
): Response {
    try {
       $order = $ordersRepository->findOneBy(['id' => $idOrder, 'user' => $idUser]);

        if (!$order) {
            return new JsonResponse(
                ['result' => 'Order not found'],
                Response::HTTP_BAD_REQUEST
            );
        }
        $createdAt = $order->getIsCreatedAt();
        $currentDate = new \DateTime();
        $interval = $createdAt->diff($currentDate);

        if ($interval->days <= 14) {
            $rowsOrders = $rowsOrderRepository->findBy(['orders' => $idOrder]);

            foreach ($rowsOrders as $rowOrder) {
                $order->removeRowsOrder($rowOrder);
                $entityManager->remove($rowOrder);
            }
            $entityManager->remove($order);
            $entityManager->flush();

            return new JsonResponse(
                ['result' => 'Order deleted successfully'],
                Response::HTTP_OK
            );
        } else {
            return new JsonResponse(
                ['result' => 'Order cannot be deleted. It is older than 14 days.'],
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
