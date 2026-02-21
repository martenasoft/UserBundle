<?php

namespace MartenaSoft\UserBundle\Service;

use MartenaSoft\SdkBundle\Service\Interfaces\RoleSdkInterface;
use MartenaSoft\UserBundle\Repository\RoleRepository;

readonly class RoleSdk implements RoleSdkInterface
{
    public function __construct(
        private RoleRepository $roleRepository,
    ) {

    }
    public function getCount(array $filter = []): int
    {
        return $this->roleRepository->count($filter);
    }
}
