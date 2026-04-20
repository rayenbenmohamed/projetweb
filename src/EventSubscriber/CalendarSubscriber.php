<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\CalendarEventRepository;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CalendarEventRepository $calendarEventRepository,
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $start = $calendar->getStart();
        $end = $calendar->getEnd();

        $events = $this->calendarEventRepository->findForUserBetween($user, $start, $end);
        foreach ($events as $calendarEvent) {
            $event = new Event(
                (string) $calendarEvent->getTitle(),
                $calendarEvent->getStartAt(),
                $calendarEvent->getEndAt()
            );

            $event->setOptions([
                'backgroundColor' => '#1f7ad8',
                'borderColor' => '#1f7ad8',
                'textColor' => '#ffffff',
                'url' => $this->urlGenerator->generate('app_calendar_edit', ['id' => $calendarEvent->getId()]),
            ]);

            $event->addOption('description', (string) ($calendarEvent->getDescription() ?? ''));
            $calendar->addEvent($event);
        }
    }
}

