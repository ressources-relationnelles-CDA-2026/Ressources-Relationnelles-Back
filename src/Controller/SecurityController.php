<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use App\Repository\RolesUtilisateursRepository;
use App\Repository\UtilisateursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends AbstractController
{
    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function login(): JsonResponse
    {
        return new JsonResponse(['message' => 'This should not be reached']);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UtilisateursRepository $utilisateursRepository,
        RolesUtilisateursRepository $rolesRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['message' => 'Payload JSON invalide'], 400);
        }

        $requiredFields = ['email', 'pseudo', 'password'];

        $errors = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                $errors[] = sprintf(
                    'Le champ "%s" est obligatoire.',
                    $field
                );
            }
        }

        if (!empty($errors)) {
            return new JsonResponse([
                'message' => implode("\n", $errors),
            ], 422);
        }

        $constraints = new Assert\Collection([
            'email' => [
                new Assert\Email(
                    message: 'L\'adresse email n\'est pas valide.'
                ),
            ],
            'telephone' => [
                new Assert\Regex(
                    pattern: '/^(?:\+33|0)[1-9](?:[0-9]{8})$/',
                    message: 'Le numéro de téléphone n\'est pas valide.'
                ),
            ],
            'password' => [
                new Assert\Length(
                    min: 8,
                    minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
                ),
            ],
            'pseudo' => [
                new Assert\Length(
                    min: 3,
                    max: 255,
                    minMessage: 'Le pseudo doit contenir au moins {{ limit }} caractères.',
                    maxMessage: 'Le pseudo ne peut pas dépasser {{ limit }} caractères.'
                ),
            ],
        ], allowExtraFields: true, allowMissingFields: true);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];

            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            return new JsonResponse([
                'message' => implode("\n", $errors),
            ], 422);
        }

        if ($utilisateursRepository->findOneBy(['email' => $data['email']])) {
            return new JsonResponse(['message' => 'Cet email est déjà utilisé'], 409);
        }

        $user = new Utilisateurs();
        $user
            ->setNom(trim((string) ($data['nom'] ?? '')))
            ->setPrenom(trim((string) ($data['prenom'] ?? '')))
            ->setTelephone(trim((string) ($data['telephone'] ?? '')))
            ->setEmail(trim((string) $data['email']))
            ->setPseudo(trim((string) $data['pseudo']))
            ->setPhotoProfil(isset($data['photoProfil']) ? trim((string) $data['photoProfil']) : null)
            ->setStatusCompte(true);

        $user->setMotDePasse($passwordHasher->hashPassword($user, (string) $data['password']));

        $roleUser = $rolesRepository->findOneBy(['libelle' => 'ROLE_USER']);
        if ($roleUser !== null) {
            $user->setRole($roleUser);
        } else {
            return new JsonResponse(['message' => 'Rôle ROLE_USER introuvable'], 500);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Inscription réussie',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'pseudo' => $user->getPseudo(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'telephone' => $user->getTelephone(),
                'roles' => $user->getRoles(),
            ],
        ], 201);
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof Utilisateurs) {
            return new JsonResponse([
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'pseudo' => $user->getPseudo(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'telephone' => $user->getTelephone(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $response = new JsonResponse([
            'message' => 'Déconnexion réussie',
        ]);

        $response->headers->clearCookie(
            'JWT',
            '/',
        );

        return $response;
    }
}
