<?php

namespace App\Security\Voter;

use App\Entity\Ressources;
use App\Entity\Utilisateurs;
use App\Entity\VisibiliteStatut;
use App\Repository\AmisRepository;
use App\Repository\UtilisateursRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class RessourcesVoter extends Voter
{
    public const EDIT = 'RESSOURCE_EDIT';
    public const DELETE = 'RESSOURCE_DELETE';
    public const VIEW = 'RESSOURCE_VIEW';

    public function __construct(private AmisRepository $amisRepository, private UtilisateursRepository $utilisateursRepository) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])
            && $subject instanceof Ressources;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        /** @var Ressources $ressource */
        $ressource = $subject;

        if ($attribute === self::VIEW && $this->canView($ressource, $user)) {
            return true;
        }

        if (!$user instanceof Utilisateurs) {
            if (is_string($user)) {
                $user = $this->utilisateursRepository->findOneBy(['email' => $user]);
            } elseif ($user instanceof UserInterface) {
                $user = $this->utilisateursRepository->findOneBy(['email' => $user->getUserIdentifier()]);
            } else {
                return false;
            }

            if (!$user instanceof Utilisateurs) {
                return false;
            }
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($ressource, $user);
            case self::EDIT:
                return $this->canEdit($ressource, $user);
            case self::DELETE:
                return $this->canDelete($ressource, $user);
            default:
                return false;
        }
    }

    private function canView(Ressources $ressource, mixed $user): bool
    {
        if ($ressource->getVisibilite() === VisibiliteStatut::PUBLIC) {
            return true;
        }

        if ($user instanceof Utilisateurs && $ressource->getUtilisateur() === $user) {
            return true;
        }

        if ($user instanceof Utilisateurs && $ressource->getVisibilite() === VisibiliteStatut::AMI) {
            $owner = $ressource->getUtilisateur();

            if ($owner === null || $owner === $user) {
                return false;
            }

            $relation = $this->amisRepository->relationExiste($user->getId(), $owner->getId());

            return $relation !== null && $relation->getStatut() === 'accepte';
        }

        return false;
    }

    private function canEdit(Ressources $ressource, Utilisateurs $user): bool
    {
        $owner = $ressource->getUtilisateur();
        return $owner !== null && (int) $owner->getId() === (int) $user->getId();
    }

    private function canDelete(Ressources $ressource, Utilisateurs $user): bool
    {
        $owner = $ressource->getUtilisateur();
        return $owner !== null && (int) $owner->getId() === (int) $user->getId();
    }
}
