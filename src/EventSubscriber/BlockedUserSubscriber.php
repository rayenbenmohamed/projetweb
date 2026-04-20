<?php

namespace App\EventSubscriber;

use App\Entity\Admin;
use App\Entity\Recruiter;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Déconnecte immédiatement un utilisateur non-admin dont le compte vient d’être bloqué.
 */
final class BlockedUserSubscriber implements EventSubscriberInterface
{
    private const SKIP_ROUTES = [
        'app_login',
        'app_register',
        'app_logout',
    ];

    public function __construct(
        private readonly Security $security,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 0]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = (string) $request->attributes->get('_route');
        if (\in_array($route, self::SKIP_ROUTES, true) || str_starts_with($route, '_')) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User || $user instanceof Admin || \in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return;
        }

        if ($user->isBlocked()) {
            $this->logoutAndRedirect($event, $request, 'Votre compte a été suspendu par un administrateur. Vous avez été déconnecté.');

            return;
        }

        if ($user instanceof Recruiter && !$user->isApproved()) {
            $this->logoutAndRedirect($event, $request, 'Votre compte recruteur n’est plus approuvé ou n’a pas encore été validé. Vous avez été déconnecté.');

            return;
        }
    }

    private function logoutAndRedirect(RequestEvent $event, \Symfony\Component\HttpFoundation\Request $request, string $flashMessage): void
    {
        $this->tokenStorage->setToken(null);
        $session = $request->getSession();
        $session->invalidate();
        $session->start();
        $session->getFlashBag()->add('error', $flashMessage);

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_login')));
    }
}

