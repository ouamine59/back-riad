<?php

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


class TokenService
{
    private $jwtManager;
    private $security;

    public function __construct(JWTTokenManagerInterface $jwtManager, Security $security)
    {
        $this->jwtManager = $jwtManager;
        $this->security = $security;
    }

    public function regenerateToken(): string
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw new \Exception('No authenticated user found.');
        }

        // Génère un nouveau token avec les données actuelles de l'utilisateur
        return $this->jwtManager->create($user);
    }
}