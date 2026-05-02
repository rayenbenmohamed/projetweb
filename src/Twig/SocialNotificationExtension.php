<?php

namespace App\Twig;

use App\Service\SocialNotificationService;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class SocialNotificationExtension extends AbstractExtension
{
    public function __construct(
        private readonly SocialNotificationService $socialNotificationService,
        private readonly Security $security,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('social_notifications', $this->getCounts(...)),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('json_decode', [$this, 'jsonDecode']),
        ];
    }

    public function jsonDecode(?string $string): array
    {
        if (!$string) {
            return [];
        }
        return json_decode($string, true) ?? [];
    }

    /**
     * @return array{friend_requests: int, unread_messages: int, total: int}
     */
    public function getCounts(): array
    {
        return $this->socialNotificationService->getCounts($this->security->getUser());
    }
}
