<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['USER_CONNECT', 'USER_ADMIN']);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'USER_CONNECT':
                return true;
            case 'USER_ADMIN':
                return $user->getRole() === 'ROLE_ADMIN';
        }

        return false;
    }
}
