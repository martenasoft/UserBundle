<?php

namespace MartenaSoft\UserBundle\Repository;

use MartenaSoft\UserBundle\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class RoleRepository extends ServiceEntityRepository
{
    public const ALIAS = 'r';
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function getAllByIndex(string $index, ?int $activeSiteId = null): array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS, self::ALIAS.'.'.$index);

        if ($activeSiteId) {
            $queryBuilder
                ->andWhere(self::ALIAS . '.siteId=:activeSiteId')
                ->setParameter('activeSiteId', $activeSiteId);
        }
        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllByNames(array $names, int $activeSiteId): ?array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder
            ->andWhere(self::ALIAS . '.siteId=:activeSiteId')
            ->setParameter('activeSiteId', $activeSiteId)
            ->andWhere(self::ALIAS.'.name IN (:roles)')
            ->setParameter("roles", $names)
        ;
        return $queryBuilder->getQuery()->getResult();
    }

    public function save(Role $role, bool $isFlush = true): void
    {
        $this->getEntityManager()->persist($role);
        if ($isFlush) {
            $this->getEntityManager()->flush();
        }
    }

    public function delete(Role $role, bool $isFlush = true): void
    {
        $this->getEntityManager()->remove($role);
        if ($isFlush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getQueryBuilder(int $activeSiteId): QueryBuilder
    {
        return $this
            ->createQueryBuilder(self::ALIAS)
            ->leftJoin(self::ALIAS.'.permissions', 'permissions')->addSelect('permissions')
            ->andWhere(self::ALIAS.'.siteId = :siteId')
            ->setParameter("siteId", $activeSiteId)
            ;
    }

}
