<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Orders;
use App\Entity\States;
use App\Entity\Products;
use App\Entity\RowsOrder;
use App\Entity\Categories;
use App\Service\TokenService;
use PHPUnit\Framework\TestCase;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use App\DTO\OrdersClientListingDTO;
use App\Controller\OrdersController;
use App\Repository\OrdersRepository;
use App\Repository\StatesRepository;
use App\Controller\ProductsController;
use App\Repository\ProductsRepository;
use App\Repository\RowsOrderRepository;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrdersControllerTest extends TestCase
{
    private $entityManagerMock;
    private $validatorMock;
    private $requestMock;
    private $rowOrderMock;
    private $ordersRepositoryMock;
    private $categoriesRepositoryMock;
    private $controller;
    private $rowsOrderRepositoryMock;
    private $tokenServiceMock;
    private $productsRepositoryMock;
    private $userRepositoryMock;
    private $statesRepositoryMock;
    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->statesRepositoryMock = $this->createMock(StatesRepository::class);
        $this->rowsOrderRepositoryMock = $this->createMock(RowsOrderRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->productsRepositoryMock = $this->createMock(ProductsRepository::class);
        // Mock pour le dépôt des utilisateurs
        $userRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $userRepositoryMock->method('find')->willReturnCallback(function ($id) {
            return $id === 1 ? new User() : null;
        });
    
        $this->ordersRepositoryMock = $this->createMock(\App\Repository\OrdersRepository::class);
    
        // Configurer getRepository pour retourner les bons mocks
        $this->entityManagerMock->method('getRepository')
            ->willReturnMap([

            ]);
    
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->tokenServiceMock = $this->createMock(TokenService::class);
        $this->requestMock = $this->createMock(Request::class);

        $this->ordersRepositoryMock = $this->createMock(\App\Repository\OrdersRepository::class);
        $this->categoriesRepositoryMock = $this->createMock(CategoriesRepository::class);

        $this->controller = new OrdersController();
    }
   /**** */
   public function testIndexReturnsNoUser()
{
    $idUser = 1;

    // Configurez le repository pour retourner null
    $this->ordersRepositoryMock
        ->method('find')
        ->with($idUser)
        ->willReturn(null);

    // Appelez la méthode du contrôleur
    $response = $this->controller->index($idUser, $this->ordersRepositoryMock);

    // Vérifiez la réponse
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    $this->assertJsonStringEqualsJsonString(
        json_encode(['result' => 'no user']),
        $response->getContent()
    );
}

public function testIndexReturnsNoOrders()
{
    $idUser = 1;

    // Configurez le repository pour retourner un utilisateur
    $userMock = $this->createMock(User::class);
    $this->ordersRepositoryMock
        ->method('find')
        ->with($idUser)
        ->willReturn($userMock);

    // Configurez le repository pour retourner une liste vide
    $this->ordersRepositoryMock
        ->method('findAllByIdUser')
        ->with($idUser)
        ->willReturn([]);

    // Appelez la méthode du contrôleur
    $response = $this->controller->index($idUser, $this->ordersRepositoryMock);

    // Vérifiez la réponse
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    $this->assertJsonStringEqualsJsonString(
        json_encode(['result' => 'no orders']),
        $response->getContent()
    );
}

public function testIndexReturnsOrdersSuccessfully()
{
    $idUser = 1;

    // Configurez le repository pour retourner un utilisateur
    $userMock = $this->createMock(User::class);
    $this->ordersRepositoryMock
        ->method('find')
        ->with($idUser)
        ->willReturn($userMock);

    // Mock d'un DTO OrdersClientListingDTO
    $orderDTO = new OrdersClientListingDTO(
        id: 100,
        states: 'delivered',
        userId: $idUser,
        isCreatedAt: new \DateTime('2024-12-10')
    );

    // Configurez le repository pour retourner une liste de DTO
    $this->ordersRepositoryMock
        ->method('findAllByIdUser')
        ->with($idUser)
        ->willReturn([$orderDTO]);

    // Appelez la méthode du contrôleur
    $response = $this->controller->index($idUser, $this->ordersRepositoryMock);

    // Vérifiez la réponse
    $this->assertInstanceOf(Response::class, $response);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    // Construisez l'attendu avec le format réel de la date
    $expectedResponse = json_encode([
        'result' => [
            [
                'id' => 100,
                'states' => 'delivered',
                'userId' => $idUser,
                'isCreatedAt' => [
                    'date' => '2024-12-10 00:00:00.000000',
                    'timezone_type' => 3,
                    'timezone' => 'UTC',
                ],
            ]
        ]
    ]);

    // Vérifiez le contenu JSON
    $this->assertJsonStringEqualsJsonString(
        $expectedResponse,
        $response->getContent()
    );
}

/******* */
public function testDetailClientNoUser()
    {
        $idUser = 1;
        $idOrder = 10;

        // Configurez le OrdersRepository pour retourner null
        $this->ordersRepositoryMock
            ->method('findOneBy')
            ->with(['user' => $idUser])
            ->willReturn(null);

        // Appelez la méthode du contrôleur
        $response = $this->controller->detailClient($idUser, $idOrder, $this->ordersRepositoryMock, $this->productsRepositoryMock, $this->rowsOrderRepositoryMock);

        // Vérifiez la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['result' => 'no user']),
            $response->getContent()
        );
    }

    public function testDetailClientNoOrder()
    {
        $idUser = 1;
        $idOrder = 10;

        // Configurez le OrdersRepository pour retourner un utilisateur
        $this->ordersRepositoryMock
            ->method('findOneBy')
            ->with(['user' => $idUser])
            ->willReturn($this->createMock(User::class));

        // Configurez le OrdersRepository pour retourner null pour la commande
        $this->ordersRepositoryMock
        ->method('findOneByUser')
        ->with($idUser, $idOrder)
        ->willReturn([]);

        // Appelez la méthode du contrôleur
        $response = $this->controller->detailClient($idUser, $idOrder, $this->ordersRepositoryMock, $this->productsRepositoryMock, $this->rowsOrderRepositoryMock);

        // Vérifiez la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['result' => 'no order']),
            $response->getContent()
        );
    }
    // public function testDetailClientReturnsOrderDetailsSuccessfully()
    // {
    //     $idUser = 1;
    //     $idOrder = 10;
    
    //     // Mock des entités nécessaires
    //     $userMock = $this->createMock(User::class);
    //     $orderMock = $this->createMock(Orders::class);
    //     $productMock = $this->createMock(Products::class);
    //     $rowOrderMock = $this->createMock(RowsOrder::class);
    //     $statesMock = $this->createMock(\App\Entity\States::class);
    
    //     // Configurez le `OrdersRepository` pour retourner un utilisateur
    //     $this->ordersRepositoryMock
    //         ->method('findOneBy')
    //         ->with(['user' => $idUser])
    //         ->willReturn($userMock);
    
    //     // Configurez `findOneByUser` pour retourner des détails de commande
    //     $this->ordersRepositoryMock
    //         ->method('findOneByUser')
    //         ->with($idUser, $idOrder)
    //         ->willReturn([$orderMock]);
    
    //     // Configurez `find` pour retourner une commande valide
    //     $this->ordersRepositoryMock
    //         ->method('find')
    //         ->with($idOrder)
    //         ->willReturn($orderMock);
    
    //     // Configurez les méthodes du mock `Orders`
    //     $orderMock->method('getId')->willReturn($idOrder);
    //     $orderMock->method('getStates')->willReturn($statesMock); // Retourne un mock de States
    //     $orderMock->method('getUser')->willReturn($userMock); // Retourne un mock de User
    //     $orderMock->method('getIsCreatedAt')->willReturn(new \DateTimeImmutable('2024-12-10'));
    //     $orderMock->method('getRowsOrders')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$rowOrderMock]));
    
    //     // Configurez les méthodes du mock `States`
    //     $statesMock->method('getStates')->willReturn('delivered');
    
    //     // Configurez les méthodes du mock `User`
    //     $userMock->method('getId')->willReturn($idUser);
    
    //     // Configurez les méthodes du mock `RowsOrder`
    //     $rowOrderMock->method('getAmount')->willReturn(2);
    //     $rowOrderMock->method('getPrice')->willReturn("50");
    //     $rowOrderMock->method('getProducts')->willReturn($productMock);
    
    //     // Configurez le mock `ProductsRepository`
    //     $this->productsRepositoryMock
    //         ->method('find')
    //         ->with(1) // ID du produit
    //         ->willReturn($productMock);
    
    //     // Configurez les méthodes du mock `Products`
    //     $productMock->method('getId')->willReturn(1);
    //     $productMock->method('getTitle')->willReturn('Product Title');
    //     $productMock->method('getDescription')->willReturn('Product Description');
    
    //     // Appelez la méthode du contrôleur
    //     $response = $this->controller->detailClient(
    //         $idUser,
    //         $idOrder,
    //         $this->ordersRepositoryMock,
    //         $this->productsRepositoryMock,
    //         $this->rowsOrderRepositoryMock
    //     );
    
    //     // Vérifiez la réponse
    //     $this->assertInstanceOf(Response::class, $response);
    //     $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    
    //     $expectedResponse = json_encode([
    //         'result' => [
    //             [
    //                 'id' => $idOrder,
    //                 'states' => 'delivered',
    //                 'userId' => $idUser,
    //                 'isCreatedAt' => '2024-12-10T00:00:00+00:00',
    //                 'products' => [
    //                     [
    //                         'title' => 'Product Title',
    //                         'description' => 'Product Description',
    //                         'amount' => 2,
    //                         'price' => "50",
    //                     ]
    //                 ]
    //             ]
    //         ]
    //     ]);
    
    //     $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    // }
    
    public function testDetailClientHandlesException()
    {
        $idUser = 1;
        $idOrder = 10;
        $orderMock = $this->createMock(Orders::class);
        // Configurez le OrdersRepository pour lancer une exception
        $this->ordersRepositoryMock
            ->method('findOneBy')
            ->with(['user' => $idUser])
            ->willThrowException(new \Exception('Database error'));
        $orderMock
            ->method('getRowsOrders')
            ->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->rowOrderMock]));
        // Appelez la méthode du contrôleur
        $response = $this->controller->detailClient($idUser, $idOrder, $this->ordersRepositoryMock, $this->productsRepositoryMock, $this->rowsOrderRepositoryMock);

        // Vérifiez la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertStringContainsString('Database error', $response->getContent());
    }
    /****** */
    public function testDeleteByClientOrderNotFound()
{
    $idUser = 1;
    $idOrder = 10;

    // Configurez le `OrdersRepository` pour retourner null
    $this->ordersRepositoryMock
        ->method('findOneBy')
        ->with(['id' => $idOrder, 'user' => $idUser])
        ->willReturn(null);

    // Appelez la méthode du contrôleur
    $response = $this->controller->deleteByClient(
        $idUser,
        $idOrder,
        $this->ordersRepositoryMock,
        $this->entityManagerMock,
        $this->rowsOrderRepositoryMock
    );

    // Vérifiez la réponse
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    $this->assertJsonStringEqualsJsonString(
        json_encode(['result' => 'Order not found']),
        $response->getContent()
    );
}

