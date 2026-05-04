<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420122000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add toggle column for two-factor authentication';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP two_factor_enabled');
    }
}

