<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Products;
use App\Entity\Categories;
use App\Service\TokenService;
use PHPUnit\Framework\TestCase;
use App\Controller\ProductsController;
use App\Repository\ProductsRepository;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductsControllerTest extends TestCase
{
    private $entityManagerMock;
    private $validatorMock;
    private $requestMock;
    private $productsRepositoryMock;
    private $categoriesRepositoryMock;
    private $controller;

    private $tokenServiceMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
    
        // Mock pour le dépôt des utilisateurs
        $userRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $userRepositoryMock->method('find')->willReturnCallback(function ($id) {
            return $id === 1 ? new User() : null;
        });
    

    
        // Configurer getRepository pour retourner les bons mocks
        $this->entityManagerMock->method('getRepository')
            ->willReturnMap([

            ]);
    
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->tokenServiceMock = $this->createMock(TokenService::class);
        $this->requestMock = $this->createMock(Request::class);

        $this->productsRepositoryMock = $this->createMock(ProductsRepository::class);
        $this->categoriesRepositoryMock = $this->createMock(CategoriesRepository::class);

        $this->controller = new ProductsController();
    }
    public function testIndexWithNoProducts(): void
    {
        $this->productsRepositoryMock
            ->method('findBy')
            ->with(['isActivied' => 1])
            ->willReturn([]);


        $response = $this->controller->index($this->productsRepositoryMock, $this->categoriesRepositoryMock);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('result', $responseData);
        $this->assertEquals('no product', $responseData['result']);
    }
    /*** */
    public function testCreateWithValidData(): void
    {
        // Simuler les données de la requête
        $requestData = json_encode([
            'title' => 'Test Product',
            'price' => 100,
            'discount' => true,
            'priceDiscount' => 80,
            'description' => 'Test Description',
            'isActivied' => 1,
            'categoriesId' => 1
        ]);

        $this->requestMock->method('getContent')->willReturn($requestData);

        // Simuler une catégorie valide
        $category = new Categories();
        $this->categoriesRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($category);

        // Simuler une validation sans erreurs
        $this->validatorMock
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        // Simuler l'EntityManager
        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist');
        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        // Appeler la méthode
        $response = $this->controller->create(
            $this->entityManagerMock,
            $this->validatorMock,
            $this->requestMock,
            $this->categoriesRepositoryMock
        );

        // Assertions
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('result', $responseData);
        $this->assertEquals('product created successfully', $responseData['result']);
    }

    public function testCreateWithMissingData(): void
    {
        $requestData = json_encode([
            'title' => 'Test Product',
            'price' => 100,
        ]);

        $this->requestMock->method('getContent')->willReturn($requestData);

        // Appeler la méthode
        $response = $this->controller->create(
            $this->entityManagerMock,
            $this->validatorMock,
            $this->requestMock,
            $this->categoriesRepositoryMock
        );

        // Assertions
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('result', $responseData);
        $this->assertEquals('data missing', $responseData['result']);
    }

    public function testCreateWithInvalidCategory(): void
    {
        $requestData = json_encode([
            'title' => 'Test Product',
            'price' => 100,
            'discount' => true,
            'priceDiscount' => 80,
            'description' => 'Test Description',
            'isActivied' => 1,
            'categoriesId' => 999 // Catégorie inexistante
        ]);

        $this->requestMock->method('getContent')->willReturn($requestData);
        $this->categoriesRepositoryMock
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $response = $this->controller->create(
            $this->entityManagerMock,
            $this->validatorMock,
            $this->requestMock,
            $this->categoriesRepositoryMock
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('result', $responseData);
        $this->assertEquals('categories missing', $responseData['result']);
    }
    /******** */

    public function testUpdateWithValidData(): void
    {
        // Simuler les données de la requête
        $requestData = json_encode([
            'title' => 'Updated Product',
            'id' => 1,
            'price' => 120,
            'discount' => true,
            'priceDiscount' => 100,
            'description' => 'Updated Description',
            'isActivied' => 1,
            'categoriesId' => 1
        ]);

        $this->requestMock->method('getContent')->willReturn($requestData);

        // Simuler une catégorie valide
        $category = new Categories();
        $this->categoriesRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($category);

        // Simuler un produit existant
        $product = new Products();
        $this->productsRepositoryMock
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($product);

        // Simuler une validation sans erreurs
        $this->validatorMock
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        // Simuler l'EntityManager
        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($product);
        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        // Appeler la méthode
        $response = $this->controller->update(
            1,
            $this->entityManagerMock,
            $this->validatorMock,
            $this->requestMock,
            $this->productsRepositoryMock,
            $this->categoriesRepositoryMock
        );

        // Assertions
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('result', $responseData);
        $this->assertEquals('product updated successfully', $responseData['result']);
    }

    public function testUpdateWithMissingData(): void
    {
        // Simuler des données incomplètes
        $requestData = json_encode([
            'title' => 'Updated Product',
            // Champs manquants
        ]);

        $this->requestMock->method('getContent')->willReturn($requestData);

        // Appeler la méthode
        $response = $this->controller->update(
            1,
            $this->entityManagerMock,
            $this->validatorMock,
            $this->requestMock,
            $this->productsRepositoryMock,
            $this->categoriesRepositoryMock
        );

        // Assertions
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('result', $responseData);
        $this->assertEquals('data missing', $responseData['result']);
    }

    public function testUpdateWithInvalidCategory(): void
    {
        // Simuler des données valides mais avec une catégorie inexistante
        $requestData = json_encode([
            'title' => 'Updated Product',
            'id' => 1,
            'price' => 120,
            'discount' => true,
            'priceDiscount' => 100,
            'description' => 'Updated Description',
            'isActivied' => 1,
            'categoriesId' => 999 // Catégorie inexistante
        ]);

        $this->requestMock->method('getContent')->willReturn($requestData);

        // Simuler une catégorie inexistante
        $this->categoriesRepositoryMock
            ->method('find')
            ->with(999)
            ->willReturn(null);

        // Appeler la méthode
        $response = $this->controller->update(
            1,
            $this->entityManagerMock,
            $this->validatorMock,
            $this->requestMock,
            $this->productsRepositoryMock,
            $this->categoriesRepositoryMock
        );

        // Assertions
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('result', $responseData);
        $this->assertEquals('categories missing', $responseData['result']);
    }

    public function testUpdateWithNonexistentProduct(): void
{
    // Simuler des données valides
    $requestData = json_encode([
        'title' => 'Updated Product',
        'id' => 1,
        'price' => 120,
        'discount' => true,
        'priceDiscount' => 100,
        'description' => 'Updated Description',
        'isActivied' => 1,
        'categoriesId' => 1
    ]);

    $this->requestMock->method('getContent')->willReturn($requestData);

    // Simuler une catégorie existante
    $mockCategory = $this->createMock(\App\Entity\Categories::class);
    $this->categoriesRepositoryMock
        ->method('find')
        ->with(1) // ID de catégorie
        ->willReturn($mockCategory);

    // Simuler un produit inexistant
    $this->productsRepositoryMock
        ->method('findOneBy')
        ->with(['id' => 1]) // ID de produit
        ->willReturn(null);

    // Appeler la méthode
    $response = $this->controller->update(
        1,
        $this->entityManagerMock,
        $this->validatorMock,
        $this->requestMock,
        $this->productsRepositoryMock,
        $this->categoriesRepositoryMock
    );

    // Assertions
    $this->assertInstanceOf(Response::class, $response);
    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertArrayHasKey('result', $responseData);
    $this->assertEquals('product none find', $responseData['result']);
}
    /**** */
    public function testStatesSuccessfullyUpdatesProduct()
    {
        $productId = 1;
        $state = 1;
    
        // Create a mock for the Product entity
        $productMock = $this->createMock(Products::class);
    
        // Configure the ProductsRepository to return the mocked Product
        $this->productsRepositoryMock
            ->method('findOneBy')
            ->with(['id' => $productId])
            ->willReturn($productMock);
    
        // Configure the Product mock to accept the state and return an id
        $productMock
            ->expects($this->once())
            ->method('setActivied')
            ->with($state);
    
        $productMock
            ->method('getId')
            ->willReturn(1);
    
        // Configure the Validator to return no errors
        $this->validatorMock
            ->method('validate')
            ->with($productMock)
            ->willReturn(new ConstraintViolationList());
    
        // Expect EntityManager to persist and flush the product
        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($productMock);
    
        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');
    
        // Call the controller method
        $response = $this->controller->states(
            $productId,
            $state,
            $this->entityManagerMock,
            $this->validatorMock,
            $this->productsRepositoryMock
        );
    
        // Assert the response
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['result' => 'State on the product updated successfully', 'id' => 1]),
            $response->getContent()
        );
    }
    /******* */