public function testDeleteByClientOrderTooOld()
{
    $idUser = 1;
    $idOrder = 10;

    // Mock d'une commande trop ancienne
    $orderMock = $this->createMock(Orders::class);
    $orderMock->method('getIsCreatedAt')->willReturn(new \DateTimeImmutable('-20 days')); // Utilisation de DateTimeImmutable

    // Configurez le `OrdersRepository` pour retourner la commande
    $this->ordersRepositoryMock
        ->method('findOneBy')
        ->with(['id' => $idOrder, 'user' => $idUser])
        ->willReturn($orderMock);

    // Appelez la méthode du contrôleur
    $response = $this->controller->deleteByClient(
        $idUser,
        $idOrder,
        $this->ordersRepositoryMock,
        $this->entityManagerMock,
        $this->rowsOrderRepositoryMock
    );

    // Vérifiez la réponse
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    $this->assertJsonStringEqualsJsonString(
        json_encode(['result' => 'Order cannot be deleted. It is older than 14 days.']),
        $response->getContent()
    );
}
public function testDeleteByClientOrderDeletedSuccessfully()
{
    $idUser = 1;
    $idOrder = 10;

    // Mock d'une commande valide
    $orderMock = $this->createMock(Orders::class);
    $orderMock->method('getIsCreatedAt')->willReturn(new \DateTimeImmutable('-5 days')); // Utilisation de DateTimeImmutable
    $rowOrderMock = $this->createMock(RowsOrder::class);

    // Configurez le `OrdersRepository` pour retourner la commande
    $this->ordersRepositoryMock
        ->method('findOneBy')
        ->with(['id' => $idOrder, 'user' => $idUser])
        ->willReturn($orderMock);

    // Configurez le `RowsOrderRepository` pour retourner des rows
    $this->rowsOrderRepositoryMock
        ->method('findBy')
        ->with(['orders' => $idOrder])
        ->willReturn([$rowOrderMock]);

    // Mock les interactions avec l'EntityManager
    $this->entityManagerMock
        ->expects($this->exactly(2)) // Suppression d'une commande et d'une row
        ->method('remove');
    $this->entityManagerMock
        ->expects($this->once())
        ->method('flush');

    // Appelez la méthode du contrôleur
    $response = $this->controller->deleteByClient(
        $idUser,
        $idOrder,
        $this->ordersRepositoryMock,
        $this->entityManagerMock,
        $this->rowsOrderRepositoryMock
    );

    // Vérifiez la réponse
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->assertJsonStringEqualsJsonString(
        json_encode(['result' => 'Order deleted successfully']),
        $response->getContent()
    );
}
/******* */
public function testCreateUserNotFound()
{
    $idUser = 1;

    // Mock du UserRepository pour retourner null
    $this->userRepositoryMock
        ->method('findOneBy')
        ->with(['id' => $idUser])
        ->willReturn(null);

    // Mock de la requête
    $this->requestMock
        ->method('getContent')
        ->willReturn(json_encode(['products' => []]));

    // Appel de la méthode du contrôleur
    $response = $this->controller->create(
        $idUser,
        $this->entityManagerMock,
        $this->productsRepositoryMock,
        $this->userRepositoryMock,
        $this->requestMock,
        $this->statesRepositoryMock,
        $this->validatorMock
    );

    // Vérifications
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    $this->assertJsonStringEqualsJsonString(
        json_encode(['result' => 'User not found']),
        $response->getContent()
    );
}

