<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260418203000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Approbation recruteurs (approved) + RNE entreprise (company_rne).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD company_rne VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` ADD approved TINYINT(1) NOT NULL DEFAULT 1');
        // Tous les comptes existants restent utilisables ; les futurs recruteurs (inscription) seront approved=0 côté appli.
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP company_rne');
        $this->addSql('ALTER TABLE `user` DROP approved');
    }
}
