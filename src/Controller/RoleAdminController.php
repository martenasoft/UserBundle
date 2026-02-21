<?php

namespace MartenaSoft\UserBundle\Controller;

use MartenaSoft\CommonLibrary\Dictionary\DictionaryMessage;
use MartenaSoft\CommonLibrary\Helper\StringHelper;
use MartenaSoft\SiteBundle\Dto\ActiveSiteDto;
use MartenaSoft\UserBundle\Entity\Role;
use MartenaSoft\UserBundle\Form\RoleType;
use MartenaSoft\UserBundle\Repository\RoleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}/admin/role')]
class RoleAdminController extends AbstractController
{
    private const string LOG_PREFIX = 'role-admin';

    #[Route('/', name: 'app_role_admin_index', methods: ['GET'])]
    #[IsGranted('ROUTE_ACCESS')]
    public function index(
        RoleRepository     $roleRepository,
        PaginatorInterface $paginator,
        Request            $request
    ): Response {
        /** @var ActiveSiteDto $activeSite */
        $activeSite = $request->attributes->get('active_site');
        $queryBuilder = $roleRepository->getQueryBuilder($activeSite->id);
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1)
        );

        return $this->render(sprintf('@User/%s/role_admin/index.html.twig', $activeSite->templatePath), [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_role_admin_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROUTE_ACCESS')]
    public function new(
        Request $request,
        RoleRepository $roleRepository,
        LoggerInterface $logger
    ): Response {
        $activeSite = $request->attributes->get('active_site');
        $role = new Role();
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $role->setSiteId($activeSite->id);
                $roleRepository->save($role);
                $this->addFlash('success', DictionaryMessage::ROLE_CREATED_SUCCESSFUL);
                $logger->notice(self::LOG_PREFIX . ' ' . DictionaryMessage::ROLE_CREATED_SUCCESSFUL, [
                    'role' => $role,
                ]);
                return $this->redirectToRoute('app_role_admin_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Throwable $exception) {
                $this->addFlash('danger', DictionaryMessage::ERROR_CREATING_ROLE);
                StringHelper::exceptionLoggerHelper(
                    self::LOG_PREFIX . ' ' . DictionaryMessage::ERROR_CREATING_ROLE,
                    $exception,
                    $logger
                );
            }
        }

        return $this->render(sprintf('@User/%s/role_admin/new.html.twig', $activeSite->templatePath), [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{uuid}', name: 'app_role_admin_show', methods: ['GET'])]
    #[IsGranted('ROUTE_ACCESS', subject: 'role')]
    public function show(Request $request, #[MapEntity(mapping: ['uuid' => 'uuid'])] Role $role): Response
    {
        $activeSite = $request->attributes->get('active_site');
        return $this->render(sprintf('@User/%s/role_admin/show.html.twig', $activeSite->templatePath), [
            'role' => $role,
        ]);
    }

    #[Route('/edit/{uuid}/', name: 'app_role_admin_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROUTE_ACCESS', subject: 'role')]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Role $role,
        RoleRepository $roleRepository,
        LoggerInterface $logger
    ): Response {
        $activeSite = $request->attributes->get('active_site');
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $roleRepository->save($role);
                $this->addFlash('success', DictionaryMessage::ROLE_CHANGED_SUCCESSFUL);
                $logger->notice(self::LOG_PREFIX . ' ' . DictionaryMessage::ROLE_DELETED_SUCCESSFUL, [
                    'role' => $role,
                ]);
                return $this->redirectToRoute('app_role_admin_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Throwable $exception) {
                $this->addFlash('danger', DictionaryMessage::ERROR_CHANGING_ROLE);
                StringHelper::exceptionLoggerHelper(
                    self::LOG_PREFIX . ' ' . DictionaryMessage::ERROR_CHANGING_ROLE,
                    $exception,
                    $logger
                );
            }
        }

        return $this->render(sprintf('@User/%s/role_admin/edit.html.twig', $activeSite->templatePath), [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{uuid}', name: 'app_role_admin_delete', methods: ['GET'])]
    #[IsGranted('ROUTE_ACCESS', subject: 'role')]
    public function delete(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Role $role,
        RoleRepository $roleRepository,
        LoggerInterface $logger
    ): Response
    {
        try {
            $roleRepository->delete($role);
            $this->addFlash('success', DictionaryMessage::ROLE_DELETED_SUCCESSFUL );
        } catch (\Throwable $exception) {
            $this->addFlash('danger', DictionaryMessage::ERROR_DELETING_ROLE);
            StringHelper::exceptionLoggerHelper(
                self::LOG_PREFIX . ' ' . DictionaryMessage::ERROR_DELETING_ROLE,
                $exception,
                $logger
            );
        }

        return $this->redirectToRoute('app_role_admin_index');
    }
}
