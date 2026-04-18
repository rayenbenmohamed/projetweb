<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Après CheckRememberMeConditionsListener : si la case n’est pas cochée, désactive le badge
 * pour que RememberMeListener ne pose jamais le cookie (évite tout faux positif sur _remember_me).
 */
final class RememberMeChoiceSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // -32 = CheckRememberMeConditionsListener, -64 = RememberMeListener → on s’exécute entre les deux.
        return [LoginSuccessEvent::class => ['onLoginSuccess', -48]];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->request->getBoolean('_remember_me', false)) {
            $passport = $event->getPassport();
            if ($passport->hasBadge(RememberMeBadge::class)) {
                $badge = $passport->getBadge(RememberMeBadge::class);
                if ($badge instanceof RememberMeBadge) {
                    $badge->disable();
                }
            }
        }
    }
}
