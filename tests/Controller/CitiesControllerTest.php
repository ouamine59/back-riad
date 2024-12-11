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

class CitiesControllerTest extends TestCase
{
    private $entityManagerMock;
    private $citiesRepositoryMock;
    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
    
        // Mock the Cities repository
        $this->citiesRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
    
        // Map the repository to the EntityManager mock
        $this->entityManagerMock->method('getRepository')
            ->with(\App\Entity\Cities::class)
            ->willReturn($this->citiesRepositoryMock);
    }
    
    public function testListing(): void
    {
        // Create mock entities for Cities
        $city1 = $this->createMock(\App\Entity\Cities::class);
        $city1->method('getId')->willReturn(1);
        $city1->method('getCities')->willReturn('City One');
    
        $city2 = $this->createMock(\App\Entity\Cities::class);
        $city2->method('getId')->willReturn(2);
        $city2->method('getCities')->willReturn('City Two');
    
        // Mock the repository to return these entities
        $this->citiesRepositoryMock->method('findAll')->willReturn([$city1, $city2]);
    
        // Instantiate the controller
        $controller = new \App\Controller\CitiesController();
    
        // Call the method
        $response = $controller->listing($this->entityManagerMock);
    
        // Assertions
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    
        $expectedData = [
            ['value' => 1, 'label' => 'City One'],
            ['value' => 2, 'label' => 'City Two'],
        ];
    
        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedData),
            $response->getContent()
        );
    }
}