//     public function testListingReturnsProductsSuccessfully()
// {
//     // Mock de l'entité Products
//     $productMock = $this->createMock(Products::class);
//     $productMock->method('getId')->willReturn(1);
//     $productMock->method('getTitle')->willReturn('Product 1');
//     $productMock->method('isActivied')->willReturn(true); 

//     $this->productsRepositoryMock
//         ->method('findAllForAdmin')
//         ->willReturn([$productMock]);

//     $response = $this->controller->listing($this->productsRepositoryMock);

//     // Vérifications
//     $this->assertInstanceOf(Response::class, $response);
//     $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
//     $this->assertJsonStringEqualsJsonString(
//         json_encode([
//             'result' => [
//                 [
//                     'id' => 1,
//                     'title' => 'Product 1',
//                     'isActivied' => true,
//                 ]
//             ]
//         ]),
//         $response->getContent()
//     );
// }
    
    public function testListingReturnsErrorWhenNoProductsFound()
    {
        // Configurez le dépôt pour retourner une liste vide
        $this->productsRepositoryMock
            ->method('findAllForAdmin')
            ->willReturn([]);
    
        // Appellez la méthode du contrôleur
        $response = $this->controller->listing($this->productsRepositoryMock);
    
        // Vérifiez la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['result' => 'product none find']),
            $response->getContent()
        );
    }
    
    public function testListingHandlesExceptionsGracefully()
    {
        // Configurez le dépôt pour lancer une exception
        $this->productsRepositoryMock
            ->method('findAllForAdmin')
            ->willThrowException(new \Exception('Database error'));
    
        // Appellez la méthode du contrôleur
        $response = $this->controller->listing($this->productsRepositoryMock);
    
        // Vérifiez la réponse
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertStringContainsString('Database error', $response->getContent());
    }
}