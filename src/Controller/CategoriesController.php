<?php

namespace App\Controller;

use App\Entity\Categories;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api/categories')]
class CategoriesController extends AbstractController
{
    #[Route('/select', name: 'app_categories_select')]
    public function listing( EntityManagerInterface $manager): Response
    {
        $repository = $manager->getRepository(Categories::class);
        $categories       = $repository->findAll();
        $r = array_map(function($c) {
            return [
                'value' => $c->getId(),
                'label' => $c->getCategories()
            ];
        }, $categories);
        return new JsonResponse($r);
    }
}
