<?php

namespace App\Controller;

use App\Service\SocialNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class NotificationController extends AbstractController
{
    #[Route('/app/notifications/summary', name: 'app_notifications_summary', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function summary(SocialNotificationService $socialNotificationService): JsonResponse
    {
        return $this->json($socialNotificationService->getCounts($this->getUser()));
    }
}
