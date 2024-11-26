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
#[Route('/api/user')]
    
class UserController extends AbstractController
{
    #[Route('/register', name: 'app_user_register', methods:["POST"])]
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
    #[Route('/update/{id}', name: 'app_user_update', methods: ['PUT'])]
    #[IsGranted(new Expression('is_granted("ROLE_USER")'))]
    public function update(
        int $id,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Request $request,
        UserPasswordHasherInterface $passwordHasher, 
        CitiesRepository $citiesRepository
    ): Response {
        try {
            // Rechercher l'utilisateur par son ID
            $client = $entityManager->getRepository(User::class)->find($id);

            // Vérifier si l'utilisateur existe
            if (!$client) {
                return new JsonResponse(
                    ['result' => 'User not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            // Décoder les données JSON envoyées dans la requête
            $data = json_decode($request->getContent(), true);

            // Vérifier la présence des clés nécessaires dans les données JSON
            if (!$data || !isset($data['email'],
                $data['password'],
                $data['phone'],
                $data['firstName'],
                $data['lastName'],

                $data['adress'],
                $data['phone'],


                $data['comment'],
                $data['citiesId'],
                )) {
                return new JsonResponse(
                    ['result' => 'Invalid data provided'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Mettre à jour les données de l'utilisateur
            $client->setEmail($data['email']);
            //$client->setRoles($data['roles'] ?? $client->getRoles()); // Garde les rôles existants s'ils ne sont pas fournis
            $client->setPhone($data['phone']);
            $client->setFirstName($data['firstName']);
            $client->setLastName($data['lastName']);

            $client->setAdress($data['adress']);
            $client->setPhone($data['phone']);
            $client->setComment($data['comment']);
            $cities = $citiesRepository->findOneBy(array("id"=>$data['citiesId']));
            if(!$cities ){
                return new JsonResponse(
                    ['result' => 'Cities not found'],
                    Response::HTTP_NOT_FOUND
                );
            }
            $client->setCities($cities);
            
            

            // Hashage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($client, $data['password']);
            $client->setPassword($hashedPassword);

            // Validation des données mises à jour
            $errors = $validator->validate($client);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse(
                    ['result' => $errorMessages],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Persister et sauvegarder les changements
            $entityManager->flush();

            return new JsonResponse(
                ['result' => 'User updated successfully'],
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new JsonResponse(['result' => 'Database error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
  


 
  
}
