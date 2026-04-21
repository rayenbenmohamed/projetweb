<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un utilisateur administrateur par défaut.',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@syfonu.com']);

        if (!$user) {
            $user = new User();
            $user->setEmail('admin@syfonu.com');
            $user->setFirstName('Admin');
            $user->setLastName('Syfonu');
            $user->setRoles(['ROLE_RECRUTEUR']);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'admin123')
            );
            $this->entityManager->persist($user);
        }


        $this->entityManager->flush();

        $io->success('Utilisateur admin@syfonu.com et une offre exemple ont été créés.');

        return Command::SUCCESS;
    }
}
