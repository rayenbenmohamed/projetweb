<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add evaluation fields (technical_rating, communication_rating, motivation_rating, final_verdict) to interview table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE interview 
            ADD COLUMN IF NOT EXISTS technical_rating INT DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS communication_rating INT DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS motivation_rating INT DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS final_verdict TEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE interview 
            DROP COLUMN IF EXISTS technical_rating,
            DROP COLUMN IF EXISTS communication_rating,
            DROP COLUMN IF EXISTS motivation_rating,
            DROP COLUMN IF EXISTS final_verdict
        ');
    }
}
