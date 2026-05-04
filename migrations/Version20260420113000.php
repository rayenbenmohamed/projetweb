<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user personal calendar events table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE calendar_event (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, start_at DATETIME NOT NULL, end_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A9F3F5A76A24B9D (user_id), INDEX IDX_A9F3F5A7AE35CE8F (start_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE calendar_event ADD CONSTRAINT FK_A9F3F5A76A24B9D FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calendar_event DROP FOREIGN KEY FK_A9F3F5A76A24B9D');
        $this->addSql('DROP TABLE calendar_event');
    }
}

