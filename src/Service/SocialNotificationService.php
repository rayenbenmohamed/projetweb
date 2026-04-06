<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\FriendMessageRepository;
use App\Repository\FriendRequestRepository;
use Symfony\Component\Security\Core\User\UserInterface;

final class SocialNotificationService
{
    public function __construct(
        private readonly FriendRequestRepository $friendRequestRepository,
        private readonly FriendMessageRepository $friendMessageRepository,
    ) {
    }

    /**
     * @return array{friend_requests: int, unread_messages: int, total: int}
     */
    public function getCounts(?UserInterface $user): array
    {
        if (!$user instanceof User) {
            return ['friend_requests' => 0, 'unread_messages' => 0, 'total' => 0];
        }

        $friendRequests = $this->friendRequestRepository->countIncomingPending($user);
        $unreadMessages = $this->friendMessageRepository->countUnreadForRecipient($user);

        return [
            'friend_requests' => $friendRequests,
            'unread_messages' => $unreadMessages,
            'total' => $friendRequests + $unreadMessages,
        ];
    }
}
