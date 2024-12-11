<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\RowsOrder;
use App\Repository\OrdersRepository;
use App\Repository\ProductsRepository;
use App\Repository\RowsOrderRepository;
use App\Repository\StatesRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/orders')]
class OrdersController extends AbstractController
{
    #[Route('/listing/{idUser}', name: 'app_client_orders_listing')]
    #[IsGranted(new Expression('is_granted("ROLE_CLIENT")'))]
    public function index(int $idUser, OrdersRepository $ordersRepository): Response
    {
        try {
            $user = $ordersRepository->find($idUser);
            if ($user === null) { // Vérifie si aucun utilisateur n'est trouvé
                return new JsonResponse(
                    ['result' => "no user"],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $result = $ordersRepository->findAllByIdUser($idUser);

            $ordersData = array_map(function ($order) {
                return [
                    'id'          => $order->getId(),
                    'states'      => $order->getStates(),
                    'userId'      => $order->getUser(),
                    'isCreatedAt' => $order->getIsCreatedAt()
                ];
            }, $result);

            if ($result) { // Vérifie si $result contient des données
                return new Response(
                    json_encode(['result' => $ordersData]),
                    Response::HTTP_OK,
                    ['Content-Type' => 'application/json']
                );
            }else {
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
        RowsOrderRepository $rowsOrderRepository
    ): Response {
        try {
            $user = $ordersRepository->findOneBy(["user" => $idUser]);
            if (!$user) {
                return new JsonResponse(
                    ['result' => 'no user'],
                    Response::HTTP_BAD_REQUEST
                );
            }
    
            $result = $ordersRepository->findOneByUser($idUser, $idOrder);
            if (empty($result)) {
                return new JsonResponse(
                    ['result' => 'no order'],
                    Response::HTTP_BAD_REQUEST
                );
            }
    
            $order = $ordersRepository->find($idOrder);
            if (!$order) {
                return new JsonResponse(
                    ['result' => 'order not found'],
                    Response::HTTP_BAD_REQUEST
                );
            }
    
            $orderData = array_map(function ($order) use ($productsRepository, $rowsOrderRepository, $idOrder) {
                $productsData = [];
                foreach ($order->getRowsOrders() as $rowOrder) {
                    $product = $productsRepository->find($rowOrder->getProducts()->getId());
                    if (!$product) {
                        continue; // Ignorez ce produit si non trouvé
                    }
                    $productsData[] = [
                        "title"       => $product->getTitle(),
                        "description" => $product->getDescription(),
                        "amount"      => $rowOrder->getAmount(),
                        "price"       => $rowOrder->getPrice(),
                    ];
                }
    
                return [
                    'id'          => $order->getId(),
                    'states'      => $order->getStates()?->getName(),
                    'userId'      => $order->getUser()?->getId(),
                    'isCreatedAt' => $order->getIsCreatedAt()?->format('c'),
                    'products'    => $productsData,
                ];
            }, $result);
    
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
            $createdAt   = $order->getIsCreatedAt();
            $currentDate = new \DateTime();
            $interval    = $createdAt->diff($currentDate);

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
            $data     = $request->getContent();
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
            foreach ($jsonData['products'] as $product) {

                $prod = $productsRepository->find($product['productsId']);
                if ($prod == null) {
                    return new JsonResponse(
                        ['result' => 'Product none available'],
                        Response::HTTP_BAD_REQUEST
                    );
                }
                $row = new RowsOrder();
                $row->setOrders($order);
                $row->setProducts($prod);
                $row->setAmount($product['amount']);
                $price = ($prod->getDiscount() == 1) ? $prod->getPriceDiscount() : $prod->getPrice();
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
    public function listingAdmin(OrdersRepository $ordersRepository): Response
    {
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


                    'id'          => $order->getId(),
                    'isCreatedAt' => $order->getCreatedAt(),
                    'firstName'   => $order->getFirstName(),
                    'lastName'    => $order->getLastName(),
                    "states"      => $order->getStates()
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

    #[Route('/admin/detail/{ordersId}', name: 'app_admin_orders_detail', methods: ["GET"])]
#[IsGranted(new Expression('is_granted("ROLE_CLIENT")'))]
public function detailAdmin(
    int $ordersId,
    OrdersRepository $ordersRepository,
    RowsOrderRepository $rowOrdersRepository,
    ProductsRepository $productsRepository
): Response {
    try {
        // Récupération de l'entité Order
        $orderEntity = $ordersRepository->find($ordersId);
        if (!$orderEntity) {
            return new JsonResponse(
                ['result' => 'Order not found'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Vérification des données spécifiques pour l'administration
        $result = $ordersRepository->findOneForAdmin($ordersId);
        if (!$result) {
            return new JsonResponse(
                ['result' => 'No admin data found for the order'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Construction des données de réponse
        $orderData = array_map(function ($orderDto) use ($orderEntity, $rowOrdersRepository, $productsRepository) {
            $productsData = [];

            // Vérification des relations RowsOrders
            if ($orderEntity->getRowsOrders() === null || $orderEntity->getRowsOrders()->isEmpty()) {
                return ['error' => 'No rows orders associated with the order'];
            }

            foreach ($orderEntity->getRowsOrders() as $rowOrder) {
                // Vérification de la relation Products
                $product = $rowOrder->getProducts();
                if ($product === null) {
                    continue; // Ignore les rows sans produit
                }

                $productEntity = $productsRepository->find($product->getId());
                if ($productEntity) {
                    $productsData[] = [
                        'title' => $productEntity->getTitle(),
                        'description' => $productEntity->getDescription(),
                        'amount' => $rowOrder->getAmount(),
                        'price' => $rowOrder->getPrice(),
                    ];
                }
            }

            // Vérification des données DTO
            return [
                'id' => $orderDto->id ?? null,
                'isCreatedAt' => isset($orderDto->isCreatedAt) ? $orderDto->isCreatedAt->format('c') : null,
                'firstName' => $orderDto->firstName ?? null,
                'lastName' => $orderDto->lastName ?? null,
                'states' => $orderDto->states ?? null,
                'products' => $productsData,
            ];
        }, $result);

        return new JsonResponse(
            ['result' => $orderData],
            Response::HTTP_OK
        );

    } catch (\Exception $e) {
        // Capture et retour d'erreur détaillée
        return new JsonResponse(
            [
                'result' => 'An error occurred while processing the request',
                'error' => $e->getMessage()
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}

    #[Route('/admin/states/update/{ordersId}/{statesId}', name: 'app_admin_orders_update_states', methods: ["PUT"])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    public function updateStateAdmin(
        int $ordersId,
        int $statesId,
        OrdersRepository $ordersRepository,
        StatesRepository $statesRepository,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $order  = $ordersRepository->findOneBy(["id" => $ordersId]);
            $states = $statesRepository->findOneBy(["id" => $statesId]);
            if (!$order or !$states) {
                return new JsonResponse(
                    ['result' => 'orders not found'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $order->setStates($states);
            $entityManager->persist($order);
            $entityManager->flush();

            return new JsonResponse(
                ['result' => "State order changed"],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return new JsonResponse(
                ['result' => 'Database error', 'error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/admin/count', name: 'app_admin_orders_count', methods: ["GET"])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    public function countAdmin(OrdersRepository $ordersRepository): Response
    {
        try {
            $count = $ordersRepository->countOrder();



            return new JsonResponse(
                ['result' => $count],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return new JsonResponse(
                ['result' => 'Database error', 'error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
