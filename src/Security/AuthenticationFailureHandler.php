<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        if ($exception instanceof TooManyLoginAttemptsAuthenticationException) {
            $minutes = $exception->getMessageData()['%minutes%'] ?? null;

            return new JsonResponse([
                'message' => 'Trop de tentatives de connexion. Reessayez plus tard.',
                'error' => 'login_throttled',
                'retry_after_minutes' => $minutes,
            ], 429);
        }

        return new JsonResponse([
            'message' => 'Identifiant ou mot de passe incorrect',
            'error' => 'invalid_credentials'
        ], 401);
    }
}
