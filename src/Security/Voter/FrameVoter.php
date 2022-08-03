<?php

namespace App\Security\Voter;

use App\Entity\Frame;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FrameVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const VIEW = 'VIEW';

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\Frame;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof Frame) {
            return false;
        }
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $subject->getProject()->getCreatedBy() === $user;
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case self::VIEW:
                return $subject->getProject()->getCreatedBy() === $user;
                // logic to determine if the user can VIEW
                // return true or false
                break;
        }

        return false;
    }
}
