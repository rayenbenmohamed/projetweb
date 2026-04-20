<?php

namespace App\Security;

use App\Entity\Admin;
use App\Entity\Recruiter;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user instanceof Admin || \in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return;
        }

        if ($user->isBlocked()) {
            throw new CustomUserMessageAccountStatusException('Votre compte a été suspendu par un administrateur. Vous ne pouvez pas vous connecter pour le moment.');
        }

        if ($user instanceof Recruiter && !$user->isApproved()) {
            throw new CustomUserMessageAccountStatusException('Votre compte recruteur n’a pas encore été validé par un administrateur. Vous recevrez un accès dès l’approbation.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
