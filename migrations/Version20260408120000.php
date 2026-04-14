<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260408120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'friend_message.read_at for unread message notifications';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE friend_message ADD read_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE friend_message SET read_at = created_at WHERE read_at IS NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE friend_message DROP read_at');
    }
}
