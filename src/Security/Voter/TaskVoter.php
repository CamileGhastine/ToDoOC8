<?php

namespace App\Security\Voter;

use App\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    protected function supports($attribute, $task): bool
    {
        return in_array($attribute, ['DELETE', 'EDIT'])
            && $task instanceof Task;
    }

    protected function voteOnAttribute($attribute, $task, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'DELETE':
                return $this->canDelete($task, $user);
            case 'EDIT':
                return true;
        }

        return false;
    }

    private function canDelete($task, $user): bool
    {
        if (!$task->getUser() && $user->getRole()=== 'ROLE_ADMIN') {
            return true ;
        }

        if(!$task->getUser()) {
            return false;
        }

        if ($user->getId() === $task->getUser()->getId()) {
            return true;
        }

        return false;
    }
}
