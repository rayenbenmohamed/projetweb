<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private const REMEMBER_ME_COOKIE = 'SYFONURH_REMEMBER';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();
        $roles = $user instanceof UserInterface ? $user->getRoles() : $token->getRoleNames();

        if (\in_array('ROLE_ADMIN', $roles, true)) {
            $response = new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        } else {
            $response = new RedirectResponse($this->urlGenerator->generate('app_app_dashboard'));
        }

        // Sans « Se souvenir de moi » : supprimer tout ancien cookie remember-me pour ne pas rester connecté.
        if (!$request->request->getBoolean('_remember_me', false)) {
            $response->headers->clearCookie(
                self::REMEMBER_ME_COOKIE,
                '/',
                null,
                $request->isSecure(),
                true,
                Cookie::SAMESITE_LAX
            );
        }

        return $response;
    }
}