public function testCreateInvalidJsonData()
{
    $idUser = 1;

    // Mock du UserRepository pour retourner un utilisateur valide
    $userMock = $this->createMock(User::class);
    $this->userRepositoryMock
        ->method('findOneBy')
        ->with(['id' => $idUser])
        ->willReturn($userMock);

    // Mock de la requête avec des données invalides
    $this->requestMock
        ->method('getContent')
        ->willReturn(json_encode([]));

    // Appel de la méthode du contrôleur
    $response = $this->controller->create(
        $idUser,
        $this->entityManagerMock,
        $this->productsRepositoryMock,
        $this->userRepositoryMock,
        $this->requestMock,
        $this->statesRepositoryMock,
        $this->validatorMock
    );

    // Vérifications
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    $this->assertJsonStringEqualsJsonString(
        json_encode(['result' => 'Invalid data provided']),
        $response->getContent()
    );
}

// public function testCreateProductNotFound()
// {
//     $idUser = 1;

//     // Mock des entités nécessaires
//     $userMock = $this->createMock(User::class);
//     $this->userRepositoryMock
//         ->method('findOneBy')
//         ->with(['id' => $idUser])
//         ->willReturn($userMock);

//     $this->requestMock
//         ->method('getContent')
//         ->willReturn(json_encode([
//             'products' => [['productsId' => 1, 'amount' => 2]]
//         ]));

