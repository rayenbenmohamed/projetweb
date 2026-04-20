<?php

namespace App\Controller;

use App\Entity\CalendarEvent;
use App\Entity\User;
use App\Repository\CalendarEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/app/calendar')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class CalendarController extends AbstractController
{
    #[Route('', name: 'app_calendar_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        CalendarEventRepository $calendarEventRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->requireUser();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('calendar_create', (string) $request->request->get('_token'))) {
                $this->addFlash('error', 'Session expirée. Rechargez la page puis réessayez.');

                return $this->redirectToRoute('app_calendar_index');
            }

            $title = trim((string) $request->request->get('title'));
            $description = trim((string) $request->request->get('description'));
            $startRaw = (string) $request->request->get('startAt');
            $endRaw = trim((string) $request->request->get('endAt'));

            if ($title === '') {
                $this->addFlash('error', 'Le titre est obligatoire.');

                return $this->redirectToRoute('app_calendar_index');
            }
            if (mb_strlen($title) > 150) {
                $this->addFlash('error', 'Le titre ne peut pas dépasser 150 caractères.');

                return $this->redirectToRoute('app_calendar_index');
            }

            $startAt = \DateTime::createFromFormat('Y-m-d\TH:i', $startRaw) ?: null;
            if (!$startAt instanceof \DateTime) {
                $this->addFlash('error', 'La date et l’heure de début sont invalides.');

                return $this->redirectToRoute('app_calendar_index');
            }

            $endAt = null;
            if ($endRaw !== '') {
                $endAt = \DateTime::createFromFormat('Y-m-d\TH:i', $endRaw) ?: null;
                if (!$endAt instanceof \DateTime) {
                    $this->addFlash('error', 'La date et l’heure de fin sont invalides.');

                    return $this->redirectToRoute('app_calendar_index');
                }
                if ($endAt < $startAt) {
                    $this->addFlash('error', 'La fin ne peut pas être avant le début.');

                    return $this->redirectToRoute('app_calendar_index');
                }
            }

            $event = new CalendarEvent();
            $event->setUser($user);
            $event->setTitle($title);
            $event->setDescription($description !== '' ? $description : null);
            $event->setStartAt($startAt);
            $event->setEndAt($endAt);
            $event->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Votre événement a été ajouté au calendrier.');

            return $this->redirectToRoute('app_calendar_index');
        }

        return $this->render('calendar/index.html.twig', [
            'events' => $calendarEventRepository->findUpcomingForUser($user),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_calendar_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        CalendarEvent $event,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->requireUser();
        if ($event->getUser()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('calendar_edit' . $event->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('error', 'Session expirée. Rechargez la page puis réessayez.');

                return $this->redirectToRoute('app_calendar_edit', ['id' => $event->getId()]);
            }

            $title = trim((string) $request->request->get('title'));
            $description = trim((string) $request->request->get('description'));
            $startRaw = (string) $request->request->get('startAt');
            $endRaw = trim((string) $request->request->get('endAt'));

            if ($title === '') {
                $this->addFlash('error', 'Le titre est obligatoire.');

                return $this->redirectToRoute('app_calendar_edit', ['id' => $event->getId()]);
            }

            $startAt = \DateTime::createFromFormat('Y-m-d\TH:i', $startRaw) ?: null;
            if (!$startAt instanceof \DateTime) {
                $this->addFlash('error', 'La date et l’heure de début sont invalides.');

                return $this->redirectToRoute('app_calendar_edit', ['id' => $event->getId()]);
            }

            $endAt = null;
            if ($endRaw !== '') {
                $endAt = \DateTime::createFromFormat('Y-m-d\TH:i', $endRaw) ?: null;
                if (!$endAt instanceof \DateTime) {
                    $this->addFlash('error', 'La date et l’heure de fin sont invalides.');

                    return $this->redirectToRoute('app_calendar_edit', ['id' => $event->getId()]);
                }
                if ($endAt < $startAt) {
                    $this->addFlash('error', 'La fin ne peut pas être avant le début.');

                    return $this->redirectToRoute('app_calendar_edit', ['id' => $event->getId()]);
                }
            }

            $event->setTitle($title);
            $event->setDescription($description !== '' ? $description : null);
            $event->setStartAt($startAt);
            $event->setEndAt($endAt);
            $event->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();
            $this->addFlash('success', 'Événement mis à jour.');

            return $this->redirectToRoute('app_calendar_index');
        }

        return $this->render('calendar/edit.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_calendar_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        Request $request,
        CalendarEvent $event,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->requireUser();
        if ($event->getUser()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('calendar_delete' . $event->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expirée. Rechargez la page puis réessayez.');

            return $this->redirectToRoute('app_calendar_index');
        }

        $entityManager->remove($event);
        $entityManager->flush();
        $this->addFlash('success', 'Événement supprimé.');

        return $this->redirectToRoute('app_calendar_index');
    }

    private function requireUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}

