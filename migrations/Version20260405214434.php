<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260405214434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract (id INT AUTO_INCREMENT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, salary INT NOT NULL, salaire_net DOUBLE PRECISION DEFAULT NULL, status VARCHAR(50) NOT NULL, is_signed TINYINT(1) NOT NULL, signed_at DATETIME DEFAULT NULL, signature_base64 LONGTEXT DEFAULT NULL, google_event_id_start VARCHAR(255) DEFAULT NULL, google_event_id_end VARCHAR(255) DEFAULT NULL, google_event_id_trial VARCHAR(255) DEFAULT NULL, type_contrat_id INT DEFAULT NULL, candidate_id INT NOT NULL, recruiter_id INT DEFAULT NULL, job_offre_id INT NOT NULL, INDEX IDX_E98F2859520D03A (type_contrat_id), INDEX IDX_E98F285991BD8781 (candidate_id), INDEX IDX_E98F2859156BE243 (recruiter_id), INDEX IDX_E98F28592B8FF521 (job_offre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cover_letter (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE document_candidate (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE document_contract (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE forum_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_21BF94265E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE forum_comment (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, post_id INT NOT NULL, INDEX IDX_65B81F1D4B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE forum_like (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE forum_post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, active TINYINT(1) NOT NULL, image_path VARCHAR(255) DEFAULT NULL, category_id INT DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_996BCC5A12469DE2 (category_id), INDEX IDX_996BCC5AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE interview (id INT AUTO_INCREMENT NOT NULL, scheduled_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, meeting_link VARCHAR(255) DEFAULT NULL, application_id INT NOT NULL, INDEX IDX_CF1D3C343E030ACD (application_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE job_application (id INT AUTO_INCREMENT NOT NULL, application_status VARCHAR(50) NOT NULL, apply_date DATETIME NOT NULL, cover_letter LONGTEXT DEFAULT NULL, cv_path VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, job_offre_id INT NOT NULL, INDEX IDX_C737C688A76ED395 (user_id), INDEX IDX_C737C6882B8FF521 (job_offre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE job_offre (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, salary DOUBLE PRECISION DEFAULT NULL, publishedAt DATETIME DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, expires_at DATETIME DEFAULT NULL, employment_type VARCHAR(100) DEFAULT NULL, is_salary_negotiable TINYINT(1) NOT NULL, advantages LONGTEXT DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_AEDA3B1FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE type_contrat (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, role VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, firstName VARCHAR(255) DEFAULT NULL, lastName VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, two_factor_code VARCHAR(10) DEFAULT NULL, two_factor_expiry DATETIME DEFAULT NULL, reset_token VARCHAR(100) DEFAULT NULL, reset_token_expiry DATETIME DEFAULT NULL, discr VARCHAR(255) NOT NULL, companyname VARCHAR(255) DEFAULT NULL, departement VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859520D03A FOREIGN KEY (type_contrat_id) REFERENCES type_contrat (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F285991BD8781 FOREIGN KEY (candidate_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859156BE243 FOREIGN KEY (recruiter_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F28592B8FF521 FOREIGN KEY (job_offre_id) REFERENCES job_offre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE forum_comment ADD CONSTRAINT FK_65B81F1D4B89032C FOREIGN KEY (post_id) REFERENCES forum_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE forum_post ADD CONSTRAINT FK_996BCC5A12469DE2 FOREIGN KEY (category_id) REFERENCES forum_category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE forum_post ADD CONSTRAINT FK_996BCC5AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE interview ADD CONSTRAINT FK_CF1D3C343E030ACD FOREIGN KEY (application_id) REFERENCES job_application (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_application ADD CONSTRAINT FK_C737C688A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE job_application ADD CONSTRAINT FK_C737C6882B8FF521 FOREIGN KEY (job_offre_id) REFERENCES job_offre (id)');
        $this->addSql('ALTER TABLE job_offre ADD CONSTRAINT FK_AEDA3B1FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859520D03A');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F285991BD8781');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859156BE243');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F28592B8FF521');
        $this->addSql('ALTER TABLE forum_comment DROP FOREIGN KEY FK_65B81F1D4B89032C');
        $this->addSql('ALTER TABLE forum_post DROP FOREIGN KEY FK_996BCC5A12469DE2');
        $this->addSql('ALTER TABLE forum_post DROP FOREIGN KEY FK_996BCC5AA76ED395');
        $this->addSql('ALTER TABLE interview DROP FOREIGN KEY FK_CF1D3C343E030ACD');
        $this->addSql('ALTER TABLE job_application DROP FOREIGN KEY FK_C737C688A76ED395');
        $this->addSql('ALTER TABLE job_application DROP FOREIGN KEY FK_C737C6882B8FF521');
        $this->addSql('ALTER TABLE job_offre DROP FOREIGN KEY FK_AEDA3B1FA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE cover_letter');
        $this->addSql('DROP TABLE document_candidate');
        $this->addSql('DROP TABLE document_contract');
        $this->addSql('DROP TABLE forum_category');
        $this->addSql('DROP TABLE forum_comment');
        $this->addSql('DROP TABLE forum_like');
        $this->addSql('DROP TABLE forum_post');
        $this->addSql('DROP TABLE interview');
        $this->addSql('DROP TABLE job_application');
        $this->addSql('DROP TABLE job_offre');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE type_contrat');
        $this->addSql('DROP TABLE `user`');
    }
}
