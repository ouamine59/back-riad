<?php

namespace App\Tests\Controller;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use App\Controller\UserController;
use App\Service\TokenService;
use App\Entity\Cities;
use App\Repository\CitiesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class UserControllerTest extends TestCase
{
    private $entityManagerMock;
    private $validatorMock;
    private $requestMock;
    private $passwordHasherMock;
    private $citiesRepositoryMock;
    private $tokenServiceMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
    
        // Mock pour le dépôt des utilisateurs
        $userRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $userRepositoryMock->method('find')->willReturnCallback(function ($id) {
            return $id === 1 ? new User() : null;
        });
    
        // Mock pour le dépôt des villes
        $this->citiesRepositoryMock = $this->createMock(CitiesRepository::class);
        $this->citiesRepositoryMock->method('findOneBy')->willReturnCallback(function ($criteria) {
            return $criteria['id'] === 1 ? new Cities() : null;
        });
    
        // Configurer getRepository pour retourner les bons mocks
        $this->entityManagerMock->method('getRepository')
            ->willReturnMap([
                [User::class, $userRepositoryMock],
                [Cities::class, $this->citiesRepositoryMock],
            ]);
    
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->passwordHasherMock = $this->createMock(UserPasswordHasherInterface::class);
        $this->tokenServiceMock = $this->createMock(TokenService::class);
        $this->requestMock = $this->createMock(Request::class);
    }
    public function testIndexWithInvalidJson()
    {
        $this->requestMock->method('getContent')->willReturn('Invalid JSON');

        $controller = new UserController();

        $response = $controller->index(
            $this->entityManagerMock,
            $this->validatorMock,
            $this->requestMock,
            $this->passwordHasherMock,
            $this->citiesRepositoryMock
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid JSON format', $response->getContent());
    }

    public function testIndexWithMissingData()
    {
        $this->requestMock->method('getContent')->willReturn(json_encode(['email' => 'test@example.com']));

        $controller = new UserController();

        $response = $controller->index(
            $this->entityManagerMock,
            $this->validatorMock,
            $this->requestMock,
            $this->passwordHasherMock,
            $this->citiesRepositoryMock
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Data missing', $response->getContent());
    }

    public function testIndexWithValidData()
    {
        $this->requestMock->method('getContent')->willReturn(json_encode([
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'idCities' => 1,
            'adress' => '123 Test St'
        ]));
    
        $mockCity = $this->createMock(Cities::class);
        $this->citiesRepositoryMock->method('findOneBy')->willReturn($mockCity);
    
        // Utilisation d'une liste vide de violations
        $mockViolations = new ConstraintViolationList();
        $this->validatorMock->method('validate')->willReturn($mockViolations);
    
        $this->passwordHasherMock->method('hashPassword')->willReturn('hashedPassword');
    
        $this->entityManagerMock->expects($this->once())->method('persist');
        $this->entityManagerMock->expects($this->once())->method('flush');
    
        $controller = new UserController();
    
        $response = $controller->index(
            $this->entityManagerMock,
            $this->validatorMock,
            $this->requestMock,
            $this->passwordHasherMock,
            $this->citiesRepositoryMock
        );
    
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertStringContainsString('User registered successfully', $response->getContent());
    }
    public function testIndexWithNonexistentCity()
    {
        $this->requestMock->method('getContent')->willReturn(json_encode([
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'idCities' => 999,
            'adress' => '123 Test St'
        ]));

        $this->citiesRepositoryMock->method('findOneBy')->willReturn(null);

        $controller = new UserController();

        $response = $controller->index(
            $this->entityManagerMock,
            $this->validatorMock,
            $this->requestMock,
            $this->passwordHasherMock,
            $this->citiesRepositoryMock
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('City not found', $response->getContent());
    }
    public function testUpdateUserNotFound()
{
    $userRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
    $userRepositoryMock->method('find')->willReturn(null);

    $this->entityManagerMock->method('getRepository')
        ->willReturn($userRepositoryMock);

    $request = new Request([], [], [], [], [], [], json_encode([
        'email' => 'test@example.com',
        'password' => 'password123',
        'phone' => '1234567890',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'adress' => '123 Test St',
        'comment' => 'Test Comment',
        'citiesId' => 1,
    ]));

    $controller = new UserController();
    $response = $controller->update(
        999, // ID inexistant
        $this->entityManagerMock,
        $this->validatorMock,
        $request,
        $this->passwordHasherMock,
        $this->citiesRepositoryMock,
        $this->tokenServiceMock
    );

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(404, $response->getStatusCode());
    $this->assertStringContainsString('User not found', $response->getContent());
}
public function testUpdateInvalidJson()
{
    $userRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
    $userRepositoryMock->method('find')->willReturn(new User());

    $this->entityManagerMock->method('getRepository')
        ->willReturn($userRepositoryMock);

    // Requête avec un JSON invalide
    $request = new Request([], [], [], [], [], [], 'Invalid JSON');

    $controller = new UserController();
    $response = $controller->update(
        1,
        $this->entityManagerMock,
        $this->validatorMock,
        $request,
        $this->passwordHasherMock,
        $this->citiesRepositoryMock,
        $this->tokenServiceMock
    );

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertStringContainsString('Invalid JSON format', $response->getContent());
}
  
public function testUpdateCityNotFound()
{
    $userRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
    $userRepositoryMock->method('find')->willReturn(new User());

    $this->entityManagerMock->method('getRepository')
        ->willReturn($userRepositoryMock);

    $this->citiesRepositoryMock->method('findOneBy')->willReturn(null);

    $request = new Request([], [], [], [], [], [], json_encode([
        'email' => 'test@example.com',
        'password' => 'password123',
        'phone' => '1234567890',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'adress' => '123 Test St',
        'comment' => 'Test Comment',
        'citiesId' => 999, // ID inexistant
    ]));

    $controller = new UserController();
    $response = $controller->update(
        1,
        $this->entityManagerMock,
        $this->validatorMock,
        $request,
        $this->passwordHasherMock,
        $this->citiesRepositoryMock,
        $this->tokenServiceMock
    );

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(404, $response->getStatusCode());
    $this->assertStringContainsString('Cities not found', $response->getContent());
}

public function testUpdateSuccess()
{
    $userRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
    $userRepositoryMock->method('find')->willReturn(new User());

    $this->entityManagerMock->method('getRepository')
        ->willReturn($userRepositoryMock);

    $this->citiesRepositoryMock->method('findOneBy')->willReturn(new Cities());

    $this->validatorMock->method('validate')->willReturn(new \Symfony\Component\Validator\ConstraintViolationList());

    $this->passwordHasherMock->method('hashPassword')->willReturn('hashedPassword');

    $request = new Request([], [], [], [], [], [], json_encode([
        'email' => 'test@example.com',
        'password' => 'password123',
        'phone' => '1234567890',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'adress' => '123 Test St',
        'comment' => 'Test Comment',
        'citiesId' => 1,
    ]));

    $controller = new UserController();
    $response = $controller->update(
        1,
        $this->entityManagerMock,
        $this->validatorMock,
        $request,
        $this->passwordHasherMock,
        $this->citiesRepositoryMock,
        $this->tokenServiceMock
    );

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString('User updated successfully', $response->getContent());
}


/******* */
public function testUpdateAdminUserNotFound()
{
    $this->entityManagerMock->method('getRepository')
        ->willReturn($this->createMock(\Doctrine\ORM\EntityRepository::class));

    $this->entityManagerMock->getRepository(User::class)
        ->method('find')
        ->willReturn(null);

    $request = new Request([], [], [], [], [], [], json_encode([]));

    $controller = new \App\Controller\UserController();
    $response = $controller->updateAdmin(
        999,
        $this->entityManagerMock,
        $this->validatorMock,
        $request,
        $this->passwordHasherMock,
        $this->citiesRepositoryMock
    );

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(404, $response->getStatusCode());
    $this->assertStringContainsString('User not found', $response->getContent());
}

public function testUpdateAdminInvalidJson()
{
    $this->entityManagerMock->method('getRepository')
        ->willReturn($this->createMock(\Doctrine\ORM\EntityRepository::class));

    $request = new Request([], [], [], [], [], [], 'Invalid JSON');

    $controller = new \App\Controller\UserController();
    $response = $controller->updateAdmin(
        1,
        $this->entityManagerMock,
        $this->validatorMock,
        $request,
        $this->passwordHasherMock,
        $this->citiesRepositoryMock
    );

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertStringContainsString('Invalid data provided', $response->getContent());
}

public function testUpdateAdminCitiesNotFound()
{
    $user = new User();
    $this->entityManagerMock->method('getRepository')
        ->willReturn($this->createMock(\Doctrine\ORM\EntityRepository::class));

    $this->entityManagerMock->getRepository(User::class)
        ->method('find')
        ->willReturn($user);

    $this->citiesRepositoryMock->method('findOneBy')->willReturn(null);

    $request = new Request([], [], [], [], [], [], json_encode([
        'email' => 'test@example.com',
        'password' => 'password123',
        'phone' => '1234567890',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'adress' => '123 Test St',
        'comment' => 'Test Comment',
        'citiesId' => 999,
    ]));

    $controller = new \App\Controller\UserController();
    $response = $controller->updateAdmin(
        1,
        $this->entityManagerMock,
        $this->validatorMock,
        $request,
        $this->passwordHasherMock,
        $this->citiesRepositoryMock
    );

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(404, $response->getStatusCode());
    $this->assertStringContainsString('Cities not found', $response->getContent());
}

public function testUpdateAdminValidationFailed()
{
    $user = new User();
    $city = new Cities();

    $this->entityManagerMock->method('getRepository')
        ->willReturn($this->createMock(\Doctrine\ORM\EntityRepository::class));

    $this->entityManagerMock->getRepository(User::class)
        ->method('find')
        ->willReturn($user);

    $this->citiesRepositoryMock->method('findOneBy')->willReturn($city);

    $this->validatorMock->method('validate')
        ->willReturn(new \Symfony\Component\Validator\ConstraintViolationList([
            new \Symfony\Component\Validator\ConstraintViolation(
                'This value should not be blank.',
                null,
                [],
                '',
                'email',
                null
            )
        ]));

    $request = new Request([], [], [], [], [], [], json_encode([
        'email' => '',
        'password' => 'password123',
        'phone' => '1234567890',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'adress' => '123 Test St',
        'comment' => 'Test Comment',
        'citiesId' => 1,
    ]));

    $controller = new \App\Controller\UserController();
    $response = $controller->updateAdmin(
        1,
        $this->entityManagerMock,
        $this->validatorMock,
        $request,
        $this->passwordHasherMock,
        $this->citiesRepositoryMock
    );

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertStringContainsString('This value should not be blank.', $response->getContent());
}

public function testUpdateAdminSuccess()
{
    $user = new User();
    $city = new Cities();

    $this->entityManagerMock->method('getRepository')
        ->willReturn($this->createMock(\Doctrine\ORM\EntityRepository::class));

    $this->entityManagerMock->getRepository(User::class)
        ->method('find')
        ->willReturn($user);

    $this->citiesRepositoryMock->method('findOneBy')->willReturn($city);

    $this->validatorMock->method('validate')
        ->willReturn(new \Symfony\Component\Validator\ConstraintViolationList());

    $this->passwordHasherMock->method('hashPassword')->willReturn('hashedPassword');

    $request = new Request([], [], [], [], [], [], json_encode([
        'email' => 'test@example.com',
        'password' => 'password123',
        'phone' => '1234567890',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'adress' => '123 Test St',
        'comment' => 'Test Comment',
        'citiesId' => 1,
    ]));

    $controller = new \App\Controller\UserController();
    $response = $controller->updateAdmin(
        1,
        $this->entityManagerMock,
        $this->validatorMock,
        $request,
        $this->passwordHasherMock,
        $this->citiesRepositoryMock
    );

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString('User updated successfully', $response->getContent());
}
}