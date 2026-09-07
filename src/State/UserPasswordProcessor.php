<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Utilisateurs;
use App\Repository\RolesUtilisateursRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private UserPasswordHasherInterface $passwordHasher,
        private RolesUtilisateursRepository $rolesRepo
    ) {}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof Utilisateurs) {
            $plainPassword = $data->getPlainPassword();
            if ($plainPassword !== null && $plainPassword !== '') {
                $hash = $this->passwordHasher->hashPassword($data, $plainPassword);
                $data->setMotDePasse($hash);
                $data->eraseCredentials();
            }

            if ($data->getDateCreation() === null) {
                $data->setDateCreation(new \DateTime()); 
            }

            if ($data->isStatusCompte() === null) {
                $data->setStatusCompte(true);
            }

            if ($data->getRole() === null) {
                $roleUser = $this->rolesRepo->findOneBy(['libelle' => 'ROLE_USER']);
                if ($roleUser) {
                    $data->setRole($roleUser);
                }
            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
