<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Repository\EntrepriseRepository;
use App\Service\CloudinaryUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/entreprise')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class EntrepriseController extends AbstractController
{
    #[Route('/mon-entreprise', name: 'app_entreprise_my', methods: ['GET'])]
    public function myEntreprise(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();

        if (!$entreprise) {
            return $this->redirectToRoute('app_entreprise_new');
        }

        return $this->render('entreprise/show.html.twig', [
            'entreprise' => $entreprise,
        ]);
    }

    #[Route('/new', name: 'app_entreprise_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, CloudinaryUploader $uploader): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Sécurité : Un recruteur ne peut avoir qu'une seule entreprise
        if ($user->getEntreprise()) {
            $this->addFlash('warning', 'Vous avez déjà enregistré une entreprise.');
            return $this->redirectToRoute('app_entreprise_my');
        }

        $entreprise = new Entreprise();
        $errors = [];

        if ($request->isMethod('POST')) {
            $entreprise->setName((string) $request->request->get('name', ''));
            $entreprise->setDescription((string) $request->request->get('description', ''));
            $entreprise->setWebsite((string) $request->request->get('website', ''));
            $entreprise->setAddress((string) $request->request->get('address', ''));
            $entreprise->setUser($user);

            // Upload logo
            $logoFile = $request->files->get('logo');
            if ($logoFile) {
                try {
                    $uploaded = $uploader->uploadLogo($logoFile);
                    $entreprise->setLogo($uploaded['url']);
                    $entreprise->setLogoPublicId($uploaded['publicId']);
                } catch (\RuntimeException $e) {
                    $errors['logo'] = $e->getMessage();
                }
            }

            $violations = $validator->validate($entreprise);
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            if (empty($errors)) {
                $entityManager->persist($entreprise);
                $entityManager->flush();

                $this->addFlash('success', 'Votre entreprise a été créée avec succès.');
                return $this->redirectToRoute('app_job_offre_new');
            }
        }

        return $this->render('entreprise/new.html.twig', [
            'entreprise' => $entreprise,
            'errors' => $errors,
        ]);
    }

    #[Route('/edit', name: 'app_entreprise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, CloudinaryUploader $uploader): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();

        if (!$entreprise) {
            return $this->redirectToRoute('app_entreprise_new');
        }

        $errors = [];

        if ($request->isMethod('POST')) {
            $entreprise->setName((string) $request->request->get('name', ''));
            $entreprise->setDescription((string) $request->request->get('description', ''));
            $entreprise->setWebsite((string) $request->request->get('website', ''));
            $entreprise->setAddress((string) $request->request->get('address', ''));

            // Upload logo
            $logoFile = $request->files->get('logo');
            if ($logoFile) {
                try {
                    if ($entreprise->getLogoPublicId()) {
                        $uploader->deleteLogo($entreprise->getLogoPublicId());
                    }
                    $uploaded = $uploader->uploadLogo($logoFile);
                    $entreprise->setLogo($uploaded['url']);
                    $entreprise->setLogoPublicId($uploaded['publicId']);
                } catch (\RuntimeException $e) {
                    $errors['logo'] = $e->getMessage();
                }
            }

            $violations = $validator->validate($entreprise);
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            if (empty($errors)) {
                $entityManager->flush();
                $this->addFlash('success', 'Les informations de votre entreprise ont été mises à jour.');
                return $this->redirectToRoute('app_entreprise_my');
            }
        }

        return $this->render('entreprise/edit.html.twig', [
            'entreprise' => $entreprise,
            'errors' => $errors,
        ]);
    }
}