//     $this->productsRepositoryMock
//         ->method('find')
//         ->with(1)
//         ->willReturn(null);

//     // Appel de la méthode du contrôleur
//     $response = $this->controller->create(
//         $idUser,
//         $this->entityManagerMock,
//         $this->productsRepositoryMock,
//         $this->userRepositoryMock,
//         $this->requestMock,
//         $this->statesRepositoryMock,
//         $this->validatorMock
//     );

//     // Vérifications
//     $this->assertInstanceOf(JsonResponse::class, $response);
//     $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
//     $this->assertJsonStringEqualsJsonString(
//         json_encode(['result' => 'Product none available']),
//         $response->getContent()
//     );
// }

// public function testCreateValidationFailed()
// {
//     $idUser = 1;

//     // Mock des entités nécessaires
//     $userMock = $this->createMock(User::class);
//     $this->userRepositoryMock
//         ->method('findOneBy')
//         ->with(['id' => $idUser])
//         ->willReturn($userMock);

//     $productMock = $this->createMock(Products::class);
//     $productMock->method('getPrice')->willReturn('100');
//     $productMock->method('getPriceDiscount')->willReturn('80');
//     $productMock->method('isDiscount')->willReturn(true);

//     $this->productsRepositoryMock
//         ->method('find')
//         ->with(1)
//         ->willReturn($productMock);

