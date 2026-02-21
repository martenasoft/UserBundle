<?php

namespace MartenaSoft\UserBundle\Controller;

use MartenaSoft\CommonLibrary\Dictionary\DictionaryMessage;
use MartenaSoft\CommonLibrary\Helper\StringHelper;
use MartenaSoft\CommonLibrary\Dto\ActiveSiteDto;
use MartenaSoft\UserBundle\Entity\Permission;
use MartenaSoft\UserBundle\Form\PermissionType;
use MartenaSoft\UserBundle\Repository\PermissionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}/admin/permission', priority: 103)]
class PermissionAdminController extends AbstractController
{
    private const string LOG_PREFIX = 'permission_admin';

    #[IsGranted('ROUTE_ACCESS')]
    #[Route('/', name: 'app_permission_admin_index', methods: ['GET'])]
    public function index(
        PermissionRepository $permissionRepository,
        PaginatorInterface   $paginator,
        Request              $request
    ): Response {

        /** @var ActiveSiteDto $activeSite */
        $activeSite = $request->attributes->get('active_site');
        $queryBuilder = $permissionRepository->getQueryBuilder($activeSite->id);
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1)
        );

        return $this->render(sprintf('@User/%s/permission_admin/index.html.twig', $activeSite->templatePath), [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_permission_admin_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROUTE_ACCESS')]
    public function new(
        Request $request,
        PermissionRepository $permissionRepository,
        LoggerInterface $logger
    ): Response {

        /** @var ActiveSiteDto $activeSite */
        $activeSite = $request->attributes->get('active_site');
        $permission = new Permission();
        $form = $this->createForm(PermissionType::class, $permission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $permissionRepository->saveCount($permission, $activeSite->id);
                $permission->setSiteId($activeSite->id);
                $permissionRepository->save($permission);
                $this->addFlash('success', DictionaryMessage::PERMISSION_CREATED_SUCCESSFUL);

                return $this->redirectToRoute('app_permission_admin_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Throwable $exception) {
                $this->addFlash('danger', DictionaryMessage::ERROR_CREATING_PERMISSION);
                StringHelper::exceptionLoggerHelper(
                    self::LOG_PREFIX . ' ' . DictionaryMessage::ERROR_CREATING_PERMISSION,
                    $exception,
                    $logger
                );
            }
        }

        return $this->render(sprintf('@User/%s/permission_admin/new.html.twig', $activeSite->templatePath), [
            'permission' => $permission,
            'form' => $form,
        ]);
    }

    #[Route('/{uuid}', name: 'app_permission_admin_show', methods: ['GET'])]
    #[IsGranted('ROUTE_ACCESS', subject: 'permission')]
    public function show(Request $request, #[MapEntity(mapping: ['uuid' => 'uuid'])] Permission $permission): Response
    {
        $activeSite = $request->attributes->get('active_site');
        return $this->render(sprintf('@User/%s/permission_admin/show.html.twig', $activeSite->templatePath), [
            'permission' => $permission,
        ]);
    }

    #[Route('/edit/{uuid}', name: 'app_permission_admin_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROUTE_ACCESS', subject: 'permission')]
    public function edit(
        Request              $request,
        #[MapEntity(mapping: ['uuid' => 'uuid'])]  Permission           $permission,
        PermissionRepository $permissionRepository,
        LoggerInterface      $logger
    ): Response {

        /** @var ActiveSiteDto $activeSite */
        $activeSite = $request->attributes->get('active_site');
        $form = $this->createForm(PermissionType::class, $permission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $permissionRepository->saveCount($permission, $activeSite->id);
                $permissionRepository->save($permission);
                $this->addFlash('success', DictionaryMessage::PERMISSION_CHANGED_SUCCESSFUL);

                return $this->redirectToRoute('app_permission_admin_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Throwable $exception) {
                $this->addFlash('danger', DictionaryMessage::ERROR_CHANGING_PERMISSION);
                StringHelper::exceptionLoggerHelper(
                    self::LOG_PREFIX . ' ' . DictionaryMessage::ERROR_CHANGING_PERMISSION,
                    $exception,
                    $logger
                );
            }
        }

        return $this->render(sprintf('@User/%s/permission_admin/edit.html.twig', $activeSite->templatePath), [
            'permission' => $permission,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{uuid}', name: 'app_permission_admin_delete', methods: ['GET'])]
    #[IsGranted('ROUTE_ACCESS', subject: 'permission')]
    public function permissionDelete(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Permission $permission,
        PermissionRepository $permissionRepository,
        LoggerInterface $logger
    ): Response {
        try {
            $permissionRepository->delete($permission);
            $this->addFlash('success', DictionaryMessage::PERMISSION_DELETED_SUCCESSFUL);
        } catch (\Throwable $exception) {
            $this->addFlash('danger', DictionaryMessage::ERROR_DELETING_PERMISSION);
            StringHelper::exceptionLoggerHelper(
                self::LOG_PREFIX . ' ' . DictionaryMessage::ERROR_DELETING_PERMISSION,
                $exception,
                $logger
            );
        }
        return $this->redirectToRoute('app_permission_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
