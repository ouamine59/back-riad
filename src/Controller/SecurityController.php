<?php namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{
    #[Route('/auth', name: 'auth', methods: ['POST'])]
    public function login(#[CurrentUser] $user = null): Response
    {
        // Vérifier si l'utilisateur est null
        if (null === $user) {
            return $this->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Retourner les informations utilisateur
        return $this->json([
            'user' => $user->getUserIdentifier(),
            'id'   => $user->getId(),
        ]);
    }
}