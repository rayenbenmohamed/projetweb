<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406172900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_id to forum_comment';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum_comment ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE forum_comment ADD CONSTRAINT FK_65B81F1DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_65B81F1DA76ED395 ON forum_comment (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum_comment DROP FOREIGN KEY FK_65B81F1DA76ED395');
        $this->addSql('DROP INDEX IDX_65B81F1DA76ED395 ON forum_comment');
        $this->addSql('ALTER TABLE forum_comment DROP user_id');
    }
}
