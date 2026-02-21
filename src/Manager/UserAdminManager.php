<?php

namespace MartenaSoft\UserBundle\Manager;

use MartenaSoft\CommonLibrary\Dictionary\DictionaryUser;
use MartenaSoft\CommonLibrary\Helper\StringHelper;
use MartenaSoft\SiteBundle\Dto\ActiveSiteDto;
use MartenaSoft\UserBundle\Entity\User;
use MartenaSoft\UserBundle\Event\SavedUserEvent;
use MartenaSoft\UserBundle\Repository\UserRepository;
use MartenaSoft\UserBundle\Service\UserRoleService;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserAdminManager
{
    public function __construct(
        private UserRoleService $roleService,
        private UserRepository $userRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {

    }
    public function create(User $user, ActiveSiteDto $activeSiteDto): void
    {
        $this->roleService->addUserRoles($user, $user->getRoles(), $activeSiteDto);
        $user->setPassword('');
        $user->setStatus(DictionaryUser::STATUS_BLOCKED);
        $user->setSiteId($activeSiteDto->id);
        $user->setUuid(StringHelper::getRandomUuid());
        $this->eventDispatcher->dispatch(new SavedUserEvent($user));
        $this->userRepository->save($user);

    }
}
