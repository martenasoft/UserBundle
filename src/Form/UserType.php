<?php

namespace MartenaSoft\UserBundle\Form;


use MartenaSoft\CommonLibrary\Dictionary\DictionaryUser;
use MartenaSoft\UserBundle\Entity\Role;
use MartenaSoft\UserBundle\Entity\User;
use MartenaSoft\UserBundle\Repository\PermissionRepository;
use MartenaSoft\UserBundle\Repository\RoleRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AbstractType
{
    public function __construct(
        private RoleRepository $roleRepository,
        private PermissionRepository $permissionRepository,
        private TranslatorInterface $translator
    )
    {

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $userRoles = [];
        $permissions = [];
        if ($options['data'] instanceof UserInterface) {
            $userRoles = $options['data']->getRoles();
            $permissions = $options['data']->getPermissions();
        }

        $builder->add('email');
        if ($options['isAdmin']) {
            $builder->add('roles', ChoiceType::class, [
                'choices' => $this->getRoles(),
                'data' => $userRoles,
                'expanded' => true,
                'multiple' => true
            ])
                ->add('status', ChoiceType::class, [
                    'choices' => array_map(function($item) {
                        return $this->translator->trans($item);
                    }, DictionaryUser::CHOICE_STATUSES),
                ])
                ->add('isVerified', CheckboxType::class, [
                    'label' => 'Is Verified'
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'isAdmin' => false,
        ]);
    }
    private function getRoles(): array
    {
        $roles = $this->roleRepository->findAll();
        $rolesArray = DictionaryUser::ROLES;
        /**
         * @var Role $role
         */
        foreach ($roles as $role) {
            $rolesArray[] = $role->getName();
        }

        return array_combine($rolesArray, $rolesArray);
    }

    private function getPermissions(): array
    {
        $permissions = $this->permissionRepository->findAll();
        $permissionsArray = [];
        /**
         * @var Role $role
         */
        foreach ($permissions as $permission) {
            $permissionsArray[$permission->getName()] = $permission->getId();
        }

        return $permissionsArray;
    }
}
