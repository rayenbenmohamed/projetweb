<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260413232123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pdf_template (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, logo_path VARCHAR(255) DEFAULT NULL, primary_color VARCHAR(20) DEFAULT NULL, secondary_color VARCHAR(20) DEFAULT NULL, header_html LONGTEXT DEFAULT NULL, footer_html LONGTEXT DEFAULT NULL, body_html LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contract ADD pdf_template_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859CA5AA7D3 FOREIGN KEY (pdf_template_id) REFERENCES pdf_template (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_E98F2859CA5AA7D3 ON contract (pdf_template_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE pdf_template');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859CA5AA7D3');
        $this->addSql('DROP INDEX IDX_E98F2859CA5AA7D3 ON contract');
        $this->addSql('ALTER TABLE contract DROP pdf_template_id');
    }
}
