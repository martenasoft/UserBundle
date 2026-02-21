<?php

namespace MartenaSoft\UserBundle\Entity;


use MartenaSoft\CommonLibrary\Entity\Interfaces\AuthorInterface;
use MartenaSoft\CommonLibrary\Entity\Traits\AuthorTrait;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class SuperAdminUser implements
    UserInterface,
    PasswordAuthenticatedUserInterface,
    RedirectToInterface,
    AuthorInterface
{
    use AuthorTrait;
    private int $id = 0;
    private Uuid|null $uuid =  null;

    private string $redirectToRoute = '';

    public function __construct(
        private string $email,
        private string $passwordHash
    ) {
        $this->uuid = Uuid::fromString("00000000-0000-0000-0000-000000000001");
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(?Uuid $uuid): SuperAdminUser
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->uuid->toRfc4122();
    }

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function getRoles(): array
    {
        return ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN'];
    }

    public function eraseCredentials(): void {}

    public function getRedirectToRoute(): string
    {
        return $this->redirectToRoute;
    }

    public function setRedirectToRoute(string $redirectToRoute): self
    {
        $this->redirectToRoute = $redirectToRoute;
        return $this;
    }
}
