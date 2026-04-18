<?php

namespace App\Controller\Admin;

use App\Entity\Recruiter;
use App\Entity\User;
use App\FlashMessages;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/recruiters')]
#[IsGranted('ROLE_ADMIN')]
class RecruiterApprovalController extends AbstractController
{
    #[Route('/pending', name: 'admin_recruiters_pending', methods: ['GET'])]
    public function pending(UserRepository $userRepository): Response
    {
        return $this->render('admin/recruiter/pending.html.twig', [
            'recruiters' => $userRepository->findPendingRecruiters(),
        ]);
    }

    #[Route('/{id}/approve', name: 'admin_recruiters_approve', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function approve(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$user instanceof Recruiter) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('admin_recruiter_approve' . $user->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', FlashMessages::CSRF_INVALID);

            return $this->redirectToRoute('admin_recruiters_pending');
        }

        if ($user->isApproved()) {
            $this->addFlash('info', 'Ce compte recruteur est déjà approuvé.');

            return $this->redirectToRoute('admin_recruiters_pending');
        }

        $user->setApproved(true);
        $entityManager->flush();

        $this->addFlash('success', 'Le compte recruteur « ' . $user->getEmail() . ' » a été approuvé. L’utilisateur peut se connecter.');

        return $this->redirectToRoute('admin_recruiters_pending');
    }
}
