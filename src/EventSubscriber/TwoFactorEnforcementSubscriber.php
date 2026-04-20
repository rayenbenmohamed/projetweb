<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TwoFactorEnforcementSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 5],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = (string) $request->attributes->get('_route', '');
        if ($route === '' || str_starts_with($route, '_')) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User || !$user->isTwoFactorEnabled()) {
            return;
        }

        $allowedRoutes = [
            'app_two_factor_verify',
            'app_logout',
        ];
        if (\in_array($route, $allowedRoutes, true)) {
            return;
        }

        $session = $request->getSession();
        if ($session && !((bool) $session->get('app_2fa_passed', false))) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_two_factor_verify')));
        }
    }
}

