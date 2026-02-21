<?php

namespace MartenaSoft\UserBundle\Form;


use MartenaSoft\CommonLibrary\Dictionary\DictionaryUser;
use MartenaSoft\UserBundle\Entity\Permission;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Role;
use Symfony\Bridge\Doctrine\Security\User;
use MartenaSoft\UserBundle\Service\RouteService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class PermissionType extends AbstractType
{
    public function __construct(private RouteService $routeService)
    {

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $routes = $this->routeService->list('/^(app_|menu_)(.*)/');
        $routes = array_column($routes, 'name');
        array_unshift($routes, '');
        $routes = array_combine($routes, $routes);
        $builder
            ->add('name')
            ->add('preview', TextareaType::class)

            ->add('route', ChoiceType::class, [
                'choices' => $routes,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Permission::class,
        ]);
    }
}
