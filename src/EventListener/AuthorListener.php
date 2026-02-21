<?php

namespace MartenaSoft\UserBundle\EventListener;

use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use MartenaSoft\CommonLibrary\Entity\Interfaces\AuthorInterface;
use MartenaSoft\UserBundle\Entity\SuperAdminUser;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class AuthorListener
{
    public function __construct(private Security $security)
    {

    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->set($args->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->set($args->getObject());
    }

    private function set(object $object):void
    {
        if (!$this->security?->getUser()) {
            return;
        }

        if (!$object instanceof AuthorInterface) {
            return;
        }
        $user = $this->security->getUser();
        $object->setAuthor($user->getUuid()->toString());
    }
}
