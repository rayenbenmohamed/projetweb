<?php

namespace App\Service;

use App\Entity\FriendRequest;
use App\Entity\User;
use App\Repository\FriendRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

class FriendService
{
    public function __construct(
        private readonly FriendRequestRepository $friendRequestRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function areFriends(User $a, User $b): bool
    {
        if ($a->getId() === $b->getId()) {
            return false;
        }

        $row = $this->friendRequestRepository->findBetweenUsers($a, $b);

        return $row && FriendRequest::STATUS_ACCEPTED === $row->getStatus();
    }

    /**
     * @return array{ok: bool, code: string, request?: FriendRequest}
     */
    public function sendRequest(User $from, User $to): array
    {
        if ($from->getId() === $to->getId()) {
            return ['ok' => false, 'code' => 'self'];
        }

        $existing = $this->friendRequestRepository->findBetweenUsers($from, $to);

        if ($existing) {
            if (FriendRequest::STATUS_ACCEPTED === $existing->getStatus()) {
                return ['ok' => false, 'code' => 'already_friends'];
            }

            if (FriendRequest::STATUS_PENDING === $existing->getStatus()) {
                // They already invited me — accept automatically
                if ($existing->getReceiver()->getId() === $from->getId()) {
                    $existing->setStatus(FriendRequest::STATUS_ACCEPTED);
                    $this->entityManager->flush();

                    return ['ok' => true, 'code' => 'accepted_mutual', 'request' => $existing];
                }

                return ['ok' => false, 'code' => 'already_sent'];
            }
        }

        $fr = new FriendRequest();
        $fr->setSender($from);
        $fr->setReceiver($to);
        $fr->setStatus(FriendRequest::STATUS_PENDING);
        $this->entityManager->persist($fr);
        $this->entityManager->flush();

        return ['ok' => true, 'code' => 'sent', 'request' => $fr];
    }

    public function accept(User $actor, FriendRequest $request): bool
    {
        if ($request->getReceiver()->getId() !== $actor->getId()) {
            return false;
        }
        if (FriendRequest::STATUS_PENDING !== $request->getStatus()) {
            return false;
        }
        $request->setStatus(FriendRequest::STATUS_ACCEPTED);
        $this->entityManager->flush();

        return true;
    }

    public function reject(User $actor, FriendRequest $request): bool
    {
        if ($request->getReceiver()->getId() !== $actor->getId()) {
            return false;
        }
        if (FriendRequest::STATUS_PENDING !== $request->getStatus()) {
            return false;
        }
        $this->entityManager->remove($request);
        $this->entityManager->flush();

        return true;
    }

    public function cancelOutgoing(User $actor, FriendRequest $request): bool
    {
        if ($request->getSender()->getId() !== $actor->getId()) {
            return false;
        }
        if (FriendRequest::STATUS_PENDING !== $request->getStatus()) {
            return false;
        }
        $this->entityManager->remove($request);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @return User[]
     */
    public function getFriends(User $user): array
    {
        $friends = [];
        foreach ($this->friendRequestRepository->findAcceptedForUser($user) as $fr) {
            $other = $fr->getOtherUser($user);
            if ($other) {
                $friends[$other->getId()] = $other;
            }
        }

        return array_values($friends);
    }
}
