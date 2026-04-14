<?php

namespace App\Service;

use App\Entity\JobApplication;
use App\Entity\JobOffre;
use App\Entity\User;
use App\Repository\JobApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;

class JobApplicationService
{
    private EntityManagerInterface $entityManager;
    private JobApplicationRepository $jobApplicationRepository;
    private NotificationService $notificationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        JobApplicationRepository $jobApplicationRepository,
        NotificationService $notificationService
    ) {
        $this->entityManager = $entityManager;
        $this->jobApplicationRepository = $jobApplicationRepository;
        $this->notificationService = $notificationService;
    }

    public function apply(JobApplication $application, User $candidat): void
    {
        $application->setCandidat($candidat);
        $application->setApplyDate(new \DateTime());
        $application->setStatus('PENDING');

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        // Ported Notification logic
        $jobOffre = $application->getJobOffre();
        $recruiter = $jobOffre->getUser();

        if ($recruiter) {
            $message = sprintf(
                "Nouvelle candidature pour l'offre '%s' par %s %s",
                $jobOffre->getTitle(),
                $candidat->getFirstName(),
                $candidat->getLastName()
            );
            $this->notificationService->addNotification($recruiter, $message);
        }
    }

    /**
     * @return JobApplication[]
     */
    public function getCandidaturesByOffre(JobOffre $jobOffre): array
    {
        return $this->jobApplicationRepository->findBy(['jobOffre' => $jobOffre]);
    }

    public function updateStatus(JobApplication $application, string $status): void
    {
        $application->setStatus($status);
        $this->entityManager->flush();
    }
}
