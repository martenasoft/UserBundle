<?php

namespace MartenaSoft\UserBundle\Repository\Traits;

use MartenaSoft\CommonLibrary\Dictionary\DictionaryUser;

trait PermissionsTrait
{
    private function permissionsQueryBuilder($queryBuilder, string $alias,  ?array $userPermissions = null): void
    {
        if (
            !empty($userPermissions)
            && (
                empty($userPermissions['is_admin'])
                && $userPermissions['permissions']?->getPermission() === DictionaryUser::PERMISSION_PRIVATE_TYPE
            )) {

            $queryBuilder
                ->andWhere( "{$alias}.author=:author")
                ->setParameter('author', $userPermissions['user']?->getUuid()->toString())
            ;
        }
    }
}
