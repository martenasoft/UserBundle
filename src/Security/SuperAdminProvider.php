<?php

namespace MartenaSoft\UserBundle\Security;

use MartenaSoft\UserBundle\Entity\SuperAdminUser;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SuperAdminProvider implements UserProviderInterface
{
    public function __construct(
        private string $email,
        private string $passwordHash
    ) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if ($identifier !== $this->email) {
            throw new UserNotFoundException();
        }

        return new SuperAdminUser($this->email, $this->passwordHash);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === SuperAdminUser::class;
    }
}
