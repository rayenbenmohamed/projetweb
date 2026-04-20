<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add profile_photo_url column to user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD profile_photo_url VARCHAR(512) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP profile_photo_url');
    }
}

