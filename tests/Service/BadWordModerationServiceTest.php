<?php

namespace App\Tests\Service;

use App\Service\BadWordModerationService;
use PHPUnit\Framework\TestCase;

class BadWordModerationServiceTest extends TestCase
{
    private BadWordModerationService $service;

    protected function setUp(): void
    {
        $this->service = new BadWordModerationService();
    }

    public function testDetectWithCleanContent(): void
    {
        $content = "This is a clean post with no bad words.";
        $result = $this->service->detect($content);

        $this->assertFalse($result['containsBadWords']);
        $this->assertEmpty($result['badWords']);
        $this->assertEquals($content, $result['sanitizedText']);
    }

    public function testDetectWithBadWords(): void
    {
        $content = "This post is stupid and full of trash.";
        $result = $this->service->detect($content);

        $this->assertTrue($result['containsBadWords']);
        $this->assertContains('stupid', $result['badWords']);
        $this->assertContains('trash', $result['badWords']);
        $this->assertEquals("This post is ****** and full of *****.", $result['sanitizedText']);
    }

    public function testDetectCaseInsensitive(): void
    {
        $content = "STUPID content.";
        $result = $this->service->detect($content);

        $this->assertTrue($result['containsBadWords']);
        $this->assertContains('stupid', $result['badWords']);
        $this->assertEquals("****** content.", $result['sanitizedText']);
    }
}
