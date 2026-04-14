<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260413220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add company_logo and company_logo_public_id columns to job_offre table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE job_offre ADD company_logo VARCHAR(500) DEFAULT NULL, ADD company_logo_public_id VARCHAR(255) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE job_offre DROP COLUMN company_logo, DROP COLUMN company_logo_public_id");
    }
}
