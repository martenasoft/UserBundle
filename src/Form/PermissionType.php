<?php

namespace MartenaSoft\UserBundle\Form;


use MartenaSoft\UserBundle\Entity\Permission;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use MartenaSoft\UserBundle\Service\RouteService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PermissionType extends AbstractType
{
    public function __construct(private RouteService $routeService)
    {

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $routes = $this->routeService->list('/^(app_|menu_)(.*)/');
        $routesGroups = $this->routeService->rolesDetail($routes);
        $builder
            ->add('name')
            ->add('preview', TextareaType::class)

            ->add('route', ChoiceType::class, [
                'choices' => $routesGroups,
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
