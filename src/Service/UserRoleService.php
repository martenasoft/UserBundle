<?php

namespace MartenaSoft\UserBundle\Service;

use MartenaSoft\CommonLibrary\Dto\ActiveSiteDto;
use MartenaSoft\UserBundle\Entity\Role;
use MartenaSoft\UserBundle\Entity\User;
use MartenaSoft\UserBundle\Repository\RoleRepository;

class UserRoleService
{
    public function __construct(private RoleRepository $roleRepository)
    {
    }
    public function addUserRoles(User $user, array $roles, ActiveSiteDto $activeSiteDto): void
    {
        $collection = $user->getRoleEntities();

        foreach ($collection as $item) {
          if ($item instanceof Role) {
              $user->removeRole($item);
          } else {
              $collection->remove($item);
          }
        }

        $collection->clear();

        $rolesInDb = $this->roleRepository->findAllByNames($roles, $activeSiteDto->id);
        foreach ($rolesInDb as $item) {
            $user->addRole($item);
        }
    }
}
