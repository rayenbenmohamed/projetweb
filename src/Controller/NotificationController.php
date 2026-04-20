<?php

namespace App\Controller;

use App\Service\NotificationService;
use App\Service\SocialNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/notifications', name: 'app_notifications_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(NotificationService $notificationService): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $notifications = $notificationService->getAllNotifications($user);

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/notifications/{id}/read', name: 'app_notification_read', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function markAsRead(int $id, NotificationService $notificationService): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $notificationService->markOneAsRead($id, $user);

        return $this->redirectToRoute('app_notifications_index');
    }

    #[Route('/notifications/read-all', name: 'app_notifications_read_all', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function markAllAsRead(NotificationService $notificationService): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $notificationService->markAllAsRead($user);

        return $this->redirectToRoute('app_notifications_index');
    }
}
