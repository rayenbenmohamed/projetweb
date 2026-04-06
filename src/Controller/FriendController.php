<?php

namespace App\Controller;

use App\FlashMessages;
use App\Entity\FriendRequest;
use App\Entity\User;
use App\Repository\FriendMessageRepository;
use App\Repository\FriendRequestRepository;
use App\Repository\UserRepository;
use App\Service\FriendService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/app/friends')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class FriendController extends AbstractController
{
    #[Route('', name: 'app_friends_index', methods: ['GET'])]
    public function index(
        Request $request,
        UserRepository $userRepository,
        FriendService $friendService,
        FriendRequestRepository $friendRequestRepository,
        FriendMessageRepository $friendMessageRepository,
    ): Response {
        $me = $this->requireUser();

        $friends = $friendService->getFriends($me);
        $friendIdSet = [];
        foreach ($friends as $f) {
            $friendIdSet[$f->getId()] = true;
        }

        $incoming = $friendRequestRepository->findIncomingPending($me);
        $outgoing = $friendRequestRepository->findOutgoingPending($me);

        $pendingFromMe = [];
        foreach ($outgoing as $r) {
            $rid = $r->getReceiver()?->getId();
            if ($rid) {
                $pendingFromMe[$rid] = $r;
            }
        }

        $pendingToMe = [];
        foreach ($incoming as $r) {
            $sid = $r->getSender()?->getId();
            if ($sid) {
                $pendingToMe[$sid] = $r;
            }
        }

        $searchQuery = trim((string) $request->query->get('q', ''));

        $qb = $userRepository->createQueryBuilder('u')
            ->where('u.id != :id')
            ->setParameter('id', $me->getId())
            ->orderBy('u.email', 'ASC')
            ->setMaxResults(300);

        if ($searchQuery !== '') {
            $pattern = '%'.addcslashes(mb_strtolower($searchQuery), '%_\\').'%';
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(u.email)', ':like'),
                    $qb->expr()->like('LOWER(COALESCE(u.firstName, :empty))', ':like'),
                    $qb->expr()->like('LOWER(COALESCE(u.lastName, :empty))', ':like'),
                    $qb->expr()->like(
                        "LOWER(CONCAT(COALESCE(u.firstName, ''), ' ', COALESCE(u.lastName, '')))",
                        ':like'
                    ),
                )
            )->setParameter('like', $pattern)->setParameter('empty', '');
        }

        $directory = $qb->getQuery()->getResult();

        $friendsDisplay = $friends;
        if ($searchQuery !== '') {
            $needle = mb_strtolower($searchQuery);
            $friendsDisplay = array_values(array_filter(
                $friends,
                static function (User $f) use ($needle): bool {
                    return str_contains(mb_strtolower((string) $f->getEmail()), $needle)
                        || str_contains(mb_strtolower((string) $f->getFirstName()), $needle)
                        || str_contains(mb_strtolower((string) $f->getLastName()), $needle);
                }
            ));
        }

        $unreadByFriend = $friendMessageRepository->unreadCountsBySender($me);

        return $this->render('friends/index.html.twig', [
            'friends' => $friendsDisplay,
            'incoming_requests' => $incoming,
            'outgoing_requests' => $outgoing,
            'directory' => $directory,
            'friend_id_set' => $friendIdSet,
            'pending_from_me' => $pendingFromMe,
            'pending_to_me' => $pendingToMe,
            'search_query' => $searchQuery,
            'unread_by_friend' => $unreadByFriend,
        ]);
    }

    #[Route('/request/{id}', name: 'app_friends_request', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function sendRequest(
        Request $request,
        User $target,
        FriendService $friendService,
    ): Response {
        $me = $this->requireUser();

        if (!$this->isCsrfTokenValid('friend_request' . $target->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', FlashMessages::CSRF_INVALID);

            return $this->redirectToRoute('app_friends_index');
        }

        $result = $friendService->sendRequest($me, $target);
        $messages = [
            'self' => 'Vous ne pouvez pas vous envoyer une demande d’ami à vous-même.',
            'already_friends' => 'Vous êtes déjà ami avec cette personne.',
            'already_sent' => 'Une demande est déjà en cours vers cette personne.',
            'sent' => 'Demande d’ami envoyée. Son statut s’affichera sur cette page.',
            'accepted_mutual' => 'Vous aviez chacun une demande en attente : vous êtes maintenant amis.',
        ];
        $fallback = 'L’action n’a pas pu être effectuée. Actualisez la page et réessayez.';
        $this->addFlash($result['ok'] ? 'success' : 'error', $messages[$result['code']] ?? $fallback);

        return $this->redirectToRoute('app_friends_index');
    }

    #[Route('/accept/{id}', name: 'app_friends_accept', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function accept(
        Request $request,
        FriendRequest $friendRequest,
        FriendService $friendService,
    ): Response {
        $me = $this->requireUser();

        if (!$this->isCsrfTokenValid('friend_accept' . $friendRequest->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', FlashMessages::CSRF_INVALID);

            return $this->redirectToRoute('app_friends_index');
        }

        if ($friendService->accept($me, $friendRequest)) {
            $this->addFlash('success', 'Demande acceptée. Vous pouvez maintenant échanger des messages.');
        } else {
            $this->addFlash('error', 'Cette demande n’existe plus, n’est plus en attente ou ne vous est pas destinée. Actualisez la page.');
        }

        return $this->redirectToRoute('app_friends_index');
    }

    #[Route('/reject/{id}', name: 'app_friends_reject', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function reject(
        Request $request,
        FriendRequest $friendRequest,
        FriendService $friendService,
    ): Response {
        $me = $this->requireUser();

        if (!$this->isCsrfTokenValid('friend_reject' . $friendRequest->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', FlashMessages::CSRF_INVALID);

            return $this->redirectToRoute('app_friends_index');
        }

        if ($friendService->reject($me, $friendRequest)) {
            $this->addFlash('success', 'La demande a été refusée.');
        } else {
            $this->addFlash('error', 'Cette demande n’existe plus ou a déjà été traitée. Actualisez la page.');
        }

        return $this->redirectToRoute('app_friends_index');
    }

    #[Route('/cancel/{id}', name: 'app_friends_cancel', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function cancel(
        Request $request,
        FriendRequest $friendRequest,
        FriendService $friendService,
    ): Response {
        $me = $this->requireUser();

        if (!$this->isCsrfTokenValid('friend_cancel' . $friendRequest->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', FlashMessages::CSRF_INVALID);

            return $this->redirectToRoute('app_friends_index');
        }

        if ($friendService->cancelOutgoing($me, $friendRequest)) {
            $this->addFlash('success', 'Votre demande d’ami a été annulée.');
        } else {
            $this->addFlash('error', 'Cette demande n’existe plus ou ne peut pas être annulée. Actualisez la page.');
        }

        return $this->redirectToRoute('app_friends_index');
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