//     $this->requestMock
//         ->method('getContent')
//         ->willReturn(json_encode([
//             'products' => [['productsId' => 1, 'amount' => 2]]
//         ]));

//     // Mock du Validator pour retourner des erreurs
//     $constraintViolationList = $this->createMock(\Symfony\Component\Validator\ConstraintViolationList::class);
//     $constraintViolationList->method('count')->willReturn(1);
//     $this->validatorMock->method('validate')->willReturn($constraintViolationList);

//     // Appel de la méthode du contrôleur
//     $response = $this->controller->create(
//         $idUser,
//         $this->entityManagerMock,
//         $this->productsRepositoryMock,
//         $this->userRepositoryMock,
//         $this->requestMock,
//         $this->statesRepositoryMock,
//         $this->validatorMock
//     );

//     // Vérifications
//     $this->assertInstanceOf(JsonResponse::class, $response);
//     $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
//     $this->assertJsonStringEqualsJsonString(
//         json_encode(['result' => 'Validation failed', 'errors' => []]),
//         $response->getContent()
//     );
// }

// public function testCreateOrderSuccessfully()
// {
//     $idUser = 1;

//     // Mock des entités nécessaires
//     $userMock = $this->createMock(User::class);
//     $this->userRepositoryMock
//         ->method('findOneBy')
//         ->with(['id' => $idUser])
//         ->willReturn($userMock);

//     $productMock = $this->createMock(Products::class);
//     $productMock->method('getPrice')->willReturn('100');
//     $productMock->method('getPriceDiscount')->willReturn('80');
//     $productMock->method('isDiscount')->willReturn(true);

//     $this->productsRepositoryMock
//         ->method('find')
//         ->with(1)
//         ->willReturn($productMock);

//     $this->requestMock
//         ->method('getContent')
//         ->willReturn(json_encode([
//             'products' => [['productsId' => 1, 'amount' => 2]]
//         ]));

//     $this->validatorMock
//         ->method('validate')
//         ->willReturn(new \Symfony\Component\Validator\ConstraintViolationList());

//     // Mock de l'EntityManager
//     $this->entityManagerMock
//         ->expects($this->exactly(2)) // Order et Row
//         ->method('persist');
//     $this->entityManagerMock
//         ->expects($this->once())
//         ->method('flush');

//     // Appel de la méthode du contrôleur
//     $response = $this->controller->create(
//         $idUser,
//         $this->entityManagerMock,
//         $this->productsRepositoryMock,
//         $this->userRepositoryMock,
//         $this->requestMock,
//         $this->statesRepositoryMock,
//         $this->validatorMock
//     );

//     // Vérifications
//     $this->assertInstanceOf(JsonResponse::class, $response);
//     $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
//     $this->assertJsonStringEqualsJsonString(
//         json_encode(['result' => 'Order registered successfully']),
//         $response->getContent()
//     );
// }

/******** */
public function testListingAdminNoOrdersFound()
{
    // Mock du OrdersRepository pour retourner un tableau vide
    $this->ordersRepositoryMock
        ->method('findAllForAdmin')
        ->willReturn([]);

    // Appel de la méthode du contrôleur
    $response = $this->controller->listingAdmin($this->ordersRepositoryMock);

    // Vérifications
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    $this->assertJsonStringEqualsJsonString(
        json_encode(['result' => 'orders not found']),
        $response->getContent()
    );
}

