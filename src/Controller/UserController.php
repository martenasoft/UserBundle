<?php

namespace MartenaSoft\UserBundle\Controller;

use MartenaSoft\CommonLibrary\Dictionary\DictionaryMessage;
use MartenaSoft\CommonLibrary\Dictionary\DictionaryUser;
use MartenaSoft\CommonLibrary\Helper\StringHelper;
use MartenaSoft\CommonLibrary\Dto\ActiveSiteDto;
use MartenaSoft\UserBundle\Entity\User;
use MartenaSoft\UserBundle\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use MartenaSoft\UserBundle\Form\UserType;
use MartenaSoft\UserBundle\Repository\UserRepository;
use MartenaSoft\UserBundle\Service\UserImageService;
use MartenaSoft\UserBundle\Service\UserRoleService;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}/profile')]
class UserController extends AbstractController
{
    #[Route('/{uuid}', name: 'app_user_profile', methods: ['GET'])]
    #[IsGranted('ROUTE_ACCESS', subject: 'user')]
    public function show(
        Request $request,
        #[MapEntity(expr: 'repository.findOneByUuid(uuid)')] User $user,
        UserImageService $userImageService,
    ): Response
    {
        $activeSite = $request->attributes->get('active_site');
        $userPermissions = $request->attributes->get('user_permissions');
        $activeSiteDto = $request->attributes->get('active_site');
        $images = $userImageService->get(
             uuid: [$user->getUuid()],
            activeSiteDto: $activeSiteDto
        );

        $imageConfig = $userImageService->getImageConfig($activeSiteDto);
        $isAdmin = $userPermissions['is_admin'];
        return $this->render(sprintf('@User/%s/profile.html.twig', $activeSite->templatePath), [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'images' => $images,
            'imagesConfig' => $imageConfig,
        ]);
    }

    #[Route('/change-password/{uuid}', name: 'app_user_profile_change_password', methods: ['GET', 'POST'])]
    #[IsGranted('ROUTE_ACCESS', subject: 'user')]
    public function changePassword(
        Request                     $request,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] User                        $user,
        EntityManagerInterface      $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        LoggerInterface             $logger
    ): Response {
        $activeSite = $request->attributes->get('active_site');
        $userPermissions = $request->attributes->get('user_permissions');
        $isAdmin = $userPermissions['is_admin'] ?? false;

        $form = $this->createForm(ChangePasswordFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $encodedPassword = $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                );

                $user->setPassword($encodedPassword);
                $entityManager->flush();
                $this->addFlash('success', DictionaryMessage::PASSWORD_CHANGED);
                return $this->redirectToRoute('app_user_admin_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Throwable $exception) {
                $this->addFlash('danger', DictionaryMessage::ERROR_CHENG_PASSWORD);
                StringHelper::exceptionLoggerHelper(DictionaryMessage::ERROR_CHENG_PASSWORD, $exception, $logger);
            }

        }

        return $this->render(sprintf('@User/%s/change_password.html.twig', $activeSite->templatePath), [
            'user' => $user,
            'form' => $form,
            'isAdmin' => $isAdmin,
        ]);
    }

    #[Route('/edit/{uuid}', name: 'app_user_update', methods: ['GET', 'POST'])]
    #[IsGranted('ROUTE_ACCESS', subject: 'user')]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] User $user,
        EntityManagerInterface $entityManager,
        UserRoleService $roleService,
        LoggerInterface $logger
    ): Response {
        $userPermissions = $request->attributes->get('user_permissions');
        $isAdmin = $userPermissions['is_admin'] ?? false;
        $activeSite = $request->attributes->get('active_site');
        $form = $this->createForm(UserType::class, $user, ['isAdmin' => $isAdmin]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $roleService->addUserRoles($user, $user->getRoles(), $activeSite);
                if (empty($user->getPassword())) {
                    $user->setStatus(DictionaryUser::STATUS_BLOCKED);
                }

                $entityManager->flush();
                $this->addFlash('success', DictionaryMessage::USER_UPDATED);
                return $this->redirectToRoute('app_user_admin_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Throwable $exception) {
                $this->addFlash('danger',DictionaryMessage::USER_UPDATE_ERROR);
                StringHelper::exceptionLoggerHelper(DictionaryMessage::USER_UPDATE_ERROR, $exception, $logger);
            }
        }
        $file = 'edit.html.twig';
        if (!$isAdmin) {
            $file = 'edit_profile.html.twig';
        }

        return $this->render(sprintf('@User/%s/user_admin/' . $file, $activeSite->templatePath), [
            'user' => $user,
            'form' => $form,
        ]);
    }


}
