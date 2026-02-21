<?php

namespace MartenaSoft\UserBundle\Entity;

use MartenaSoft\CommonLibrary\Entity\Interfaces\AuthorInterface;
use MartenaSoft\CommonLibrary\Entity\Traits\AuthorTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\NameTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\PostgresIdTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\PreviewTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\SiteIdTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\UuidTrait;
use MartenaSoft\UserBundle\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Permission  implements AuthorInterface
{
    use
        PostgresIdTrait,
        UuidTrait,
        NameTrait,
        PreviewTrait,
        AuthorTrait,
        SiteIdTrait
        ;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'permissions')]
    private Collection $users;

    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'permissions')]
    private Collection $roles;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $route = null;

    #[ORM\Column(nullable: true)]
    private ?int $countOfUsers = null;

    #[ORM\Column(nullable: true)]
    private ?int $countOfRoles = null;


    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
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
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addPermission($this);
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        if ($this->roles->removeElement($role)) {
            $role->removePermission($this);
        }

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): static
    {
        $this->route = $route;

        return $this;
    }

    public function getCountOfUsers(): ?int
    {
        return $this->countOfUsers;
    }

    public function setCountOfUsers(?int $countOfUsers): static
    {
        $this->countOfUsers = $countOfUsers;

        return $this;
    }

    public function getCountOfRoles(): ?int
    {
        return $this->countOfRoles;
    }

    public function setCountOfRoles(?int $countOfRoles): static
    {
        $this->countOfRoles = $countOfRoles;

        return $this;
    }
}
