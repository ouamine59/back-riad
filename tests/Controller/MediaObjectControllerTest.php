<?php

namespace App\Tests\Controller;

use App\Controller\MediaObjectController;
use App\Entity\Products;
use App\Entity\MediaObject;
use Vich\UploaderBundle\Handler\UploadHandler;
use PHPUnit\Framework\TestCase;
use App\Controller\ProductsController;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MediaObjectControllerTest extends TestCase
{
    private $entityManager;
    private $validator;
    private $managerRegistry;
    private $uploadHandler;
    private $controller;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->uploadHandler = $this->createMock(\App\Service\UploadHandlerWrapper::class);
        $this->controller = new MediaObjectController();
    }

    public function testUploadSuccess()
    {
        // Création d'un fichier temporaire
        $filePath = tempnam(sys_get_temp_dir(), 'test_file');
        file_put_contents($filePath, 'dummy content'); // Contenu fictif
    
        $uploadedFile = new UploadedFile(
            $filePath,
            'test_image.jpg',
            'image/jpeg',
            null,
            true
        );
    
        $request = new Request([], ['productsId' => 1], [], [], ['filePath' => $uploadedFile]);
    
        $product = $this->createMock(Products::class);
        $repository = $this->createMock(\Doctrine\Persistence\ObjectRepository::class);
        $repository->method('find')->willReturn($product);
    
        $this->managerRegistry
            ->method('getRepository')
            ->willReturn($repository);
    
        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());
    
        $this->uploadHandler
            ->expects($this->once())
            ->method('upload');
    
        $this->entityManager
            ->expects($this->once())
            ->method('persist');
    
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
    
        $response = $this->controller->upload(
            $request,
            $this->entityManager,
            $this->validator,
            $this->managerRegistry,
            $this->uploadHandler
        );
    
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals(['message' => 'Image associée avec succès à l\'annonce.'], json_decode($response->getContent(), true));
    
        // Suppression du fichier temporaire
        unlink($filePath);
    }
    
    public function testUploadFileNotProvided()
    {
        $request = new Request([], ['productsId' => 1]);
    
        $product = $this->createMock(Products::class);
        $repository = $this->createMock(\Doctrine\Persistence\ObjectRepository::class);
        $repository->method('find')->willReturn($product);
    
        $this->managerRegistry
            ->method('getRepository')
            ->willReturn($repository);
    
        $response = $this->controller->upload(
            $request,
            $this->entityManager,
            $this->validator,
            $this->managerRegistry,
            $this->uploadHandler
        );
    
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(['error' => 'Fichier non fourni'], json_decode($response->getContent(), true));
    }


}