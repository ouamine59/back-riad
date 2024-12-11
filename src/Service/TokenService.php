<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CitiesRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


class TokenService
{
    private $jwtManager;
    private $security;
    private $citiesRepository;
    public function __construct(JWTTokenManagerInterface $jwtManager, Security $security,CitiesRepository $citiesRepository)
    {
        $this->jwtManager = $jwtManager;
        $this->security = $security;
        $this->citiesRepository = $citiesRepository;
    }
    public function regenerateToken(): string
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new \Exception('Authenticated user is not an instance of App\Entity\User.');
        }
    
        // Récupération de la ville associée à l'utilisateur
        $city = $this->citiesRepository->findOneBy(['id' => $user->getCities()]);
    
        if (!$city) {
            throw new \Exception('City not found for the given user.');
        }
    
        // Création des données utilisateur pour le token
        $userData = [
            "id" => $user->getId(),
            "firstName" => $user->getFirstName(),
            "lastName" => $user->getLastName(),
            "email" => $user->getEmail(),
            "roles" => $user->getRoles(),
            "phone" => $user->getPhone(),
            "adress" => $user->getAdress(),
            "cityName" => $city->getCities() // Ajout du nom de la ville
        ];
    
        // Génération du token
        return $this->jwtManager->createFromPayload($user, $userData);
    }
}
