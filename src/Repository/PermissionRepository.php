<?php

namespace MartenaSoft\UserBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use MartenaSoft\UserBundle\Entity\Permission;
use MartenaSoft\UserBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PermissionRepository extends ServiceEntityRepository
{
    public const ALIAS = 'perm';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }

    public function getQueryBuilder(int $activeSiteId): QueryBuilder
    {
        $queryBuilder = $this
            ->createQueryBuilder(self::ALIAS)
            ->leftJoin(self::ALIAS . '.roles', 'roles')->addSelect('roles')
            ->andWhere(self::ALIAS . '.siteId=:activeSiteId')
            ->setParameter('activeSiteId', $activeSiteId)
        ;
        return $queryBuilder;
    }

    public function findOneByRoute(string $route, int $activeSiteId, ?User $user = null): ?Permission
    {
        $queryBuilder = $this->getQueryBuilder($activeSiteId);

        if (!empty($route)) {
            $queryBuilder
                ->andWhere(self::ALIAS . ".route=:route")
                ->setParameter("route", $route);
        }

        $roles = $user?->getRoles() ?? [];

        if (!empty($user)) {
            $queryBuilder
                ->leftJoin(self::ALIAS . ".roles", "r")->addSelect("r")
                ->leftJoin(self::ALIAS . ".users", "u")->addSelect("u");

            if (!empty($roles)) {
                $queryBuilder
                    ->andWhere("(r.name IN (:roles) OR u.id IN (:users))")
                    ->setParameter("roles", $roles);
            } else {
                $queryBuilder->andWhere("u.id IN (:users)");
            }

            $queryBuilder->setParameter("users", $user);

        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function saveCount(Permission $permission, int $activeSiteId)
    {
        $queryBuilder = $this
            ->getQueryBuilder($activeSiteId)
            ->select("COUNT(u.id) as uCount, COUNT(r.id) as rCount")
            ->leftJoin(self::ALIAS . ".users", "u")
            ->leftJoin(self::ALIAS . ".roles", "r")
            ->andWhere(self::ALIAS . ".id=:id")
            ->setParameter("id", $permission->getId());

        $result = $queryBuilder->getQuery()->getOneOrNullResult() ?? ['uCount' => 0, 'rCount' => 0];
        $permission
            ->setCountOfRoles($result['rCount'])
            ->setCountOfUsers($result['uCount']);
    }

    public function save(Permission $permission, bool $flush = true)
    {
        $this->getEntityManager()->persist($permission);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function delete(Permission $permission, bool $flush = true)
    {
        $this->getEntityManager()->remove($permission);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
