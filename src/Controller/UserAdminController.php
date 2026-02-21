<?php

namespace MartenaSoft\UserBundle\Controller;

use MartenaSoft\CommonLibrary\Dictionary\DictionaryMessage;
use MartenaSoft\CommonLibrary\Dictionary\DictionaryUser;
use MartenaSoft\CommonLibrary\Helper\StringHelper;
use MartenaSoft\SiteBundle\Dto\ActiveSiteDto;
use MartenaSoft\UserBundle\Entity\Role;
use MartenaSoft\UserBundle\Entity\User;
use MartenaSoft\UserBundle\Form\UserType;
use MartenaSoft\UserBundle\Manager\UserAdminManager;
use MartenaSoft\UserBundle\Repository\UserRepository;
use MartenaSoft\UserBundle\Service\UserRoleService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}/admin/user')]

class UserAdminController extends AbstractController
{
    #[Route('/', name: 'app_user_admin_index', methods: ['GET'],  priority: 100)]
    #[IsGranted('ROUTE_ACCESS')]
    public function index(
        PaginatorInterface $paginator,
        Request            $request,
        UserRepository $userRepository
    ): Response {
        /** @var ActiveSiteDto $activeSite */
        $activeSite = $request->attributes->get('active_site');
        $userPermissions = $request->attributes->get('user_permissions');
        $queryBuilder = $userRepository->getQueryBuilder(userPermissions: $userPermissions);
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            options: [
                'distinct' => false,
            ]
        );

        return $this->render(sprintf('@User/%s/user_admin/index.html.twig', $activeSite->templatePath), [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_user_admin_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROUTE_ACCESS')]
    public function new(
        Request $request,
        UserAdminManager $userAdminManager,
        LoggerInterface $logger
    ): Response {
        $activeSite = $request->attributes->get('active_site');
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $userAdminManager->create($user, $activeSite);
                $this->addFlash('success', DictionaryMessage::USER_CREATED);
                return $this->redirectToRoute(
                    'app_user_profile_change_password',
                    ['uuid' => $user->getUuid()->toString()],
                    Response::HTTP_SEE_OTHER
                );
            } catch (\Throwable $exception) {
                $this->addFlash('danger',DictionaryMessage::USER_CREATED_ERROR);
                StringHelper::exceptionLoggerHelper(DictionaryMessage::USER_CREATED_ERROR, $exception, $logger);
            }
        }

        return $this->render(sprintf('@User/%s/user_admin/new.html.twig', $activeSite->templatePath), [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{uuid}', name: 'app_user_profile_delete', methods: ['GET'])]
    #[Route('/delete-profile/{uuid}', name: 'app_user_profile_safe_delete', methods: ['GET'])]
    #[IsGranted('ROUTE_ACCESS', subject: 'user')]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        $route = $request->attributes->get('_route');

        if ($route === 'app_user_profile_safe_delete') {
            $user->setStatus(DictionaryUser::STATUS_DELETED);
            $user->setDeletedAt(new \DateTime());
            $userRepository->save($user);
            return $this->redirectToRoute('app_logout');

        } else {
            $userRepository->delete($user);
        }

        return $this->redirectToRoute('app_user_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
