<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260418120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le champ user.blocked pour suspendre les comptes (candidat / recruteur) par l’admin.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD blocked TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP blocked');
    }
}
