<?php

namespace App\Command;

use App\Entity\Candidat;
use App\Entity\JobOffre;
use App\Entity\OfferStatus;
use App\Entity\Recruiter;
use App\Entity\TypeContrat;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-database',
    description: 'Rplit la base de données avec des données de test (Candidats, Recruteurs, Offres, Types de Contrat).',
)]
class SeedDatabaseCommand extends Command
{
    private const DEFAULT_PASSWORD = 'password123';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 1. Types de Contrat
        $contractTypesData = [
            ['name' => 'CDI', 'description' => 'Contrat à Durée Indéterminée'],
            ['name' => 'CDD', 'description' => 'Contrat à Durée Déterminée'],
            ['name' => 'Freelance', 'description' => 'Travailleur indépendant'],
            ['name' => 'Stage', 'description' => 'Stage de fin d\'études ou d\'immersion'],
        ];

        $contractTypes = [];
        foreach ($contractTypesData as $data) {
            $existing = $this->entityManager->getRepository(TypeContrat::class)->findOneBy(['name' => $data['name']]);
            if (!$existing) {
                $type = new TypeContrat();
                $type->setName($data['name']);
                $type->setDescription($data['description']);
                $this->entityManager->persist($type);
                $contractTypes[$data['name']] = $type;
                $io->note(sprintf('Type de contrat %s créé.', $data['name']));
            } else {
                $contractTypes[$data['name']] = $existing;
            }
        }

        // 2. Candidats
        $candidatesData = [
            ['email' => 'oussema@example.com', 'firstName' => 'Oussema', 'lastName' => 'Ben Ammar'],
            ['email' => 'rayen@example.com', 'firstName' => 'Rayen', 'lastName' => 'Ben Mohamed'],
        ];

        foreach ($candidatesData as $data) {
            $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if (!$existing) {
                $candidat = new Candidat();
                $candidat->setEmail($data['email']);
                $candidat->setFirstName($data['firstName']);
                $candidat->setLastName($data['lastName']);
                $hash = $this->passwordHasher->hashPassword($candidat, self::DEFAULT_PASSWORD);
                $candidat->setPassword($hash);
                $candidat->setRoles(['ROLE_CANDIDAT']);
                $this->entityManager->persist($candidat);
                $io->note(sprintf('Candidat %s créé.', $data['firstName']));
            }
        }

        // 3. Recruteurs
        $recruitersData = [
            ['email' => 'hachem@company.com', 'firstName' => 'Hachem', 'lastName' => 'Oueslati', 'company' => 'Tech Solutions'],
            ['email' => 'habib@company.com', 'firstName' => 'Habib', 'lastName' => 'Trabelsi', 'company' => 'Innovate IT'],
            ['email' => 'taha@company.com', 'firstName' => 'Taha', 'lastName' => 'Gharbi', 'company' => 'Future Digital'],
        ];

        $recruiters = [];
        foreach ($recruitersData as $data) {
            $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if (!$existing) {
                $recruiter = new Recruiter();
                $recruiter->setEmail($data['email']);
                $recruiter->setFirstName($data['firstName']);
                $recruiter->setLastName($data['lastName']);
                $recruiter->setCompanyname($data['company']);
                $hash = $this->passwordHasher->hashPassword($recruiter, self::DEFAULT_PASSWORD);
                $recruiter->setPassword($hash);
                $recruiter->setRoles(['ROLE_RECRUTEUR']);
                $this->entityManager->persist($recruiter);
                $recruiters[] = $recruiter;
                $io->note(sprintf('Recruteur %s créé.', $data['firstName']));
            } else {
                if ($existing instanceof Recruiter) {
                    $recruiters[] = $existing;
                }
            }
        }

        $this->entityManager->flush();

        // 4. Offres d'emploi
        $offersData = [
            [
                'title' => 'Développeur Fullstack PHP/Symfony',
                'description' => 'Nous recherchons un développeur passionné par Symfony pour rejoindre notre équipe dynamique.',
                'location' => 'Tunis',
                'salary' => 2500,
                'employmentType' => 'CDI',
                'recruiter' => $recruiters[0] ?? null,
            ],
            [
                'title' => 'Designer UI/UX Senior',
                'description' => 'Concevez des interfaces modernes et intuitives pour nos clients internationaux.',
                'location' => 'Sousse',
                'salary' => 1800,
                'employmentType' => 'CDD',
                'recruiter' => $recruiters[1] ?? null,
            ],
            [
                'title' => 'Expert Infrastructure & Cloud',
                'description' => 'Gérez nos serveurs et optimisez nos déploiements AWS/Azure.',
                'location' => 'Ariana',
                'salary' => 3500,
                'employmentType' => 'Freelance',
                'recruiter' => $recruiters[2] ?? null,
            ],
        ];

        foreach ($offersData as $data) {
            if (!$data['recruiter']) continue;

            $existing = $this->entityManager->getRepository(JobOffre::class)->findOneBy(['title' => $data['title']]);
            if (!$existing) {
                $offer = new JobOffre();
                $offer->setTitle($data['title']);
                $offer->setDescription($data['description']);
                $offer->setLocation($data['location']);
                $offer->setSalary($data['salary']);
                $offer->setEmploymentType($data['employmentType']);
                $offer->setStatus(OfferStatus::PUBLISHED->value);
                $offer->setUser($data['recruiter']);
                $offer->setCreatedAt(new \DateTime());
                $offer->setPublishedAt(new \DateTime());
                $this->entityManager->persist($offer);
                $io->note(sprintf('Offre d\'emploi %s créée.', $data['title']));
            }
        }

        $this->entityManager->flush();

        $io->success('La base de données a été remplie avec succès !');
        $io->info('E-mails : oussema@example.com, rayen@example.com, hachem@company.com, habib@company.com, taha@company.com');
        $io->info('Mot de passe par défaut : password123');

        return Command::SUCCESS;
    }
}