public function testListingAdminOrdersRetrievedSuccessfully()
{
    // Création d'une véritable instance de `OrdersAdminListingDTO`
    $orderDto = new \App\DTO\OrdersAdminListingDTO(
        1, // id
        new \DateTimeImmutable('2024-12-10'), // isCreatedAt
        'John', // firstName
        'Doe', // lastName
        'delivered' // states
    );

    // Mock du repository pour retourner un tableau de DTO
    $this->ordersRepositoryMock
        ->method('findAllForAdmin')
        ->willReturn([$orderDto]);

    // Appel de la méthode du contrôleur
    $response = $this->controller->listingAdmin($this->ordersRepositoryMock);

    // Vérifications
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

    $expectedResponse = json_encode([
        'result' => [
            [
                'id' => 1,
                'isCreatedAt' => [
                    'date' => '2024-12-10 00:00:00.000000',
                    'timezone_type' => 3,
                    'timezone' => 'UTC',
                ],
                'firstName' => 'John',
                'lastName' => 'Doe',
                'states' => 'delivered'
            ]
        ]
    ]);

    $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
}

public function testListingAdminDatabaseError()
{
    // Mock du OrdersRepository pour lancer une exception
    $this->ordersRepositoryMock
        ->method('findAllForAdmin')
        ->willThrowException(new \Exception('Database error'));

    // Appel de la méthode du contrôleur
    $response = $this->controller->listingAdmin($this->ordersRepositoryMock);

    // Vérifications
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    $this->assertStringContainsString('Database error', $response->getContent());
}
/***** */
public function testDetailAdminOrderNotFound()
{
    $ordersId = 1;

    // Mock du OrdersRepository pour retourner null
    $this->ordersRepositoryMock
        ->method('findOneBy')
        ->with(['id' => $ordersId])
        ->willReturn(null);

    // Appel de la méthode du contrôleur
    $response = $this->controller->detailAdmin(
        $ordersId,
        $this->ordersRepositoryMock,
        $this->rowsOrderRepositoryMock,
        $this->productsRepositoryMock
    );

    // Vérifications
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    $this->assertJsonStringEqualsJsonString(
        json_encode(['result' => 'Order not found']),
        $response->getContent()
    );
}

public function testDetailAdminOrderRetrievedSuccessfully()
{
    $ordersId = 1;

    // Mock des entités et DTO nécessaires
    $orderDtoMock = $this->createMock(\App\DTO\OrdersAdminDetailDTO::class);
    $orderDtoMock->id = $ordersId;
    $orderDtoMock->isCreatedAt = new \DateTimeImmutable('2024-12-10');
    $orderDtoMock->firstName = 'John';
    $orderDtoMock->lastName = 'Doe';
    $orderDtoMock->states = 'delivered';

    $orderMock = $this->createMock(\App\Entity\Orders::class);
    $rowOrderMock = $this->createMock(\App\Entity\RowsOrder::class);
    $productMock = $this->createMock(\App\Entity\Products::class);

    // Mock OrdersRepository pour findOneBy et find
    $this->ordersRepositoryMock
        ->method('findOneBy')
        ->with(['id' => $ordersId])
        ->willReturn($orderMock);

    $this->ordersRepositoryMock
        ->method('findOneForAdmin')
        ->with($ordersId)
        ->willReturn([$orderDtoMock]);

    $this->ordersRepositoryMock
        ->method('find')
        ->with($ordersId)
        ->willReturn($orderMock);

    $orderMock
        ->method('getRowsOrders')
        ->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$rowOrderMock]));

    // Mock RowsOrderRepository
    $this->rowsOrderRepositoryMock
        ->method('findOneBy')
        ->with(['orders' => $ordersId])
        ->willReturn($rowOrderMock);

    $rowOrderMock
        ->method('getAmount')
        ->willReturn(2);
    $rowOrderMock
        ->method('getPrice')
        ->willReturn('50');
    $rowOrderMock
        ->method('getProducts')
        ->willReturn($productMock);

    // Mock ProductsRepository
    $productMock->method('getId')->willReturn(123); // Un ID arbitraire

$this->productsRepositoryMock
    ->method('find')
    ->with(123)
    ->willReturn($productMock);

    $productMock
        ->method('getTitle')
        ->willReturn('Product Title');
    $productMock
        ->method('getDescription')
        ->willReturn('Product Description');

    // Appel de la méthode du contrôleur
    $response = $this->controller->detailAdmin(
        $ordersId,
        $this->ordersRepositoryMock,
        $this->rowsOrderRepositoryMock,
        $this->productsRepositoryMock
    );

    // Vérifications
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    $expectedResponse = json_encode([
        'result' => [
            [
                'id' => $ordersId,
                'isCreatedAt' => '2024-12-10T00:00:00+00:00',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'states' => 'delivered',
                'products' => [
                    [
                        'title' => 'Product Title',
                        'description' => 'Product Description',
                        'amount' => 2,
                        'price' => '50',
                    ]
                ]
            ]
        ]
    ]);

    $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
}

