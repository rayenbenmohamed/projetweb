<?php

namespace App\Command;

use App\Repository\InterviewRepository;
use App\Service\NotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:interview-reminders',
    description: 'Envoie une notification de rappel 24h avant chaque entretien planifié.',
)]
class SendInterviewRemindersCommand extends Command
{
    public function __construct(
        private readonly InterviewRepository $interviewRepository,
        private readonly NotificationService $notificationService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('SyfonuRH — Rappels d\'entretiens 24h');

        // Fenêtre : dans 23h à 25h à partir de maintenant
        $now   = new \DateTime();
        $from  = (clone $now)->modify('+23 hours');
        $to    = (clone $now)->modify('+25 hours');

        $interviews = $this->interviewRepository->createQueryBuilder('i')
            ->join('i.application', 'a')
            ->where('i.scheduledAt BETWEEN :from AND :to')
            ->andWhere('i.status NOT IN (:done)')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('done', ['Réalisée', 'Archivée', 'Annulée'])
            ->getQuery()
            ->getResult();

        if (empty($interviews)) {
            $io->success('Aucun entretien dans les prochaines 24h.');
            return Command::SUCCESS;
        }

        $io->note(sprintf('%d entretien(s) trouvé(s) dans la fenêtre 24h.', count($interviews)));

        $sent = 0;
        foreach ($interviews as $interview) {
            $app        = $interview->getApplication();
            $candidate  = $app->getCandidat();
            $recruiter  = $app->getJobOffre()?->getUser();
            $offerTitle = $app->getJobOffre()?->getTitle() ?? 'votre candidature';
            $date       = $interview->getScheduledAt()->format('d/m/Y à H:i');

            // Notifier le candidat
            if ($candidate) {
                $this->notificationService->addNotification(
                    $candidate,
                    "⏰ Rappel : Votre entretien pour \"$offerTitle\" est prévu demain à $date. Soyez prêt(e) !"
                );
            }

            // Notifier le recruteur
            if ($recruiter) {
                $this->notificationService->addNotification(
                    $recruiter,
                    "📋 Rappel recruteur : Entretien avec {$candidate?->getFirstName()} {$candidate?->getLastName()} pour \"$offerTitle\" demain à $date."
                );
            }

            $io->writeln(sprintf('  ✓ Rappel envoyé pour entretien #%d (%s)', $interview->getId(), $date));
            $sent++;
        }

        $io->success(sprintf('%d rappel(s) envoyé(s) avec succès.', $sent));
        return Command::SUCCESS;
    }
}
