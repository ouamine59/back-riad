<?php

namespace App\Controller;

use App\Entity\Cities;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CitiesController extends AbstractController
{
    #[Route('/cities', name: 'app_cities')]
    #[Route(
        name: 'app_cities_select',
        path: '/api/cities/select',
        methods: ['GET']
    )] 
     // 
     public function listing( EntityManagerInterface $manager): Response
      {
          $repository = $manager->getRepository(Cities::class);
          $cities       = $repository->findAll();
          $r = array_map(function($c) {
              return [
                  'value' => $c->getId(),
                  'label' => $c->getCities()
              ];
          }, $cities);
          return new JsonResponse($r);
      }
}
