<?php

namespace MartenaSoft\UserBundle\Repository;

use Doctrine\ORM\Query\Expr;
use MartenaSoft\CommonLibrary\Dictionary\DictionaryUser;
use MartenaSoft\ImageBundle\Entity\Image;
use MartenaSoft\UserBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use MartenaSoft\UserBundle\Repository\Traits\PermissionsTrait;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface, PasswordUpgraderInterface
{

    use PermissionsTrait;
    public const string ALIAS = 'u';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByUuid(string $uuid, ?array $statuses = null, ?array $userPermissions = null): ?User
    {
        return $this
            ->getQueryBuilder($statuses, $userPermissions)
            ->leftJoin('u.roles', 'role')->addSelect('role')
            ->leftJoin('role.permissions', 'permissions')->addSelect('permissions')
            ->andWhere('u.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getQueryBuilder(?array $statuses = null, ?array $userPermissions = null): QueryBuilder
    {
        $queryBuilder = $this
            ->createQueryBuilder(self::ALIAS)
        ;

        if (!empty($statuses)) {
            $queryBuilder
                ->andWhere(self::ALIAS . '.status IN(:statuses)')
                ->setParameter('statuses', $statuses)
            ;
        }

        $this->permissionsQueryBuilder($queryBuilder, self::ALIAS, $userPermissions);
        return $queryBuilder;
    }

    public function loadUserByUsername(string $username)
    {
        $this->loadUserByIdentifier($username);
    }

    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        $queryBuilder = $this
            ->createQueryBuilder('u')
            ->leftJoin('u.roles', 'r')->addSelect('r')
            ->leftJoin('r.permissions', 'p')->addSelect('p')
            ->andWhere('u.email=:identifier')
            ->setParameter('identifier', $identifier)
            ->andWhere('u.status=:status')
            ->setParameter('status', DictionaryUser::STATUS_ACTIVE)
            ->andWhere('u.isVerified=:isVerified')
            ->setParameter('isVerified', true)
        ;

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();
    }


    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function save(UserInterface $user, bool $flush = true): void
    {
        $this->getEntityManager()->persist($user);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function delete(UserInterface $user, bool $flush = true): void
    {
        $this->getEntityManager()->remove($user);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
