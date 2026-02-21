<?php

namespace MartenaSoft\UserBundle\Service;

use MartenaSoft\SdkBundle\Service\Interfaces\RoleSdkInterface;
use MartenaSoft\UserBundle\Repository\PermissionRepository;

readonly class PermissionSdk implements RoleSdkInterface
{
    public function __construct(
        private PermissionRepository $repository,
    ) {

    }
    public function getCount(array $filter = []): int
    {
        return $this->repository->count($filter);
    }
}
