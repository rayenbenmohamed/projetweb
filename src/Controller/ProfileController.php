<?php

namespace App\Controller;

use App\FlashMessages;
use App\Entity\Recruiter;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\CloudinaryService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        CloudinaryService $cloudinaryService,
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

            $photoUrl = $user->getProfilePhotoUrl();
            $twoFactorEnabled = $request->request->getBoolean('twoFactorEnabled');
            $uploadedPhoto = $request->files->get('profilePhoto');
            if ($uploadedPhoto instanceof UploadedFile) {
                if (!$uploadedPhoto->isValid()) {
                    $this->addFlash('error', 'Le fichier photo est invalide. Réessayez avec une image JPG, PNG, GIF ou WebP.');

                    return $this->redirectToRoute('app_profile');
                }

                $mimeType = (string) $uploadedPhoto->getMimeType();
                if (!str_starts_with($mimeType, 'image/')) {
                    $this->addFlash('error', 'Seules les images sont autorisées pour la photo de profil.');

                    return $this->redirectToRoute('app_profile');
                }

                if ((int) $uploadedPhoto->getSize() > 5 * 1024 * 1024) {
                    $this->addFlash('error', 'La photo de profil ne doit pas dépasser 5 Mo.');

                    return $this->redirectToRoute('app_profile');
                }

                try {
                    $photoUrl = $cloudinaryService->uploadFile($uploadedPhoto, 'avatars');
                } catch (\Throwable) {
                    $this->addFlash('error', 'L’envoi de la photo a échoué. Vérifiez la configuration Cloudinary puis réessayez.');

                    return $this->redirectToRoute('app_profile');
                }
            }

            $connection->executeStatement(
                'UPDATE `user` SET email = ?, firstName = ?, lastName = ?, phone = ?, password = ?, profile_photo_url = ?, two_factor_enabled = ?, two_factor_code = ?, two_factor_expiry = ? WHERE id = ?',
                [
                    $email,
                    trim((string) $request->request->get('firstName')) ?: null,
                    trim((string) $request->request->get('lastName')) ?: null,
                    trim((string) $request->request->get('phone')) ?: null,
                    $hash,
                    $photoUrl,
                    $twoFactorEnabled ? 1 : 0,
                    $twoFactorEnabled ? $user->getTwoFactorCode() : null,
                    $twoFactorEnabled ? $user->getTwoFactorExpiry()?->format('Y-m-d H:i:s') : null,
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
