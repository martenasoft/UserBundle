<?php

namespace MartenaSoft\UserBundle\Voter;

use Doctrine\Common\Collections\Collection;
use MartenaSoft\CommonLibrary\Dictionary\DictionaryUser;
use MartenaSoft\CommonLibrary\Entity\Interfaces\AuthorInterface;
use MartenaSoft\SiteBundle\Dto\ActiveSiteDto;
use MartenaSoft\UserBundle\Entity\Permission;
use MartenaSoft\UserBundle\Repository\PermissionRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminLocalVoter extends Voter
{
    private const string LOG_PREFIX = 'Check user permissions';

    public function __construct(
        private RequestStack $request,
        private readonly PermissionRepository $permissionRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === DictionaryUser::ROUTE_ACCESS;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        $request = $this->request->getCurrentRequest();
        $route = $request?->attributes->get('_route');
        if (!$route) {
            $this->logger->error(self::LOG_PREFIX . ' route not found!');
            return false;
        }

        if (!$user) {
            $this->logger->info(self::LOG_PREFIX . ' User is empty. Skip!');
            return false;
        }

        $roles = $user->getRoles();
        if (in_array(DictionaryUser::ADMIN_ROLE, $roles)) {
            $request->attributes->set('user_permissions', ['is_admin' => true, 'roles' => $roles, 'user' => $user]);
            return true;
        }

        /** @var ActiveSiteDto $activeSite */
        $activeSite = $request->attributes->get('active_site');
        $roles = $user->getRoleEntities();

        $permissions = $this->getPermission($roles, $activeSite, $route);

        $request->attributes->set('user_permissions', ['is_admin' => false, 'roles' => $roles, 'permissions' => $permissions, 'user' => $user]);
        if ($permissions) {
            $this->logger->notice(self::LOG_PREFIX . ' permission found!', [
                'permissions' => $permissions,
                'userId' => $user->getId(),
            ]);
            return true;
        }

        if ($subject instanceof AuthorInterface && $subject->getAuthor() === $user->getUuid()->toString()) {
            $request->attributes->set('user_permissions', ['is_admin' => false, 'is_owner' => true, 'roles' => $roles, 'user' => $user]);
            $this->logger->info(self::LOG_PREFIX . ' Object belongs to author.', [
                'class' => get_class($subject),
                'objectId' => $subject->getAuthor(),
                'userUuid' => $user->getUuid()->toString(),
            ]);
            return true;
        }

        $this->logger->error(self::LOG_PREFIX . ' permission not found!', [
            'route' => $route,
            'userRoles' => $user->getRoles(),
            'userPermissions' => $user->getPermissions(),
            'allowedPermissions' => $this->permissionRepository->findOneByRoute($route, $activeSite->id),
            'userUuid' => $user->getUuid()->toString(),
        ]);

        return false;
    }

    private function getPermission(Collection $roles, ActiveSiteDto $activeSite, ?string $route): ?Permission
    {
        foreach ($roles as $role) {
            if ($role->getSiteId() !== $activeSite->id) {
                continue;
            }

            foreach ($role->getPermissions() as $permission) {
                if ($permission->getSiteId() !== $activeSite->id) {
                    continue;
                }

                if ($permission->getPermission() !== DictionaryUser::PERMISSION_PRIVATE_TYPE) {
                    return $permission;
                }

                if ($permission->getRoute() === $route) {
                    return $permission;
                }

            }
        }
        return null;
    }
}
