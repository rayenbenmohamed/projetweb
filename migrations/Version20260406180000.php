<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Anciens dumps MySQL : password VARCHAR(50) tronque les hash bcrypt (~60 car.) → connexion impossible.
 */
final class Version20260406180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Élargit user.password (255) et user.role (80) pour Symfony PasswordHasher';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` MODIFY `password` VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE `user` MODIFY `role` VARCHAR(80) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` MODIFY `password` VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE `user` MODIFY `role` VARCHAR(50) NOT NULL');
    }
}
