<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use ApiPlatform\Validator\Exception\ValidationException;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
                KernelEvents::EXCEPTION => ['onKernelException', -64],
            ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception instanceof ValidationException) {
            $violations = [];

            foreach ($exception->getConstraintViolationList() as $violation) {
                $violations[] = [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            $event->setResponse(new JsonResponse([
                'status' => 422,
                'message' => 'Les données fournies sont invalides.',
                'violations' => $violations,
            ], 422));

            return;
        }

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        $messages = [
            400 => 'La requête est invalide.',
            401 => 'Vous devez être authentifié pour accéder à cette ressource.',
            403 => 'Vous n’avez pas les droits nécessaires pour effectuer cette action.',
            404 => 'La ressource demandée est introuvable.',
            405 => 'Cette méthode HTTP n’est pas autorisée.',
            500 => 'Une erreur interne est survenue.',
        ];

        $message = $messages[$statusCode] ?? 'Une erreur est survenue.';

        $event->setResponse(new JsonResponse([
            'status' => $statusCode,
            'message' => $message,
        ], $statusCode));
    }
}
