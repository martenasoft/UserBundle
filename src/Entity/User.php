<?php

namespace MartenaSoft\UserBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MartenaSoft\CommonLibrary\Dictionary\DictionaryUser;
use MartenaSoft\CommonLibrary\Entity\Interfaces\AuthorInterface;
use MartenaSoft\CommonLibrary\Entity\Traits\AuthorTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\CreatedAtTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\DeletedAtTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\PostgresIdTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\SiteIdTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\StatusTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\UpdatedAtTrait;
use MartenaSoft\CommonLibrary\Entity\Traits\UuidTrait;
use MartenaSoft\UserBundle\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'mrs_user')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ORM\HasLifecycleCallbacks]
class User implements
    UserInterface,
    PasswordAuthenticatedUserInterface,
    AuthorInterface,
    RedirectToInterface
{
    use
        PostgresIdTrait,
        UuidTrait,
        StatusTrait,
        CreatedAtTrait,
        UpdatedAtTrait,
        DeletedAtTrait,
        AuthorTrait,
        SiteIdTrait
        ;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\ManyToMany(targetEntity: Permission::class, mappedBy: 'users')]
    private Collection $permissions;

    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'users')]
    private Collection $roles;
    private Collection $userRoles;

    #[ORM\Column]
    private string $redirectToRoute = '';

    public function __construct()
    {
        $this->permissions = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }


    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

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
            $permission->addUser($this);
        }

        return $this;
    }

    public function removePermission(Permission $permission): static
    {
        if ($this->permissions->removeElement($permission)) {
            $permission->removeUser($this);
        }
        return $this;
    }


    /**
     * @return array<int, Role>
     */
    public function getRoles(): array
    {
        $roles[] = DictionaryUser::USER_ROLE;
        foreach ($this->roles as $role) {
            if ($role instanceof Role) {
                $roles[] = $role->getName();
            } else {
                $roles[] = $role;
            }
        }

        return array_unique($roles);
    }

    public function addRole(mixed $role): static
    {
        if (is_string($role)) {
            $this->roles->add($role);
            return $this;
        }
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addUser($this);
        }

        return $this;
    }
    public function removeRole(mixed $role): static
    {
        if (is_string($role)) {
           foreach ($this->roles as $roleItem) {
               if ($roleItem->getName() == $role) {
                   $role = $roleItem;
                   break;
               }
           }
        }

        if ($this->roles->removeElement($role)) {

            $role->removeUser($this);
        }

        return $this;
    }

    public function setRoleCollection(Collection $roles): static
    {
        $this->roles = $roles;
        return $this;
    }
    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }
    public function hasRoles(array $role): bool
    {
        return !empty(array_intersect($role, $this->getRoles()));
    }

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
