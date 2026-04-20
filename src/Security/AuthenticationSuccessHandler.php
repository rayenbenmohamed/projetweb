<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private const REMEMBER_ME_COOKIE = 'SYFONURH_REMEMBER';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        #[Autowire('%env(MAILER_FROM)%')]
        private readonly string $mailerFrom,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();
        $roles = $user instanceof UserInterface ? $user->getRoles() : $token->getRoleNames();

        if ($user instanceof User && $user->isTwoFactorEnabled()) {
            $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $user->setTwoFactorCode(password_hash($code, PASSWORD_DEFAULT));
            $user->setTwoFactorExpiry(new \DateTimeImmutable('+10 minutes'));
            $this->entityManager->flush();

            try {
                $this->mailer->send(
                    (new Email())
                        ->from(Address::create($this->mailerFrom))
                        ->to((string) $user->getEmail())
                        ->subject('SyfonuRH — Votre code de double authentification')
                        ->text(sprintf("Votre code de connexion est : %s\n\nCe code est valable 10 minutes.", $code))
                );
            } catch (\Throwable $e) {
                $this->logger->error('Echec envoi code 2FA.', ['exception' => $e, 'userId' => $user->getId()]);
            }

            $request->getSession()->set('app_2fa_passed', false);
            $this->tokenStorage->setToken($token);
            $response = new RedirectResponse($this->urlGenerator->generate('app_two_factor_verify'));

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

        if (\in_array('ROLE_ADMIN', $roles, true)) {
            $response = new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        } else {
            $response = new RedirectResponse($this->urlGenerator->generate('app_app_dashboard'));
        }

        $request->getSession()->set('app_2fa_passed', true);

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
