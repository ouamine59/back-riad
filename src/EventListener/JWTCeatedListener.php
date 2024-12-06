<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        // Récupérer les données actuelles du payload
        $payload = $event->getData();

        // Récupérer l'utilisateur connecté
        $user = $event->getUser();

        if ($user instanceof UserInterface) {
            // Ajouter des données spécifiques au payload
            $payload['email'] = $user->getEmail();
            $payload['roles'] = $user->getRoles();

            // Exemple : ajouter une donnée personnalisée
            $payload['id'] = $user->getId();
            $payload['firstName'] = $user->getFirstName();
            $payload['lastName'] = $user->getLastName();
        }

        // Mettre à jour le payload
        $event->setData($payload);
    }
}