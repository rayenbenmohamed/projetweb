<?php

namespace App\Controller;

use App\FlashMessages;
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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/app/profile')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        Connection $connection,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('profile_edit', (string) $request->request->get('_token'))) {
                $this->addFlash('error', FlashMessages::CSRF_INVALID);

                return $this->redirectToRoute('app_profile');
            }

            $email = trim((string) $request->request->get('email'));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Le format de l’e-mail n’est pas valide. Vérifiez qu’il ne manque rien (ex. @domaine.fr).');

                return $this->redirectToRoute('app_profile');
            }

            $duplicate = $userRepository->findOneBy(['email' => $email]);
            if ($duplicate && $duplicate->getId() !== $user->getId()) {
                $this->addFlash('error', 'Cette adresse e-mail est déjà utilisée par un autre compte.');

                return $this->redirectToRoute('app_profile');
            }

            $plainPassword = (string) $request->request->get('password');
            if ($plainPassword !== '' && strlen($plainPassword) < 6) {
                $this->addFlash('error', 'Si vous changez le mot de passe, il doit comporter au moins 4 caractères.');

                return $this->redirectToRoute('app_profile');
            }

            $hash = $plainPassword !== ''
                ? $passwordHasher->hashPassword($user, $plainPassword)
                : $user->getPassword();

            $connection->executeStatement(
                'UPDATE `user` SET email = ?, firstName = ?, lastName = ?, phone = ?, password = ? WHERE id = ?',
                [
                    $email,
                    trim((string) $request->request->get('firstName')) ?: null,
                    trim((string) $request->request->get('lastName')) ?: null,
                    trim((string) $request->request->get('phone')) ?: null,
                    $hash,
                    $user->getId(),
                ]
            );

            if ($user instanceof Recruiter) {
                $connection->executeStatement(
                    'UPDATE `user` SET companyname = ?, departement = ? WHERE id = ?',
                    [
                        trim((string) $request->request->get('companyname')) ?: null,
                        trim((string) $request->request->get('departement')) ?: null,
                        $user->getId(),
                    ]
                );
            }

            $this->addFlash('success', 'Vos informations ont été enregistrées.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'is_recruiter' => $user instanceof Recruiter,
        ]);
    }

    #[Route('/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('profile_delete', (string) $request->request->get('_token'))) {
            $this->addFlash('error', FlashMessages::CSRF_INVALID);

            return $this->redirectToRoute('app_profile');
        }

        try {
            $entityManager->remove($user);
            $entityManager->flush();
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Votre compte ne peut pas être supprimé tant qu’il reste des données liées (candidatures, offres d’emploi, messages, etc.). Supprimez ou modifiez ces éléments depuis l’application, puis réessayez.');

            return $this->redirectToRoute('app_profile');
        }

        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();
        $request->getSession()->start();
        $this->addFlash('success', 'Votre compte a été supprimé définitivement.');

        return $this->redirectToRoute('app_home');
    }
}
