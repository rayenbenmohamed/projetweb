<?php

namespace App\Tests\Entity;

use App\Entity\JobOffre;
use App\Entity\Entreprise;
use PHPUnit\Framework\TestCase;

class JobOffreTest extends TestCase
{
    public function testJobOffreEntity(): void
    {
        $jobOffre = new JobOffre();
        $entreprise = new Entreprise();
        $date = new \DateTime();

        $jobOffre->setTitle('Développeur PHP Symfony')
            ->setDescription('Développement de plateformes web')
            ->setLocation('Tunis')
            ->setSalary('2500 - 3500 DT')
            ->setJobType('CDI')
            ->setExperienceLevel('Intermédiaire')
            ->setCreatedAt($date)
            ->setEntreprise($entreprise);

        $this->assertEquals('Développeur PHP Symfony', $jobOffre->getTitle());
        $this->assertEquals('Développement de plateformes web', $jobOffre->getDescription());
        $this->assertEquals('Tunis', $jobOffre->getLocation());
        $this->assertEquals('2500 - 3500 DT', $jobOffre->getSalary());
        $this->assertEquals('CDI', $jobOffre->getJobType());
        $this->assertEquals('Intermédiaire', $jobOffre->getExperienceLevel());
        $this->assertSame($date, $jobOffre->getCreatedAt());
        $this->assertSame($entreprise, $jobOffre->getEntreprise());
    }

    public function testEmptyJobOffre(): void
    {
        $jobOffre = new JobOffre();
        
        $this->assertNull($jobOffre->getId());
        $this->assertNull($jobOffre->getTitle());
    }
}
