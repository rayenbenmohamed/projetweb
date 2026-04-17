<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260411220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add AI scoring columns (ai_score, ai_analysis, ai_analyzed_at) to job_application table';
    }

    public function up(Schema $schema): void
    {
        // Add columns only if they don't already exist
        $this->addSql('ALTER TABLE job_application 
            ADD COLUMN IF NOT EXISTS ai_score INT DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS ai_analysis LONGTEXT DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS ai_analyzed_at DATETIME DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE job_application 
            DROP COLUMN IF EXISTS ai_score,
            DROP COLUMN IF EXISTS ai_analysis,
            DROP COLUMN IF EXISTS ai_analyzed_at
        ');
    }
}
