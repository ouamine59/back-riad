<?php

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use App\Entity\User; // Votre classe utilisateur réelle
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends KernelTestCase
{
    public function testLoginWithNoUser(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $controller = $container->get(SecurityController::class);

        $response = $controller->login(null);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['message' => 'Unauthorized']), // Correction ici
            $response->getContent()
        );
    }

    public function testLoginWithUser(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $controller = $container->get(SecurityController::class);

        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userMock->method('getUserIdentifier')->willReturn('test_user'); // Assurez-vous que ceci est correct
        $userMock->method('getId')->willReturn(123);

        $response = $controller->login($userMock);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $expectedData = [
            'user' => 'test_user',
            'id'   => 123,
        ];
        $actualData = json_decode($response->getContent(), true);

        $this->assertEquals($expectedData, $actualData); // Comparer les données corrigées
    }
}