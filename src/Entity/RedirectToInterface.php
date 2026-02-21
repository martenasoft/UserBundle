<?php

namespace MartenaSoft\UserBundle\Entity;

interface RedirectToInterface
{
    public function getRedirectToRoute(): string;
    public function setRedirectToRoute(string $redirectToRoute): self;
}
