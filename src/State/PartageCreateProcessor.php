<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Partages;
use App\Entity\Utilisateurs;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PartageCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Partages) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof Utilisateurs) {
            throw new AccessDeniedException('Utilisateur non authentifie.');
        }

        $resource = $data->getResource();
        if ($resource === null || $resource->getUtilisateur() !== $currentUser) {
            throw new AccessDeniedException('Une ressource ne peut etre partagee que par son proprietaire.');
        }

        $data->setUtilisateur($currentUser);
        $data->setDatePartage(new \DateTime());

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}