<?php

namespace MartenaSoft\UserBundle\Security;


use MartenaSoft\CommonLibrary\Dto\ActiveSiteDto;
use MartenaSoft\UserBundle\Entity\RedirectToInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ParameterBagInterface $parameterBag
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $route = $this->getRedirectRoute(
            $request->attributes->get('active_site'),
            $token->getRoleNames()
        );

        $user = $token->getUser();

        if ($user instanceof RedirectToInterface && !empty($user->getRedirectToRoute())) {
            $route = $user->getRedirectToRoute();
        }

        $locale = ['_locale' => $request->getLocale()];
        return new RedirectResponse($this->urlGenerator->generate($route, $locale));
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): Response {
        return parent::onAuthenticationFailure($request, $exception);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    private function getRedirectRoute(ActiveSiteDto $activeSiteDto, array $roles): string
    {
        $result = 'app_page_main';
        $config = $this->parameterBag->get('user');

        foreach ($config as $item) {
            if (empty($item['site']['id'])
                || $item['site']['id'] !==  $activeSiteDto->id
                || empty($item['site']['redirect_to_after_login']['default_route'])
            ) {
                continue;
            }
            return $item['site']['redirect_to_after_login']['default_route'];
        }

        return $result;
    }
}
