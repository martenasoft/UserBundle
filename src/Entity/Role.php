<?php

namespace MartenaSoft\UserBundle\Entity;

use MartenaSoft\CommonLibrary\Entity\Interfaces\AuthorInterface;
use MartenaSoft\CommonLibrary\Entity\Traits\AuthorTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\NameTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\PostgresIdTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\SiteIdTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\UuidTrait;
use MartenaSoft\UserBundle\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Role implements AuthorInterface
{
    use
        PostgresIdTrait,
        UuidTrait,
        NameTrait,
        AuthorTrait,
        SiteIdTrait
        ;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'roles')]
    private Collection $users;

    #[ORM\ManyToMany(targetEntity: Permission::class, inversedBy: 'roles')]
    private Collection $permissions;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): static
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
        }

        return $this;
    }

    public function removePermission(Permission $permission): static
    {
        $this->permissions->removeElement($permission);

        return $this;
    }
}
