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
            $entreprise->setSector((string) $request->request->get('sector', ''));
            $entreprise->setSize((string) $request->request->get('size', ''));
            $entreprise->setFoundedAt($request->request->get('foundedAt') ? (int) $request->request->get('foundedAt') : null);
            $entreprise->setPhone((string) $request->request->get('phone', ''));
            $entreprise->setContactEmail((string) $request->request->get('contactEmail', ''));
            $entreprise->setSocialLinkedin((string) $request->request->get('socialLinkedin', ''));
            $entreprise->setSlogan((string) $request->request->get('slogan', ''));
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
            $entreprise->setSector((string) $request->request->get('sector', ''));
            $entreprise->setSize((string) $request->request->get('size', ''));
            $entreprise->setFoundedAt($request->request->get('foundedAt') ? (int) $request->request->get('foundedAt') : null);
            $entreprise->setPhone((string) $request->request->get('phone', ''));
            $entreprise->setContactEmail((string) $request->request->get('contactEmail', ''));
            $entreprise->setSocialLinkedin((string) $request->request->get('socialLinkedin', ''));
            $entreprise->setSlogan((string) $request->request->get('slogan', ''));

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

    #[Route('/generate-description-ai', name: 'app_entreprise_generate_ai', methods: ['POST'])]
    public function generateDescriptionAi(Request $request, \App\Service\JobOffreAiService $aiService): Response
    {
        $data = [
            'name'      => $request->request->get('name'),
            'sector'    => $request->request->get('sector'),
            'slogan'    => $request->request->get('slogan'),
            'size'      => $request->request->get('size'),
            'address'   => $request->request->get('address'),
            'foundedAt' => $request->request->get('foundedAt'),
        ];

        try {
            $description = $aiService->generateEntrepriseDescription($data);
            return $this->json(['description' => $description]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
    #[Route('/delete', name: 'app_entreprise_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, CloudinaryUploader $uploader): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();

        if (!$entreprise) {
            $this->addFlash('error', 'Entreprise non trouvée.');
            return $this->redirectToRoute('app_home');
        }

        if ($this->isCsrfTokenValid('delete_entreprise', $request->request->get('_token'))) {
            // Supprimer le logo sur Cloudinary
            if ($entreprise->getLogoPublicId()) {
                try {
                    $uploader->deleteLogo($entreprise->getLogoPublicId());
                } catch (\Exception $e) {
                    // Log error if needed
                }
            }

            // Supprimer explicitement les offres pour éviter tout problème de contrainte
            foreach ($entreprise->getJobOffres() as $offre) {
                $entityManager->remove($offre);
            }

            $entityManager->remove($entreprise);
            $entityManager->flush();

            $this->addFlash('success', 'Votre entreprise et toutes ses offres ont été supprimées avec succès.');
            return $this->redirectToRoute('app_home');
        }

        $this->addFlash('error', 'Erreur de sécurité (CSRF). Veuillez réessayer.');
        return $this->redirectToRoute('app_entreprise_my');
    }
}
