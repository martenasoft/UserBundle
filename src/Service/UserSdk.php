<?php

namespace MartenaSoft\UserBundle\Service;

use MartenaSoft\SdkBundle\Service\Interfaces\UserSdkInterface;
use MartenaSoft\UserBundle\Repository\UserRepository;

readonly class UserSdk implements UserSdkInterface
{
    public function __construct(
        private UserRepository $userSdk
    ) {

    }
    public function getCount(array $filter = []): int
    {
        return $this->userSdk->count($filter);
    }
}
