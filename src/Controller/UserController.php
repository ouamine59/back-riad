<?php

namespace App\Controller;
use App\Entity\User;
use DateTimeImmutable;
use App\Repository\UserRepository;
use App\Repository\CitiesRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MediaObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/api/user/register', name: 'app_user_register', methods:["POST"])]
    public function index(EntityManagerInterface $entityManager, 
    ValidatorInterface $validator, 
    Request $request, 
    UserPasswordHasherInterface $passwordHasher,
    CitiesRepository $citiesRepository): Response
    {
        try {
            $data = $request->getContent();
            // Traite les données (par exemple, décoder le JSON si nécessaire)
            $jsonData = json_decode($data, true);
            if ($jsonData === null) {
                return new JsonResponse(['result' => 'Invalid JSON format'], Response::HTTP_BAD_REQUEST);
            }
            if (!isset($jsonData['email'], 
            $jsonData['password'], 
            $jsonData['phone'], 
            $jsonData['firstName'], 
            $jsonData['lastName'],
            $jsonData['idCities'],
            $jsonData['adress'])) {
                return new JsonResponse(['result' => 'Data missing'], Response::HTTP_BAD_REQUEST);
            }
            $cities = $citiesRepository->findOneBy(array("id"=>$jsonData['idCities']));
            if (!$cities) {
                return new JsonResponse(['result' => 'City not found'], Response::HTTP_BAD_REQUEST);
            }
            $client = new User();
            $client->setEmail($jsonData['email']);
            $client->setRoles(["ROLE_CLIENT"]);
            $hashedPassword = $passwordHasher->hashPassword($client, $jsonData['password']);
            $client->setPassword($hashedPassword);
            $client->setPhone($jsonData['phone']);
            $client->setFirstName($jsonData['firstName']);
            $client->setLastName($jsonData['lastName']);
            $date = new \DateTimeImmutable();
            $client->setIsCreatedAt($date);
            $client->setAdress($jsonData['adress']);
            $client->setCities($cities);
            $errors = $validator->validate($client);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                return new JsonResponse(
                    ['result' => 'Validation failed', 'errors' => $errorMessages],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $entityManager->persist($client);
            $entityManager->flush();
            return new JsonResponse(
                ['result' => 'User registered successfully'],
                Response::HTTP_CREATED
            );
        }catch (\Exception $e) {
            return new JsonResponse(['result' => 'Internal server error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
  


 
  
}
