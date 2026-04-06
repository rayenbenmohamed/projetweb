<?php

namespace App\Controller\Admin;

use App\FlashMessages;
use App\Entity\Admin;
use App\Entity\Candidat;
use App\Entity\Recruiter;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
    public const ROLE_CHOICES = [
        'ROLE_CANDIDAT' => 'Candidat',
        'ROLE_RECRUTEUR' => 'Recruteur',
        'ROLE_ADMIN' => 'Administrateur',
    ];

    public static function roleToDiscr(string $role): string
    {
        return match ($role) {
            'ROLE_ADMIN' => 'admin',
            'ROLE_RECRUTEUR' => 'recruiter',
            'ROLE_CANDIDAT' => 'candidat',
            default => 'user',
        };
    }

    private static function createUserForRole(string $role): User
    {
        return match ($role) {
            'ROLE_ADMIN' => new Admin(),
            'ROLE_RECRUTEUR' => new Recruiter(),
            default => new Candidat(),
        };
    }

    #[Route('', name: 'admin_users_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findBy([], ['id' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'admin_users_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('admin_user_new', (string) $request->request->get('_token'))) {
                $this->addFlash('error', FlashMessages::CSRF_INVALID);

                return $this->redirectToRoute('admin_users_new');
            }

            $email = trim((string) $request->request->get('email'));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Indiquez une adresse e-mail valide.');

                return $this->redirectToRoute('admin_users_new');
            }

            if ($userRepository->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'Un utilisateur existe déjà avec cette adresse e-mail.');

                return $this->redirectToRoute('admin_users_new');
            }

            $role = (string) $request->request->get('role');
            if (!isset(self::ROLE_CHOICES[$role])) {
                $this->addFlash('error', 'Le rôle choisi n’est pas reconnu. Sélectionnez Candidat, Recruteur ou Administrateur.');

                return $this->redirectToRoute('admin_users_new');
            }

            $plainPassword = (string) $request->request->get('password');
            if (strlen($plainPassword) < 4) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 4 caractères.');

                return $this->redirectToRoute('admin_users_new');
            }

            $user = self::createUserForRole($role);
            $user->setEmail($email);
            $user->setFirstName(trim((string) $request->request->get('firstName')) ?: null);
            $user->setLastName(trim((string) $request->request->get('lastName')) ?: null);
            $user->setPhone(trim((string) $request->request->get('phone')) ?: null);
            $user->setRole($role);
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            if ($user instanceof Recruiter) {
                $user->setCompanyname(trim((string) $request->request->get('companyname')) ?: null);
                $user->setDepartement(trim((string) $request->request->get('departement')) ?: null);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'L’utilisateur a été créé et peut se connecter.');

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/user/new.html.twig', [
            'role_choices' => self::ROLE_CHOICES,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_users_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        Connection $connection,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('admin_user_edit' . $user->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('error', FlashMessages::CSRF_INVALID);

                return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
            }

            $email = trim((string) $request->request->get('email'));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Indiquez une adresse e-mail valide.');

                return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
            }

            $duplicate = $userRepository->findOneBy(['email' => $email]);
            if ($duplicate && $duplicate->getId() !== $user->getId()) {
                $this->addFlash('error', 'Cette adresse e-mail est déjà attribuée à un autre utilisateur.');

                return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
            }

            $role = (string) $request->request->get('role');
            if (!isset(self::ROLE_CHOICES[$role])) {
                $this->addFlash('error', 'Le rôle choisi n’est pas reconnu. Sélectionnez Candidat, Recruteur ou Administrateur.');

                return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
            }

            $plainPassword = (string) $request->request->get('password');
            if ($plainPassword !== '' && strlen($plainPassword) < 4) {
                $this->addFlash('error', 'Si vous définissez un nouveau mot de passe, il doit contenir au moins 4 caractères.');

                return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
            }

            $discr = self::roleToDiscr($role);
            $hash = $plainPassword !== ''
                ? $passwordHasher->hashPassword($user, $plainPassword)
                : $user->getPassword();

            $connection->executeStatement(
                'UPDATE `user` SET email = ?, firstName = ?, lastName = ?, phone = ?, `role` = ?, discr = ?, password = ? WHERE id = ?',
                [
                    $email,
                    trim((string) $request->request->get('firstName')) ?: null,
                    trim((string) $request->request->get('lastName')) ?: null,
                    trim((string) $request->request->get('phone')) ?: null,
                    $role,
                    $discr,
                    $hash,
                    $user->getId(),
                ]
            );

            if ('ROLE_RECRUTEUR' === $role) {
                $connection->executeStatement(
                    'UPDATE `user` SET companyname = ?, departement = ? WHERE id = ?',
                    [
                        trim((string) $request->request->get('companyname')) ?: null,
                        trim((string) $request->request->get('departement')) ?: null,
                        $user->getId(),
                    ]
                );
            }

            $this->addFlash('success', 'Les modifications ont été enregistrées pour cet utilisateur.');

            return $this->redirectToRoute('admin_users_index');
        }

        $currentRole = $user->getRole() ?? '';
        if (!isset(self::ROLE_CHOICES[$currentRole])) {
            $currentRole = match (true) {
                str_starts_with($currentRole, 'ROLE_') => $currentRole,
                $currentRole === 'Candidat' => 'ROLE_CANDIDAT',
                $currentRole === 'Recruteur' => 'ROLE_RECRUTEUR',
                $currentRole === 'Admin' => 'ROLE_ADMIN',
                default => 'ROLE_CANDIDAT',
            };
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'role_choices' => self::ROLE_CHOICES,
            'current_role' => $currentRole,
            'companyname' => $user instanceof Recruiter ? ($user->getCompanyname() ?? '') : '',
            'departement' => $user instanceof Recruiter ? ($user->getDepartement() ?? '') : '',
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_users_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isCsrfTokenValid('admin_user_delete' . $user->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', FlashMessages::CSRF_INVALID);

            return $this->redirectToRoute('admin_users_index');
        }

        $me = $this->getUser();
        if ($me instanceof User && $user->getId() === $me->getId()) {
            $this->addFlash('error', 'Pour des raisons de sécurité, vous ne pouvez pas supprimer votre propre compte depuis cette liste. Demandez à un autre administrateur si nécessaire.');

            return $this->redirectToRoute('admin_users_index');
        }

        try {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'L’utilisateur a été supprimé.');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'La suppression a échoué : cet utilisateur est encore lié à des candidatures, offres, messages ou autres données. Supprimez ou réassignez ces éléments, puis réessayez.');
        }

        return $this->redirectToRoute('admin_users_index');
    }
}
