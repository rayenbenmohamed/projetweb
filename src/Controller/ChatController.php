<?php

namespace App\Controller;

use App\FlashMessages;
use App\Entity\FriendMessage;
use App\Entity\User;
use App\Repository\FriendMessageRepository;
use App\Service\FriendService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/app/chat')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ChatController extends AbstractController
{
    #[Route('/{id}', name: 'app_chat_with', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function thread(
        Request $request,
        User $peer,
        FriendService $friendService,
        FriendMessageRepository $friendMessageRepository,
        EntityManagerInterface $entityManager,
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

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('friend_chat' . $peer->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('error', FlashMessages::CSRF_INVALID);

                return $this->redirectToRoute('app_chat_with', ['id' => $peer->getId()]);
            }

            $text = trim((string) $request->request->get('content'));
            if ($text === '') {
                $this->addFlash('error', 'Saisissez un message avant d’envoyer.');

                return $this->redirectToRoute('app_chat_with', ['id' => $peer->getId()]);
            }
            if (strlen($text) > 5000) {
                $this->addFlash('error', 'Votre message dépasse 5 000 caractères. Raccourcissez-le puis réessayez.');

                return $this->redirectToRoute('app_chat_with', ['id' => $peer->getId()]);
            }

            $msg = new FriendMessage();
            $msg->setSender($me);
            $msg->setRecipient($peer);
            $msg->setContent($text);
            $entityManager->persist($msg);
            $entityManager->flush();

            return $this->redirectToRoute('app_chat_with', ['id' => $peer->getId()]);
        }

        $messages = $friendMessageRepository->findConversation($me, $peer, 300);

        return $this->render('friends/chat.html.twig', [
            'peer' => $peer,
            'messages' => $messages,
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
