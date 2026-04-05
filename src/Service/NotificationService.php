<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private EntityManagerInterface $entityManager;
    private NotificationRepository $notificationRepository;

    public function __construct(EntityManagerInterface $entityManager, NotificationRepository $notificationRepository)
    {
        $this->entityManager = $entityManager;
        $this->notificationRepository = $notificationRepository;
    }

    public function addNotification(User $recipient, string $message): void
    {
        $notification = new Notification();
        $notification->setUser($recipient);
        $notification->setMessage($message);
        $notification->setIsRead(false);
        $notification->setCreatedAt(new \DateTime());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    /**
     * @return Notification[]
     */
    public function getUnreadNotifications(User $user): array
    {
        return $this->notificationRepository->findBy(['user' => $user, 'isRead' => false]);
    }
}
