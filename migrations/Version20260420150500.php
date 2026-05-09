<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420150500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add forum_like relations and metadata for post likes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum_like ADD post_id INT NOT NULL, ADD user_id INT NOT NULL, ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE forum_like ADD CONSTRAINT FK_6F82E0644B89032C FOREIGN KEY (post_id) REFERENCES forum_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE forum_like ADD CONSTRAINT FK_6F82E064A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6F82E0644B89032C ON forum_like (post_id)');
        $this->addSql('CREATE INDEX IDX_6F82E064A76ED395 ON forum_like (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_forum_like_post_user ON forum_like (post_id, user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum_like DROP FOREIGN KEY FK_6F82E0644B89032C');
        $this->addSql('ALTER TABLE forum_like DROP FOREIGN KEY FK_6F82E064A76ED395');
        $this->addSql('DROP INDEX uniq_forum_like_post_user ON forum_like');
        $this->addSql('DROP INDEX IDX_6F82E0644B89032C ON forum_like');
        $this->addSql('DROP INDEX IDX_6F82E064A76ED395 ON forum_like');
        $this->addSql('ALTER TABLE forum_like DROP post_id, DROP user_id, DROP created_at');
    }
}
