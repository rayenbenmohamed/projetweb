<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260427193217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SET foreign_key_checks = 0');
        $this->addSql('SET SESSION sql_mode = ""');

        $this->addSql('CREATE TABLE IF NOT EXISTS `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, role VARCHAR(80) NOT NULL, password VARCHAR(255) NOT NULL, firstName VARCHAR(255) DEFAULT NULL, lastName VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, two_factor_code VARCHAR(10) DEFAULT NULL, two_factor_expiry DATETIME DEFAULT NULL, reset_token VARCHAR(100) DEFAULT NULL, reset_token_expiry DATETIME DEFAULT NULL, google_access_token LONGTEXT DEFAULT NULL, google_refresh_token LONGTEXT DEFAULT NULL, google_token_expires_at DATETIME DEFAULT NULL, discr VARCHAR(255) NOT NULL, companyname VARCHAR(255) DEFAULT NULL, departement VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `pdf_template` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, logo_path VARCHAR(255) DEFAULT NULL, primary_color VARCHAR(20) DEFAULT NULL, secondary_color VARCHAR(20) DEFAULT NULL, header_html LONGTEXT DEFAULT NULL, footer_html LONGTEXT DEFAULT NULL, body_html LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `job_offre` (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, salary DOUBLE PRECISION DEFAULT NULL, publishedAt DATETIME DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, expires_at DATETIME DEFAULT NULL, employment_type VARCHAR(100) DEFAULT NULL, is_salary_negotiable TINYINT(1) NOT NULL, advantages LONGTEXT DEFAULT NULL, user_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `contract_type` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `contract` (id INT AUTO_INCREMENT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, salary INT NOT NULL, salaire_net DOUBLE PRECISION DEFAULT NULL, status VARCHAR(50) NOT NULL, is_signed TINYINT(1) NOT NULL, signed_at DATETIME DEFAULT NULL, signature_base64 LONGTEXT DEFAULT NULL, google_event_id_start VARCHAR(255) DEFAULT NULL, google_event_id_end VARCHAR(255) DEFAULT NULL, google_event_id_trial VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, contract_type_id INT DEFAULT NULL, candidate_id INT NOT NULL, recruiter_id INT DEFAULT NULL, job_offer_id INT NOT NULL, pdf_template_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `cv` (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, birth_date DATE DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, summary LONGTEXT DEFAULT NULL, education LONGTEXT DEFAULT NULL, experience LONGTEXT DEFAULT NULL, skills LONGTEXT DEFAULT NULL, cv_file VARCHAR(255) DEFAULT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `forum_category` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_21BF94265E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `forum_post` (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, active TINYINT(1) NOT NULL, image_path VARCHAR(255) DEFAULT NULL, category_id INT DEFAULT NULL, user_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `forum_comment` (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, post_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `forum_like` (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `friend_message` (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, read_at DATETIME DEFAULT NULL, sender_id INT NOT NULL, recipient_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `friend_request` (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `interview` (id INT AUTO_INCREMENT NOT NULL, scheduled_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, meeting_link VARCHAR(255) DEFAULT NULL, application_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `job_application` (id INT AUTO_INCREMENT NOT NULL, application_status VARCHAR(50) NOT NULL, apply_date DATETIME NOT NULL, cover_letter LONGTEXT DEFAULT NULL, cv_path VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, job_offre_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `notification` (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `cover_letter` (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');

        // Foreign keys — ignore errors if they already exist
        $this->addSql('ALTER TABLE `contract` ADD CONSTRAINT FK_E98F2859CD1DF15B FOREIGN KEY IF NOT EXISTS (contract_type_id) REFERENCES contract_type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `contract` ADD CONSTRAINT FK_E98F285991BD8781 FOREIGN KEY IF NOT EXISTS (candidate_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `contract` ADD CONSTRAINT FK_E98F2859156BE243 FOREIGN KEY IF NOT EXISTS (recruiter_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `contract` ADD CONSTRAINT FK_E98F28593481D195 FOREIGN KEY IF NOT EXISTS (job_offer_id) REFERENCES job_offre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `contract` ADD CONSTRAINT FK_E98F2859CA5AA7D3 FOREIGN KEY IF NOT EXISTS (pdf_template_id) REFERENCES pdf_template (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `cv` ADD CONSTRAINT FK_B66FFE92A76ED395 FOREIGN KEY IF NOT EXISTS (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `forum_post` ADD CONSTRAINT FK_996BCC5A12469DE2 FOREIGN KEY IF NOT EXISTS (category_id) REFERENCES forum_category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `forum_post` ADD CONSTRAINT FK_996BCC5AA76ED395 FOREIGN KEY IF NOT EXISTS (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `forum_comment` ADD CONSTRAINT FK_65B81F1D4B89032C FOREIGN KEY IF NOT EXISTS (post_id) REFERENCES forum_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `friend_message` ADD CONSTRAINT FK_8202F274F624B39D FOREIGN KEY IF NOT EXISTS (sender_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `friend_message` ADD CONSTRAINT FK_8202F274E92F8F78 FOREIGN KEY IF NOT EXISTS (recipient_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `friend_request` ADD CONSTRAINT FK_F284D94F624B39D FOREIGN KEY IF NOT EXISTS (sender_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `friend_request` ADD CONSTRAINT FK_F284D94CD53EDB6 FOREIGN KEY IF NOT EXISTS (receiver_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `interview` ADD CONSTRAINT FK_CF1D3C343E030ACD FOREIGN KEY IF NOT EXISTS (application_id) REFERENCES job_application (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `job_application` ADD CONSTRAINT FK_C737C688A76ED395 FOREIGN KEY IF NOT EXISTS (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `job_application` ADD CONSTRAINT FK_C737C6882B8FF521 FOREIGN KEY IF NOT EXISTS (job_offre_id) REFERENCES job_offre (id)');
        $this->addSql('ALTER TABLE `job_offre` ADD CONSTRAINT FK_AEDA3B1FA76ED395 FOREIGN KEY IF NOT EXISTS (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `notification` ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY IF NOT EXISTS (user_id) REFERENCES `user` (id) ON DELETE CASCADE');

        $this->addSql('SET foreign_key_checks = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE type_contrat (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859CD1DF15B');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F285991BD8781');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859156BE243');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F28593481D195');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859CA5AA7D3');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY FK_B66FFE92A76ED395');
        $this->addSql('ALTER TABLE forum_comment DROP FOREIGN KEY FK_65B81F1D4B89032C');
        $this->addSql('ALTER TABLE forum_post DROP FOREIGN KEY FK_996BCC5A12469DE2');
        $this->addSql('ALTER TABLE forum_post DROP FOREIGN KEY FK_996BCC5AA76ED395');
        $this->addSql('ALTER TABLE friend_message DROP FOREIGN KEY FK_8202F274F624B39D');
        $this->addSql('ALTER TABLE friend_message DROP FOREIGN KEY FK_8202F274E92F8F78');
        $this->addSql('ALTER TABLE friend_request DROP FOREIGN KEY FK_F284D94F624B39D');
        $this->addSql('ALTER TABLE friend_request DROP FOREIGN KEY FK_F284D94CD53EDB6');
        $this->addSql('ALTER TABLE interview DROP FOREIGN KEY FK_CF1D3C343E030ACD');
        $this->addSql('ALTER TABLE job_application DROP FOREIGN KEY FK_C737C688A76ED395');
        $this->addSql('ALTER TABLE job_application DROP FOREIGN KEY FK_C737C6882B8FF521');
        $this->addSql('ALTER TABLE job_offre DROP FOREIGN KEY FK_AEDA3B1FA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE contract_type');
        $this->addSql('DROP TABLE cover_letter');
        $this->addSql('DROP TABLE cv');
        $this->addSql('DROP TABLE forum_category');
        $this->addSql('DROP TABLE forum_comment');
        $this->addSql('DROP TABLE forum_like');
        $this->addSql('DROP TABLE forum_post');
        $this->addSql('DROP TABLE friend_message');
        $this->addSql('DROP TABLE friend_request');
        $this->addSql('DROP TABLE interview');
        $this->addSql('DROP TABLE job_application');
        $this->addSql('DROP TABLE job_offre');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE pdf_template');
        $this->addSql('DROP TABLE `user`');
    }
}
