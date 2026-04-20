<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420124000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Increase two_factor_code length to store password hash';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` MODIFY two_factor_code VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` MODIFY two_factor_code VARCHAR(10) DEFAULT NULL');
    }
}

