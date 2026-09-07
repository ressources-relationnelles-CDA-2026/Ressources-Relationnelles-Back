<?php

namespace App\Security;

use App\Entity\Utilisateurs;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $user = $token->getUser();

        $jwt = $this->jwtManager->create($token->getUser());

        $userData = null;
        if ($user instanceof Utilisateurs) {
            $userData = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'pseudo' => $user->getPseudo(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'telephone' => $user->getTelephone(),
                'roles' => $user->getRoles(),
            ];
        }

        $response = new JsonResponse([
            'data' => [
                'user' => $userData,
            ]
        ]);

        $response->headers->setCookie(
            Cookie::create(
                'JWT',
                $jwt,
                0,
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'lax'
            )
        );

        return $response;
    }
}
