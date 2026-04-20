<?php

namespace App\Controller;

use App\FlashMessages;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    private const RESET_CODE_TTL_MINUTES = 30;

    private const SESSION_EMAIL = 'app_pwd_reset_email';

    private const SESSION_AT = 'app_pwd_reset_at';

    /** Délai après validation du code pour saisir le nouveau mot de passe (secondes). */
    private const SESSION_TTL_SECONDS = 1200;

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        #[Autowire('%env(MAILER_DSN)%')]
        private readonly string $mailerDsn,
        #[Autowire('%env(RESET_PASSWORD_DELIVERY)%')]
        private readonly string $resetPasswordDelivery,
        #[Autowire('%env(MAILER_FROM)%')]
        private readonly string $mailerFrom,
        #[Autowire('%kernel.debug%')]
        private readonly bool $kernelDebug,
    ) {
    }

    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function request(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_app_dashboard');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('forgot_password', (string) $request->request->get('_token'))) {
                $this->addFlash('error', FlashMessages::CSRF_INVALID);

                return $this->redirectToRoute('app_forgot_password');
            }

            $email = trim((string) $request->request->get('email'));
            $codeShownOnScreen = false;
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($user instanceof User && !$user->isBlocked()) {
                    $plainCode = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
                    $user->setResetToken(password_hash($plainCode, PASSWORD_DEFAULT));
                    $user->setResetTokenExpiry(new \DateTimeImmutable('+' . self::RESET_CODE_TTL_MINUTES . ' minutes'));
                    $entityManager->flush();

                    $message = (new TemplatedEmail())
                        ->from(Address::create($this->mailerFrom))
                        ->to($user->getEmail())
                        ->subject('SyfonuRH — Code pour réinitialiser votre mot de passe')
                        ->htmlTemplate('emails/reset_password_code.html.twig')
                        ->context([
                            'code' => $plainCode,
                            'minutes' => self::RESET_CODE_TTL_MINUTES,
                        ]);

                    $deliveryMode = strtolower(trim($this->resetPasswordDelivery));
                    if ($deliveryMode === '' || $deliveryMode === 'auto') {
                        $deliveryMode = str_contains($this->mailerDsn, 'null://') ? 'screen' : 'email';
                    }

                    if ($deliveryMode === 'screen') {
                        $codeShownOnScreen = true;
                        $this->addFlash(
                            'info',
                            sprintf('Code de reinitialisation: %s (valable %d minutes).', $plainCode, self::RESET_CODE_TTL_MINUTES)
                        );
                    } elseif (str_contains($this->mailerDsn, 'null://')) {
                        $codeShownOnScreen = true;
                        if ($this->kernelDebug) {
                            $this->addFlash(
                                'info',
                                sprintf('[Développement — aucun e-mail envoyé] Votre code : %s (valable %d minutes).', $plainCode, self::RESET_CODE_TTL_MINUTES)
                            );
                        } else {
                            $this->addFlash(
                                'warning',
                                'Aucun e-mail ne peut partir : MAILER_DSN est sur « null ». Configurez Gmail dans le fichier .env.local (MAILER_DSN et MAILER_FROM).'
                            );
                        }
                    } else {
                        if (str_contains($this->mailerDsn, 'CHANGEMOI') || str_contains($this->mailerFrom, 'CHANGEMOI')) {
                            $this->addFlash(
                                'error',
                                'Configuration e-mail incomplète : remplacez CHANGEMOI et le mot de passe d\'application dans .env.local.'
                            );

                            return $this->redirectToRoute('app_forgot_password');
                        }

                        try {
                            $this->mailer->send($message);
                        } catch (\Throwable $e) {
                            $this->logger->error('Échec envoi e-mail réinitialisation mot de passe.', [
                                'exception' => $e,
                                'userId' => $user->getId(),
                            ]);
                            if ($this->kernelDebug) {
                                $codeShownOnScreen = true;
                                $this->addFlash('error', 'Échec d’envoi de l’e-mail : ' . $e->getMessage());
                                $this->addFlash(
                                    'info',
                                    sprintf('Code de reinitialisation (secours): %s (valable %d minutes).', $plainCode, self::RESET_CODE_TTL_MINUTES)
                                );
                            } else {
                                $this->addFlash(
                                    'error',
                                    'L’envoi du courriel a échoué. Vérifiez la configuration SMTP (identifiant Gmail, mot de passe d’application, DSN dans .env.local).'
                                );
                            }
                        }
                    }
                }
            }

            if ($codeShownOnScreen) {
                $this->addFlash(
                    'success',
                    'Si cette adresse correspond à un compte actif, le code s’affiche ci-dessus. Saisissez-le sur la page suivante pour choisir un nouveau mot de passe.'
                );
            } else {
                $this->addFlash(
                    'success',
                    'Si cette adresse correspond à un compte actif, vous recevrez un e-mail avec un code à 6 chiffres. Ensuite, sur la page suivante, saisissez ce code pour pouvoir choisir un nouveau mot de passe.'
                );
            }

            return $this->redirectToRoute('app_reset_password');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function reset(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_app_dashboard');
        }

        $session = $request->getSession();

        if ($request->query->get('cancel') === '1') {
            $session->remove(self::SESSION_EMAIL);
            $session->remove(self::SESSION_AT);
            $this->addFlash('info', 'Vous pouvez à nouveau saisir votre e-mail et le code reçu par e-mail.');

            return $this->redirectToRoute('app_reset_password');
        }

        $verifiedEmail = (string) $session->get(self::SESSION_EMAIL, '');
        $verifiedAt = (int) $session->get(self::SESSION_AT, 0);
        $passwordStepAllowed = $verifiedEmail !== '' && (time() - $verifiedAt) < self::SESSION_TTL_SECONDS;

        if ($passwordStepAllowed === false) {
            $session->remove(self::SESSION_EMAIL);
            $session->remove(self::SESSION_AT);
        }

        if ($request->isMethod('POST')) {
            $step = (string) $request->request->get('step');

            if ('password' === $step) {
                return $this->handlePasswordStep($request, $entityManager, $passwordHasher, $session);
            }

            return $this->handleVerifyStep($request, $entityManager, $session);
        }

        $verifiedEmail = (string) $session->get(self::SESSION_EMAIL, '');
        $verifiedAt = (int) $session->get(self::SESSION_AT, 0);
        $showPasswordStep = $verifiedEmail !== '' && (time() - $verifiedAt) < self::SESSION_TTL_SECONDS;

        return $this->render('security/reset_password.html.twig', [
            'show_password_step' => $showPasswordStep,
            'verified_email' => $showPasswordStep ? $verifiedEmail : null,
        ]);
    }

    private function handleVerifyStep(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        if (!$this->isCsrfTokenValid('reset_password_verify', (string) $request->request->get('_token'))) {
            $this->addFlash('error', FlashMessages::CSRF_INVALID);

            return $this->redirectToRoute('app_reset_password');
        }

        $email = trim((string) $request->request->get('email'));
        $code = trim((string) $request->request->get('code'));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Indiquez une adresse e-mail valide.');

            return $this->redirectToRoute('app_reset_password');
        }

        if (!preg_match('/^[0-9]{6}$/', $code)) {
            $this->addFlash('error', 'Le code doit comporter exactement 6 chiffres.');

            return $this->redirectToRoute('app_reset_password');
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user instanceof User || $user->isBlocked()) {
            $this->addFlash('error', 'Le code est incorrect ou a expiré. Vérifiez l’e-mail ou demandez un nouveau code.');

            return $this->redirectToRoute('app_reset_password');
        }

        $hash = $user->getResetToken();
        $expiry = $user->getResetTokenExpiry();
        if ($hash === null || $expiry === null || $expiry < new \DateTimeImmutable()) {
            $this->addFlash('error', 'Le code est incorrect ou a expiré. Demandez un nouveau code depuis « Mot de passe oublié ».');

            return $this->redirectToRoute('app_reset_password');
        }

        if (!password_verify($code, $hash)) {
            $this->addFlash('error', 'Le code est incorrect. Réessayez ou demandez un nouveau code.');

            return $this->redirectToRoute('app_reset_password');
        }

        $session->set(self::SESSION_EMAIL, $email);
        $session->set(self::SESSION_AT, time());
        $this->addFlash('success', 'Code accepté. Vous pouvez maintenant définir un nouveau mot de passe.');

        return $this->redirectToRoute('app_reset_password');
    }

    private function handlePasswordStep(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SessionInterface $session,
    ): Response {
        if (!$this->isCsrfTokenValid('reset_password_final', (string) $request->request->get('_token'))) {
            $this->addFlash('error', FlashMessages::CSRF_INVALID);

            return $this->redirectToRoute('app_reset_password');
        }

        $email = (string) $session->get(self::SESSION_EMAIL, '');
        $at = (int) $session->get(self::SESSION_AT, 0);
        if ($email === '' || (time() - $at) >= self::SESSION_TTL_SECONDS) {
            $session->remove(self::SESSION_EMAIL);
            $session->remove(self::SESSION_AT);
            $this->addFlash('error', 'La session a expiré. Vérifiez à nouveau votre code.');

            return $this->redirectToRoute('app_reset_password');
        }

        $plainPassword = (string) $request->request->get('password');
        $plainPassword2 = (string) $request->request->get('password_confirm');

        if ($plainPassword !== $plainPassword2) {
            $this->addFlash('error', 'Les deux mots de passe ne correspondent pas.');

            return $this->redirectToRoute('app_reset_password');
        }

        if (strlen($plainPassword) < 6) {
            $this->addFlash('error', 'Le mot de passe doit contenir au moins 6 caractères.');

            return $this->redirectToRoute('app_reset_password');
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user instanceof User || $user->isBlocked()) {
            $session->remove(self::SESSION_EMAIL);
            $session->remove(self::SESSION_AT);
            $this->addFlash('error', 'Session invalide. Recommencez depuis « Mot de passe oublié ».');

            return $this->redirectToRoute('app_reset_password');
        }

        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        $user->setResetToken(null);
        $user->setResetTokenExpiry(null);
        $entityManager->flush();

        $session->remove(self::SESSION_EMAIL);
        $session->remove(self::SESSION_AT);

        $this->addFlash('success', 'Votre mot de passe a été mis à jour. Vous pouvez vous connecter.');

        return $this->redirectToRoute('app_login');
    }
}
