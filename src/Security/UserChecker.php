<?php

namespace MartenaSoft\UserBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        //TODO: check if active
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {

        if (!$user) {
            throw new CustomUserMessageAccountStatusException('Your user account not found.');
        }
    }
}
