<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fix-user-table-columns',
    description: 'Élargit user.password (255) et user.role (80) — nécessaire si la connexion échoue à cause d’anciens VARCHAR(50).',
)]
class FixUserTableColumnsCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->connection->executeStatement(
                'ALTER TABLE `user` MODIFY `password` VARCHAR(255) NOT NULL'
            );
            $this->connection->executeStatement(
                'ALTER TABLE `user` MODIFY `role` VARCHAR(80) NOT NULL'
            );
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Colonnes `user.password` et `user.role` mises à jour. Exécutez ensuite : php bin/console app:create-admin --reset');

        return Command::SUCCESS;
    }
}
