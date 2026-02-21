<?php

namespace MartenaSoft\UserBundle\Event;

use MartenaSoft\UserBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class SavedUserEvent extends Event
{
    public const string NAME = 'saved_user.event';

    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
