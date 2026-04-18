<?php

namespace App\Controller;

use App\FlashMessages;
use App\Entity\Candidat;
use App\Entity\Recruiter;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() instanceof UserInterface) {
            $roles = $this->getUser()->getRoles();
            if (\in_array('ROLE_ADMIN', $roles, true)) {
                return $this->redirectToRoute('admin_dashboard');
            }

            return $this->redirectToRoute('app_app_dashboard');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        if ($this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('app_app_dashboard');
        }

        if ($request->isMethod('POST')) {
            $token = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('register', (string) $token)) {
                $this->addFlash('error', FlashMessages::CSRF_INVALID);

                return $this->redirectToRoute('app_register');
            }

            $email = trim((string) $request->request->get('email'));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Indiquez une adresse e-mail valide (ex. prenom@exemple.fr).');

                return $this->redirectToRoute('app_register');
            }

            $existing = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existing) {
                $this->addFlash('error', 'Un compte existe déjà avec cette adresse e-mail. Connectez-vous ou utilisez une autre adresse.');

                return $this->redirectToRoute('app_register');
            }

            $plainPassword = (string) $request->request->get('password');
            if (strlen($plainPassword) < 6) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 6 caractères pour des raisons de sécurité.');

                return $this->redirectToRoute('app_register');
            }

            $roleChoice = (string) $request->request->get('role');
            if (!\in_array($roleChoice, ['Candidat', 'Recruteur'], true)) {
                $this->addFlash('error', 'Le rôle choisi n’est pas valide.');

                return $this->redirectToRoute('app_register');
            }

            $user = 'Recruteur' === $roleChoice ? new Recruiter() : new Candidat();
            $user->setEmail($email);
            $user->setFirstName(trim((string) $request->request->get('firstName')) ?: null);
            $user->setLastName(trim((string) $request->request->get('lastName')) ?: null);
            $phone = trim((string) $request->request->get('phone'));
            $user->setPhone('' !== $phone ? $phone : null);
            $user->setRole('Recruteur' === $roleChoice ? 'ROLE_RECRUTEUR' : 'ROLE_CANDIDAT');
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            if ($user instanceof Recruiter) {
                $companyName = trim((string) $request->request->get('companyname'));
                $companyRne = self::normalizeCompanyRne((string) $request->request->get('companyRne'));

                if (mb_strlen($companyName) < 2 || mb_strlen($companyName) > 255) {
                    $this->addFlash('error', 'Indiquez le nom de l’entreprise (2 à 255 caractères).');

                    return $this->redirectToRoute('app_register');
                }

                if (!self::isValidCompanyRne($companyRne)) {
                    $this->addFlash('error', 'Le RNE / SIREN / SIRET doit comporter 9 chiffres (SIREN), 14 chiffres (SIRET) ou un identifiant alphanumérique de 5 à 20 caractères (sans espaces).');

                    return $this->redirectToRoute('app_register');
                }

                $user->setCompanyname($companyName);
                $user->setCompanyRne($companyRne);
                $user->setApproved(false);
            } else {
                $user->setApproved(true);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            if ($user instanceof Recruiter) {
                $this->addFlash(
                    'success',
                    'Votre demande de compte recruteur a bien été enregistrée. Un administrateur doit valider votre entreprise avant que vous puissiez vous connecter.'
                );
            } else {
                $this->addFlash('success', 'Votre compte a été créé. Vous pouvez maintenant vous connecter.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig');
    }

    private static function normalizeCompanyRne(string $raw): string
    {
        $s = preg_replace('/\s+/', '', $raw);

        return strtoupper((string) $s);
    }

    private static function isValidCompanyRne(string $rne): bool
    {
        if ($rne === '') {
            return false;
        }

        if (preg_match('/^[0-9]{9}$/', $rne) || preg_match('/^[0-9]{14}$/', $rne)) {
            return true;
        }

        return (bool) preg_match('/^[A-Z0-9]{5,20}$/', $rne);
    }
}
