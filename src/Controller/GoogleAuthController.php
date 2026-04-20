<?php

namespace App\Controller;

use App\Service\GoogleCalendarService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/google/auth')]
class GoogleAuthController extends AbstractController
{
    public function __construct(
        private readonly GoogleCalendarService $calendarService
    ) {}

    #[Route('/init', name: 'app_google_auth_init')]
    public function init(): Response
    {
        return $this->redirect($this->calendarService->generateAuthUrl());
    }

    #[Route('/callback', name: 'app_google_auth_callback')]
    public function callback(Request $request): Response
    {
        $code = $request->query->get('code');
        $user = $this->getUser();

        if (!$user instanceof User) {
            $this->addFlash('error', 'Vous devez être connecté pour lier votre compte Google.');
            return $this->redirectToRoute('app_login');
        }

        if ($code) {
            $success = $this->calendarService->authenticate($code, $user);
            if ($success) {
                $this->addFlash('success', 'Votre compte Google Calendar est maintenant lié !');
            } else {
                $this->addFlash('error', 'Échec de l\'authentification Google.');
            }
        } else {
            $this->addFlash('error', 'Aucun code d\'autorisation reçu.');
        }

        return $this->redirectToRoute('app_contract_index');
    }
}
