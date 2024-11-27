<?php

namespace App\Controller;

use DateTime;
use App\Entity\Orders;
use App\Entity\RowsOrder;
use DateTimeImmutable;
use App\Repository\UserRepository;
use App\Repository\OrdersRepository;
use App\Repository\StatesRepository;
use App\Repository\ProductsRepository;
use App\Repository\RowsOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

#[Route('/create/{idUser}', name: 'app_client_orders_create', methods: ["POST"])]
#[IsGranted(new Expression('is_granted("ROLE_CLIENT")'))]
public function create(
    int $idUser,

    
    EntityManagerInterface $entityManager,
    ProductsRepository $productsRepository,
    UserRepository $userRepository,
    Request $request,
    StatesRepository $statesRepository,
    ValidatorInterface $validator
): Response {
    try {
       $user = $userRepository->findOneBy(['id' => $idUser]);
       
        if (!$user) {
            return new JsonResponse(
                ['result' => 'User not found'],
                Response::HTTP_BAD_REQUEST
            );
        }
        $data = $request->getContent();
        $jsonData = json_decode($data, true);

            // Vérifier la présence des clés nécessaires dans les données JSON
            if (!$jsonData || !isset($jsonData['products'])) {
                return new JsonResponse(
                    ['result' => 'Invalid data provided'],
                    Response::HTTP_BAD_REQUEST
                );
            }
      
           $order = new Orders(); 
           $order->setUser($user) ;
           $states = $statesRepository->find(1);
           $order->setStates($states) ; 
           $order->setIsCreatedAt(new DateTimeImmutable());
            //enregistrememnt des produits
            foreach($jsonData['products'] as $product){
                
                $prod = $productsRepository->find($product['productsId']);
                if($prod==null){
                    return new JsonResponse(
                        ['result' => 'Product none available'],
                        Response::HTTP_BAD_REQUEST
                    );
                }
                $row = new RowsOrder();
                $row->setOrders($order);
                $row->setProducts($prod);
                $row->setAmount($product['amount']);
                $price = ($prod->getDiscount()==1)? $prod->getPriceDiscount():$prod->getPrice();
                $row->setPrice($price);
            }

           $errors = $validator->validate($order);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                return new JsonResponse(
                    ['result' => 'Validation failed', 'errors' => $errorMessages],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $entityManager->persist($order);
            $entityManager->persist($row);
            $entityManager->flush();
            return new JsonResponse(
                ['result' => 'Order registered successfully'],
                Response::HTTP_CREATED
            );
        
    } catch (\Exception $e) {
        return new JsonResponse(
            ['result' => 'Database error', 'error' => $e->getMessage()],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}


#[Route('/admin/listing', name: 'app_admin_orders_listing', methods: ["GET"])]
#[IsGranted(new Expression('is_granted("ROLE_CLIENT")'))]
public function listingAdmin(OrdersRepository $ordersRepository): Response {
    try {
       $result = $ordersRepository->findAllForAdmin();
       
        if (!$result) {
            return new JsonResponse(
                ['result' => 'orders not found'],
                Response::HTTP_BAD_REQUEST
            );
        }
        $ordersData = array_map(function ($order) {
            return [
  

                'id' => $order->getId(),
                'isCreatedAt' => $order->getCreatedAt(),
                'firstName' => $order->getFirstName(),
                'lastName'=>$order->getLastName(),
                "states"=>$order->getStates()
            ];
        }, $result);
            return new JsonResponse(
                ['result' => $ordersData],
                Response::HTTP_CREATED
            );
        
    } catch (\Exception $e) {
        return new JsonResponse(
            ['result' => 'Database error', 'error' => $e->getMessage()],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
}
