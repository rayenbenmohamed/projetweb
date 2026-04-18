<?php

namespace App\Controller;

use App\FlashMessages;
use App\Entity\FriendMessage;
use App\Entity\User;
use App\Repository\FriendMessageRepository;
use App\Service\ChatMercurePublisher;
use App\Service\FriendService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/app/chat')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class ChatController extends AbstractController
{
    /**
     * Nouveaux messages depuis le dernier id connu (temps réel sans Mercure, ou complément).
     */
    #[Route('/{id}/poll', name: 'app_chat_poll', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function poll(
        Request $request,
        User $peer,
        FriendService $friendService,
        FriendMessageRepository $friendMessageRepository,
    ): JsonResponse {
        $me = $this->requireUser();

        if ($peer->getId() === $me->getId()) {
            throw $this->createNotFoundException();
        }

        if (!$friendService->areFriends($me, $peer)) {
            return new JsonResponse(['ok' => false, 'error' => 'forbidden'], 403);
        }

        $after = max(0, (int) $request->query->get('after', 0));
        $rows = $friendMessageRepository->findConversationAfter($me, $peer, $after, 50);
        $messages = [];
        foreach ($rows as $m) {
            $messages[] = [
                'type' => 'message',
                'id' => $m->getId(),
                'senderId' => $m->getSender()?->getId(),
                'recipientId' => $m->getRecipient()?->getId(),
                'content' => $m->getContent(),
                'createdAt' => $m->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        return new JsonResponse(['ok' => true, 'messages' => $messages]);
    }

    #[Route('/{id}', name: 'app_chat_with', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function thread(
        Request $request,
        User $peer,
        FriendService $friendService,
        FriendMessageRepository $friendMessageRepository,
        EntityManagerInterface $entityManager,
        ChatMercurePublisher $chatMercurePublisher,
    ): Response {
        $me = $this->requireUser();

        if ($peer->getId() === $me->getId()) {
            throw $this->createNotFoundException();
        }

        if (!$friendService->areFriends($me, $peer)) {
            $this->addFlash('error', 'Pour discuter avec cette personne, vous devez d’abord être amis. Envoyez une demande depuis la page Amis ou acceptez la sienne.');

            return $this->redirectToRoute('app_friends_index');
        }

        $friendMessageRepository->markConversationReadForRecipient($me, $peer);

        $wantsJson = str_contains((string) $request->headers->get('Accept', ''), 'application/json');

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('friend_chat' . $peer->getId(), (string) $request->request->get('_token'))) {
                if ($wantsJson) {
                    return new JsonResponse(['ok' => false, 'error' => FlashMessages::CSRF_INVALID], 400);
                }
                $this->addFlash('error', FlashMessages::CSRF_INVALID);

                return $this->redirectToRoute('app_chat_with', ['id' => $peer->getId()]);
            }

            $text = trim((string) $request->request->get('content'));
            if ($text === '') {
                if ($wantsJson) {
                    return new JsonResponse(['ok' => false, 'error' => 'Saisissez un message avant d’envoyer.'], 400);
                }
                $this->addFlash('error', 'Saisissez un message avant d’envoyer.');

                return $this->redirectToRoute('app_chat_with', ['id' => $peer->getId()]);
            }
            if (strlen($text) > 5000) {
                if ($wantsJson) {
                    return new JsonResponse(['ok' => false, 'error' => 'Votre message dépasse 5 000 caractères. Raccourcissez-le puis réessayez.'], 400);
                }
                $this->addFlash('error', 'Votre message dépasse 5 000 caractères. Raccourcissez-le puis réessayez.');

                return $this->redirectToRoute('app_chat_with', ['id' => $peer->getId()]);
            }

            $msg = new FriendMessage();
            $msg->setSender($me);
            $msg->setRecipient($peer);
            $msg->setContent($text);
            $entityManager->persist($msg);
            $entityManager->flush();

            $chatMercurePublisher->publishNewMessage($msg);

            if ($wantsJson) {
                return new JsonResponse([
                    'ok' => true,
                    'message' => [
                        'id' => $msg->getId(),
                        'senderId' => $me->getId(),
                        'recipientId' => $peer->getId(),
                        'content' => $msg->getContent(),
                        'createdAt' => $msg->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                    ],
                ]);
            }

            return $this->redirectToRoute('app_chat_with', ['id' => $peer->getId()]);
        }

        $messages = $friendMessageRepository->findConversation($me, $peer, 300);

        return $this->render('friends/chat.html.twig', [
            'peer' => $peer,
            'messages' => $messages,
            'mercure_topic' => ChatMercurePublisher::topicForUserPair($me->getId(), $peer->getId()),
        ]);
    }

    private function requireUser(): User
    {
        $u = $this->getUser();
        if (!$u instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $u;
    }
}
