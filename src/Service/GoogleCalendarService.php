<?php

namespace App\Service;

use App\Entity\Contract;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoogleCalendarService
{
    private Client $client;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
        $this->client = new Client();
        $this->client->setClientId($this->clientId);
        $this->client->setClientSecret($this->clientSecret);
        $this->client->setRedirectUri($this->redirectUri);
        $this->client->addScope(Calendar::CALENDAR_EVENTS);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
    }

    public function generateAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function authenticate(string $authCode, User $user): bool
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($authCode);
        
        if (isset($token['error'])) {
            return false;
        }

        $this->saveTokenToUser($token, $user);
        return true;
    }

    public function syncContract(Contract $contract, User $user): array
    {
        if (!$this->isUserAuthenticated($user)) {
            return ['success' => false, 'error' => 'User not authenticated with Google'];
        }

        try {
            $this->setupClientForUser($user);
            $service = new Calendar($this->client);

            $eventsCreated = [];

            // 1. Contract Start Event
            if ($contract->getStartDate()) {
                $event = $this->createContractEvent(
                    $contract, 
                    "Début Contrat: " . ($contract->getCandidate() ? $contract->getCandidate()->getFirstName() : 'Nouveau'),
                    $contract->getStartDate()
                );
                $result = $service->events->insert('primary', $event);
                $eventsCreated[] = $result->htmlLink;
            }

            // 2. Trial Period End (approx 90 days after start)
            if ($contract->getStartDate()) {
                $trialDate = (clone $contract->getStartDate())->modify('+90 days');
                $event = $this->createContractEvent(
                    $contract, 
                    "Fin Période d'essai: " . ($contract->getCandidate() ? $contract->getCandidate()->getFirstName() : ''),
                    $trialDate
                );
                $service->events->insert('primary', $event);
            }

            return ['success' => true, 'links' => $eventsCreated];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function isUserAuthenticated(User $user): bool
    {
        return $user->getGoogleAccessToken() !== null;
    }

    private function setupClientForUser(User $user): void
    {
        $this->client->setAccessToken($user->getGoogleAccessToken());

        if ($this->client->isAccessTokenExpired()) {
            if ($user->getGoogleRefreshToken()) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($user->getGoogleRefreshToken());
                if (!isset($newToken['error'])) {
                    $this->saveTokenToUser($newToken, $user);
                }
            }
        }
    }

    private function saveTokenToUser(array $token, User $user): void
    {
        $user->setGoogleAccessToken(json_encode($token));
        if (isset($token['refresh_token'])) {
            $user->setGoogleRefreshToken($token['refresh_token']);
        }
        
        $expiresAt = new \DateTime();
        $expiresAt->modify('+' . ($token['expires_in'] ?? 3600) . ' seconds');
        $user->setGoogleTokenExpiresAt($expiresAt);

        $this->entityManager->flush();
    }

    private function createContractEvent(Contract $contract, string $summary, \DateTimeInterface $date): Event
    {
        $event = new Event();
        $event->setSummary($summary);
        $event->setLocation('SyfonuRH Platform');
        $event->setDescription(sprintf(
            "Rappel automatisé pour le contrat #%d\nCandidat: %s\nType: %s\nLien: %s",
            $contract->getId(),
            $contract->getCandidate() ? ($contract->getCandidate()->getFirstName() . ' ' . $contract->getCandidate()->getLastName()) : '—',
            $contract->getTypeContrat() ? $contract->getTypeContrat()->getName() : '—',
            $this->urlGenerator->generate('app_contract_show', ['id' => $contract->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));

        $start = new EventDateTime();
        $start->setDate($date->format('Y-m-d'));
        $event->setStart($start);

        $end = new EventDateTime();
        $end->setDate($date->format('Y-m-d'));
        $event->setEnd($end);

        return $event;
    }
}
