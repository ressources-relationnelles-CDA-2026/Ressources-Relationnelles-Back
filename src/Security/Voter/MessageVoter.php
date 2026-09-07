<?php

namespace App\Security\Voter;

use App\Entity\Message;
use App\Entity\Utilisateurs;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageVoter extends Voter
{
    public const VIEW = 'MESSAGE_VIEW';
    public const EDIT = 'MESSAGE_EDIT';
    public const DELETE = 'MESSAGE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Message && in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Utilisateurs) {
            return false;
        }

        /** @var Message $message */
        $message = $subject;

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        return match ($attribute) {
            self::VIEW => $message->getExpediteur() === $user || $message->getDestinataire() === $user,
            self::EDIT => $message->getExpediteur() === $user,
            self::DELETE => $message->getExpediteur() === $user || $message->getDestinataire() === $user,
            default => false,
        };
    }
}