<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260407130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'friend_request + friend_message (amis + messagerie)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE friend_request (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX friend_request_pair (sender_id, receiver_id), INDEX IDX_FR_RECV (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE friend_message (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, recipient_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX idx_friend_msg_pair (sender_id, recipient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE friend_request ADD CONSTRAINT FK_FR_SENDER FOREIGN KEY (sender_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend_request ADD CONSTRAINT FK_FR_RECV FOREIGN KEY (receiver_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend_message ADD CONSTRAINT FK_FM_SENDER FOREIGN KEY (sender_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend_message ADD CONSTRAINT FK_FM_RECIP FOREIGN KEY (recipient_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE friend_message DROP FOREIGN KEY FK_FM_RECIP');
        $this->addSql('ALTER TABLE friend_message DROP FOREIGN KEY FK_FM_SENDER');
        $this->addSql('ALTER TABLE friend_request DROP FOREIGN KEY FK_FR_RECV');
        $this->addSql('ALTER TABLE friend_request DROP FOREIGN KEY FK_FR_SENDER');
        $this->addSql('DROP TABLE friend_message');
        $this->addSql('DROP TABLE friend_request');
    }
}
