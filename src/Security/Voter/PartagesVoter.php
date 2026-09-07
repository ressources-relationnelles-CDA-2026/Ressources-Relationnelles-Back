<?php

namespace App\Security\Voter;

use App\Entity\Partages;
use App\Entity\Utilisateurs;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PartagesVoter extends Voter
{
    public const EDIT = 'PARTAGE_EDIT';
    public const DELETE = 'PARTAGE_DELETE';
    public const VIEW = 'PARTAGE_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])
            && $subject instanceof Partages;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Utilisateurs) {
            return false;
        }

        /** @var Partages $partage */
        $partage = $subject;

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($partage, $user);
            case self::EDIT:
                return $this->canEdit($partage, $user);
            case self::DELETE:
                return $this->canDelete($partage, $user);
            default:
                return false;
        }
    }

    private function canView(Partages $partage, Utilisateurs $user): bool
    {
        return $partage->getUtilisateur() === $user
            || $partage->getUtilisateur2() === $user;
    }

    private function canEdit(Partages $partage, Utilisateurs $user): bool
    {
        return $partage->getUtilisateur() === $user;
    }

    private function canDelete(Partages $partage, Utilisateurs $user): bool
    {
        return $partage->getUtilisateur() === $user;
    }
}
