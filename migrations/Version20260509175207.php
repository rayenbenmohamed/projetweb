<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260509175207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avantage (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract (id INT AUTO_INCREMENT NOT NULL, contract_type_id INT DEFAULT NULL, candidate_id INT NOT NULL, recruiter_id INT DEFAULT NULL, job_offer_id INT NOT NULL, pdf_template_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, salary INT NOT NULL, salaire_net DOUBLE PRECISION DEFAULT NULL, status VARCHAR(50) NOT NULL, is_signed TINYINT(1) NOT NULL, signed_at DATETIME DEFAULT NULL, signature_base64 LONGTEXT DEFAULT NULL, google_event_id_start VARCHAR(255) DEFAULT NULL, google_event_id_end VARCHAR(255) DEFAULT NULL, google_event_id_trial VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, INDEX IDX_E98F2859CD1DF15B (contract_type_id), INDEX IDX_E98F285991BD8781 (candidate_id), INDEX IDX_E98F2859156BE243 (recruiter_id), INDEX IDX_E98F28593481D195 (job_offer_id), INDEX IDX_E98F2859CA5AA7D3 (pdf_template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cv (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, full_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, birth_date DATE DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, summary LONGTEXT DEFAULT NULL, education LONGTEXT DEFAULT NULL, experience LONGTEXT DEFAULT NULL, skills LONGTEXT DEFAULT NULL, cv_file VARCHAR(255) DEFAULT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B66FFE92A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, logo_public_id VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, sector VARCHAR(255) DEFAULT NULL, size VARCHAR(100) DEFAULT NULL, founded_at INT DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, contact_email VARCHAR(255) DEFAULT NULL, social_linkedin VARCHAR(255) DEFAULT NULL, slogan VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_D19FA60A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE friend_message (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, recipient_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, read_at DATETIME DEFAULT NULL, INDEX IDX_8202F274F624B39D (sender_id), INDEX IDX_8202F274E92F8F78 (recipient_id), INDEX idx_friend_msg_pair (sender_id, recipient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE friend_request (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_F284D94F624B39D (sender_id), INDEX IDX_F284D94CD53EDB6 (receiver_id), UNIQUE INDEX friend_request_pair (sender_id, receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE interview (id INT AUTO_INCREMENT NOT NULL, application_id INT NOT NULL, scheduled_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, meeting_link VARCHAR(255) DEFAULT NULL, technical_rating INT DEFAULT NULL, communication_rating INT DEFAULT NULL, motivation_rating INT DEFAULT NULL, final_verdict LONGTEXT DEFAULT NULL, outcome VARCHAR(20) DEFAULT NULL, completed_at DATETIME DEFAULT NULL, INDEX IDX_CF1D3C343E030ACD (application_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_application (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, job_offre_id INT NOT NULL, application_status VARCHAR(50) NOT NULL, apply_date DATETIME NOT NULL, cover_letter LONGTEXT DEFAULT NULL, cv_path VARCHAR(255) DEFAULT NULL, ai_score INT DEFAULT NULL, ai_analysis LONGTEXT DEFAULT NULL, ai_analyzed_at DATETIME DEFAULT NULL, INDEX idx_job_app_candidat (user_id), INDEX idx_job_app_offre (job_offre_id), INDEX idx_job_app_status (application_status), INDEX idx_job_app_date (apply_date), INDEX idx_job_app_ai_score (ai_score), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_offre (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, entreprise_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, salary DOUBLE PRECISION DEFAULT NULL, publishedAt DATETIME DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, expires_at DATETIME DEFAULT NULL, employment_type VARCHAR(100) DEFAULT NULL, is_salary_negotiable TINYINT(1) NOT NULL, advantages LONGTEXT DEFAULT NULL, company_logo VARCHAR(500) DEFAULT NULL, company_logo_public_id VARCHAR(255) DEFAULT NULL, skills LONGTEXT DEFAULT NULL, INDEX IDX_AEDA3B1FA4AEAFEA (entreprise_id), INDEX idx_job_offre_status (status), INDEX idx_job_offre_user (user_id), INDEX idx_job_offre_created (created_at), INDEX idx_job_offre_expires (expires_at), INDEX idx_job_offre_type (employment_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_offre_avantage (job_offre_id INT NOT NULL, avantage_id INT NOT NULL, INDEX IDX_E6DA9B2B8FF521 (job_offre_id), INDEX IDX_E6DA9BEA96B22C (avantage_id), PRIMARY KEY(job_offre_id, avantage_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pdf_template (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, logo_path VARCHAR(255) DEFAULT NULL, primary_color VARCHAR(20) DEFAULT NULL, secondary_color VARCHAR(20) DEFAULT NULL, header_html LONGTEXT DEFAULT NULL, footer_html LONGTEXT DEFAULT NULL, body_html LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859CD1DF15B FOREIGN KEY (contract_type_id) REFERENCES contract_type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F285991BD8781 FOREIGN KEY (candidate_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859156BE243 FOREIGN KEY (recruiter_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F28593481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859CA5AA7D3 FOREIGN KEY (pdf_template_id) REFERENCES pdf_template (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE92A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE entreprise ADD CONSTRAINT FK_D19FA60A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE friend_message ADD CONSTRAINT FK_8202F274F624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend_message ADD CONSTRAINT FK_8202F274E92F8F78 FOREIGN KEY (recipient_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend_request ADD CONSTRAINT FK_F284D94F624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend_request ADD CONSTRAINT FK_F284D94CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE interview ADD CONSTRAINT FK_CF1D3C343E030ACD FOREIGN KEY (application_id) REFERENCES job_application (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_application ADD CONSTRAINT FK_C737C688A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE job_application ADD CONSTRAINT FK_C737C6882B8FF521 FOREIGN KEY (job_offre_id) REFERENCES job_offre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offre ADD CONSTRAINT FK_AEDA3B1FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE job_offre ADD CONSTRAINT FK_AEDA3B1FA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE job_offre_avantage ADD CONSTRAINT FK_E6DA9B2B8FF521 FOREIGN KEY (job_offre_id) REFERENCES job_offre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offre_avantage ADD CONSTRAINT FK_E6DA9BEA96B22C FOREIGN KEY (avantage_id) REFERENCES avantage (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE document_candidate');
        $this->addSql('DROP TABLE document_contract');
        $this->addSql('ALTER TABLE forum_category CHANGE name name VARCHAR(180) NOT NULL');
        $this->addSql('CREATE INDEX idx_notif_user_read ON notification (user_id, is_read)');
        $this->addSql('ALTER TABLE user CHANGE role role VARCHAR(80) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document_candidate (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE document_contract (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859CD1DF15B');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F285991BD8781');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859156BE243');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F28593481D195');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859CA5AA7D3');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY FK_B66FFE92A76ED395');
        $this->addSql('ALTER TABLE entreprise DROP FOREIGN KEY FK_D19FA60A76ED395');
        $this->addSql('ALTER TABLE friend_message DROP FOREIGN KEY FK_8202F274F624B39D');
        $this->addSql('ALTER TABLE friend_message DROP FOREIGN KEY FK_8202F274E92F8F78');
        $this->addSql('ALTER TABLE friend_request DROP FOREIGN KEY FK_F284D94F624B39D');
        $this->addSql('ALTER TABLE friend_request DROP FOREIGN KEY FK_F284D94CD53EDB6');
        $this->addSql('ALTER TABLE interview DROP FOREIGN KEY FK_CF1D3C343E030ACD');
        $this->addSql('ALTER TABLE job_application DROP FOREIGN KEY FK_C737C688A76ED395');
        $this->addSql('ALTER TABLE job_application DROP FOREIGN KEY FK_C737C6882B8FF521');
        $this->addSql('ALTER TABLE job_offre DROP FOREIGN KEY FK_AEDA3B1FA76ED395');
        $this->addSql('ALTER TABLE job_offre DROP FOREIGN KEY FK_AEDA3B1FA4AEAFEA');
        $this->addSql('ALTER TABLE job_offre_avantage DROP FOREIGN KEY FK_E6DA9B2B8FF521');
        $this->addSql('ALTER TABLE job_offre_avantage DROP FOREIGN KEY FK_E6DA9BEA96B22C');
        $this->addSql('DROP TABLE avantage');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE contract_type');
        $this->addSql('DROP TABLE cv');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE friend_message');
        $this->addSql('DROP TABLE friend_request');
        $this->addSql('DROP TABLE interview');
        $this->addSql('DROP TABLE job_application');
        $this->addSql('DROP TABLE job_offre');
        $this->addSql('DROP TABLE job_offre_avantage');
        $this->addSql('DROP TABLE pdf_template');
        $this->addSql('ALTER TABLE forum_category CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX idx_notif_user_read ON notification');
        $this->addSql('ALTER TABLE `user` CHANGE role role VARCHAR(50) NOT NULL');
    }
}
