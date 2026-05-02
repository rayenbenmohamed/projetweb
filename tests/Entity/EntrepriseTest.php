<?php

namespace App\Tests\Entity;

use App\Entity\Entreprise;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class EntrepriseTest extends TestCase
{
    public function testEntrepriseEntity(): void
    {
        $entreprise = new Entreprise();
        $user = new User();

        $entreprise->setName('Tech Corp')
            ->setDescription('Une entreprise de test')
            ->setWebsite('https://techcorp.com')
            ->setAddress('123 Test Street')
            ->setSector('Technologie')
            ->setPhone('21612345678')
            ->setUser($user);

        $this->assertEquals('Tech Corp', $entreprise->getName());
        $this->assertEquals('Une entreprise de test', $entreprise->getDescription());
        $this->assertEquals('https://techcorp.com', $entreprise->getWebsite());
        $this->assertEquals('123 Test Street', $entreprise->getAddress());
        $this->assertEquals('Technologie', $entreprise->getSector());
        $this->assertEquals('21612345678', $entreprise->getPhone());
        $this->assertSame($user, $entreprise->getUser());
    }

    public function testEmptyEntreprise(): void
    {
        $entreprise = new Entreprise();
        
        $this->assertNull($entreprise->getId());
        $this->assertNull($entreprise->getName());
        $this->assertCount(0, $entreprise->getJobOffres());
    }
}
