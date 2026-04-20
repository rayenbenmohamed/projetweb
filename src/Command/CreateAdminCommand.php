<?php

namespace App\Command;

use App\Entity\Admin;
use App\Entity\JobOffre;
use App\Entity\User;
use App\Entity\OfferStatus;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée ou réinitialise le compte admin@syfonu.com (mot de passe par défaut : 1234).',
)]
class CreateAdminCommand extends Command
{
    private const ADMIN_EMAIL = 'admin@syfonu.com';
    private const DEFAULT_PASSWORD = '1234';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'reset',
            null,
            InputOption::VALUE_NONE,
            'Réinitialise le compte existant : rôle admin, mot de passe 1234, type administrateur'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $reset = $input->getOption('reset');
        $repository = $this->entityManager->getRepository(User::class);
        $existing = $repository->findOneBy(['email' => self::ADMIN_EMAIL]);

        $subject = $existing ?? new Admin();
        if (!$existing) {
            $subject->setEmail(self::ADMIN_EMAIL);
        }
        $hash = $this->passwordHasher->hashPassword($subject, self::DEFAULT_PASSWORD);

        if ($existing && $reset) {
            $this->connection->executeStatement(
                'UPDATE `user` SET discr = ?, `role` = ?, password = ?, firstName = COALESCE(firstName, ?), lastName = COALESCE(lastName, ?), approved = 1 WHERE email = ?',
                ['admin', 'ROLE_ADMIN', $hash, 'Admin', 'Syfonu', self::ADMIN_EMAIL]
            );
            $io->success('Compte ' . self::ADMIN_EMAIL . ' réinitialisé : administrateur, mot de passe **' . self::DEFAULT_PASSWORD . '**. Reconnectez-vous.');
            return Command::SUCCESS;
        }

        if ($existing && !$reset) {
            $io->warning('Un compte ' . self::ADMIN_EMAIL . ' existe déjà. Utilisez --reset pour forcer le mot de passe **' . self::DEFAULT_PASSWORD . '** et le rôle administrateur.');
            return Command::SUCCESS;
        }

        $user = new Admin();
        $user->setEmail(self::ADMIN_EMAIL);
        $user->setFirstName('Admin');
        $user->setLastName('Syfonu');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($hash);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $job = new JobOffre();
        $job->setTitle('Développeur Symfony Senior');
        $job->setLocation('Remote');
        $job->setSalary(55000);
        $job->setEmploymentType('CDI');
        $job->setStatus(OfferStatus::PUBLISHED->value);
        $job->setUser($user);
        $job->setCreatedAt(new \DateTime());
        $job->setDescription('Rejoignez notre équipe pour construire le futur des RH !');
        $this->entityManager->persist($job);
        $this->entityManager->flush();

        $io->success('Compte ' . self::ADMIN_EMAIL . ' créé (mot de passe : **' . self::DEFAULT_PASSWORD . '**) et une offre exemple ajoutée.');

        return Command::SUCCESS;
    }
}
