<?php

namespace App\Service;

use App\Entity\FriendMessage;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/**
 * Publie les nouveaux messages sur un topic Mercure partagé par les deux interlocuteurs.
 */
final class ChatMercurePublisher
{
    public function __construct(private readonly HubInterface $hub)
    {
    }

    public static function topicForUserPair(int $userIdA, int $userIdB): string
    {
        return sprintf('urn:friendchat:%d-%d', min($userIdA, $userIdB), max($userIdA, $userIdB));
    }

    public function publishNewMessage(FriendMessage $message): void
    {
        $sender = $message->getSender();
        $recipient = $message->getRecipient();
        if (null === $sender?->getId() || null === $recipient?->getId()) {
            return;
        }

        $topic = self::topicForUserPair($sender->getId(), $recipient->getId());
        $createdAt = $message->getCreatedAt();

        $payload = [
            'type' => 'message',
            'id' => $message->getId(),
            'senderId' => $sender->getId(),
            'recipientId' => $recipient->getId(),
            'content' => $message->getContent(),
            'createdAt' => $createdAt?->format(\DateTimeInterface::ATOM),
        ];

        try {
            // Public : compatible avec EventSource ?topic=… sans cookie JWT (voir doc Mercure / twig `mercure()`).
            $this->hub->publish(new Update(
                $topic,
                json_encode($payload, JSON_THROW_ON_ERROR),
                false
            ));
        } catch (\Throwable) {
            // Hub Mercure indisponible : le message reste enregistré, pas de diffusion temps réel.
        }
    }
}