public function testDetailAdminDatabaseError()
{
    $ordersId = 1;

    // Mock OrdersRepository pour lancer une exception sur la méthode `find`
    $this->ordersRepositoryMock
        ->method('find')
        ->with($ordersId)
        ->willThrowException(new \Exception('Database error'));

    // Appel de la méthode du contrôleur
    $response = $this->controller->detailAdmin(
        $ordersId,
        $this->ordersRepositoryMock,
        $this->rowsOrderRepositoryMock,
        $this->productsRepositoryMock
    );

    // Vérifications
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

    $content = json_decode($response->getContent(), true);

    // Vérifie que le message d'erreur est présent
    $this->assertArrayHasKey('error', $content);
    $this->assertEquals('Database error', $content['error']);
}

/**** */
public function testUpdateStateAdminOrderNotFound()
    {
        $this->ordersRepositoryMock
            ->method('findOneBy')
            ->willReturn(null);

        $this->statesRepositoryMock
            ->method('findOneBy')
            ->willReturn(null);

        $response = $this->controller->updateStateAdmin(
            1,
            1,
            $this->ordersRepositoryMock,
            $this->statesRepositoryMock,
            $this->entityManagerMock
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(['result' => 'orders not found'], json_decode($response->getContent(), true));
    }

    public function testUpdateStateAdminStateNotFound()
    {
        $order = $this->createMock(\App\Entity\Orders::class);

        $this->ordersRepositoryMock
            ->method('findOneBy')
            ->willReturn($order);

        $this->statesRepositoryMock
            ->method('findOneBy')
            ->willReturn(null);

        $response = $this->controller->updateStateAdmin(
            1,
            1,
            $this->ordersRepositoryMock,
            $this->statesRepositoryMock,
            $this->entityManagerMock
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(['result' => 'orders not found'], json_decode($response->getContent(), true));
    }

    public function testUpdateStateAdminSuccess()
    {
        $order = $this->createMock(\App\Entity\Orders::class);
        $states = $this->createMock(\App\Entity\States::class);

        $this->ordersRepositoryMock
            ->method('findOneBy')
            ->willReturn($order);

        $this->statesRepositoryMock
            ->method('findOneBy')
            ->willReturn($states);

        $order->expects($this->once())
            ->method('setStates')
            ->with($states);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($order);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        $response = $this->controller->updateStateAdmin(
            1,
            1,
            $this->ordersRepositoryMock,
            $this->statesRepositoryMock,
            $this->entityManagerMock
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(['result' => 'State order changed'], json_decode($response->getContent(), true));
    }

    public function testUpdateStateAdminDatabaseError()
    {
        $order = $this->createMock(\App\Entity\Orders::class);
        $states = $this->createMock(\App\Entity\States::class);

        $this->ordersRepositoryMock
            ->method('findOneBy')
            ->willReturn($order);

        $this->statesRepositoryMock
            ->method('findOneBy')
            ->willReturn($states);

        $this->entityManagerMock
            ->method('persist')
            ->willThrowException(new \Exception('Database error'));

        $response = $this->controller->updateStateAdmin(
            1,
            1,
            $this->ordersRepositoryMock,
            $this->statesRepositoryMock,
            $this->entityManagerMock
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertStringContainsString('Database error', json_decode($response->getContent(), true)['error']);
    }
    /**** */
    public function testCountAdminSuccess()
    {
        // Configure le mock pour retourner un compte précis
        $this->ordersRepositoryMock
            ->method('countOrder')
            ->willReturn(42);

        $response = $this->controller->countAdmin($this->ordersRepositoryMock);

        // Vérifications de la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(['result' => 42], json_decode($response->getContent(), true));
    }

    public function testCountAdminDatabaseError()
    {
        // Configure le mock pour lancer une exception
        $this->ordersRepositoryMock
            ->method('countOrder')
            ->willThrowException(new \Exception('Database error'));

        $response = $this->controller->countAdmin($this->ordersRepositoryMock);

        // Vérifications de la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Database error', $responseData['result']);
        $this->assertStringContainsString('Database error', $responseData['error']);
    }
}