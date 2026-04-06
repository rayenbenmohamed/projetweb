<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406122634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document_candidate (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE document_contract (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE type_contrat (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
//        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY fk_cv_user');
//        $this->addSql('DROP TABLE contract_type');
//        $this->addSql('DROP TABLE cv');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY fk_contract_job');
//        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY fk_contract_type');
        $this->addSql('DROP INDEX fk_contract_job ON contract');
//        $this->addSql('DROP INDEX fk_contract_type ON contract');
        $this->addSql('ALTER TABLE contract CHANGE status status VARCHAR(50) NOT NULL, CHANGE is_signed is_signed TINYINT(1) NOT NULL, CHANGE contract_type_id type_contrat_id INT DEFAULT NULL, CHANGE job_offer_id job_offre_id INT NOT NULL');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859520D03A FOREIGN KEY (type_contrat_id) REFERENCES type_contrat (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F285991BD8781 FOREIGN KEY (candidate_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859156BE243 FOREIGN KEY (recruiter_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F28592B8FF521 FOREIGN KEY (job_offre_id) REFERENCES job_offre (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_E98F2859520D03A ON contract (type_contrat_id)');
        $this->addSql('CREATE INDEX IDX_E98F2859156BE243 ON contract (recruiter_id)');
        $this->addSql('CREATE INDEX IDX_E98F28592B8FF521 ON contract (job_offre_id)');
        $this->addSql('ALTER TABLE contract RENAME INDEX fk_contract_candidate TO IDX_E98F285991BD8781');
        $this->addSql('ALTER TABLE cover_letter DROP FOREIGN KEY fk_cl_user');
        $this->addSql('DROP INDEX fk_cl_user ON cover_letter');
        $this->addSql('ALTER TABLE cover_letter DROP user_id, DROP company_name, DROP position, DROP recipient_name, DROP recipient_title, DROP company_address, DROP letter_content, DROP letter_file, DROP is_public, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE forum_category CHANGE name name VARCHAR(255) NOT NULL, CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_21BF94265E237E06 ON forum_category (name)');
        $this->addSql('ALTER TABLE forum_comment DROP FOREIGN KEY fk_comment_user');
        $this->addSql('DROP INDEX fk_comment_user ON forum_comment');
        $this->addSql('ALTER TABLE forum_comment DROP user_id, CHANGE content content LONGTEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE forum_comment RENAME INDEX fk_comment_post TO IDX_65B81F1D4B89032C');
        $this->addSql('ALTER TABLE forum_like DROP FOREIGN KEY fk_like_user');
        $this->addSql('ALTER TABLE forum_like DROP FOREIGN KEY fk_like_post');
        $this->addSql('DROP INDEX fk_like_user ON forum_like');
        $this->addSql('DROP INDEX IDX_6F82E0644B89032C ON forum_like');
        $this->addSql('ALTER TABLE forum_like ADD id INT AUTO_INCREMENT NOT NULL, DROP post_id, DROP user_id, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE forum_post DROP FOREIGN KEY fk_post_user');
        $this->addSql('ALTER TABLE forum_post CHANGE content content LONGTEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE status status VARCHAR(50) NOT NULL, CHANGE active active TINYINT(1) NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE image_path image_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE forum_post ADD CONSTRAINT FK_996BCC5AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE forum_post RENAME INDEX fk_post_category TO IDX_996BCC5A12469DE2');
        $this->addSql('ALTER TABLE forum_post RENAME INDEX fk_post_user TO IDX_996BCC5AA76ED395');
        $this->addSql('ALTER TABLE interview CHANGE status status VARCHAR(50) NOT NULL, CHANGE notes notes LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE interview RENAME INDEX application_id TO IDX_CF1D3C343E030ACD');
        $this->addSql('ALTER TABLE job_application DROP FOREIGN KEY job_application_ibfk_1');
        $this->addSql('ALTER TABLE job_application DROP FOREIGN KEY job_application_ibfk_2');
        $this->addSql('ALTER TABLE job_application CHANGE application_status application_status VARCHAR(50) NOT NULL, CHANGE apply_date apply_date DATETIME NOT NULL, CHANGE cover_letter cover_letter LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_application ADD CONSTRAINT FK_C737C688A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE job_application ADD CONSTRAINT FK_C737C6882B8FF521 FOREIGN KEY (job_offre_id) REFERENCES job_offre (id)');
        $this->addSql('ALTER TABLE job_application RENAME INDEX user_id TO IDX_C737C688A76ED395');
        $this->addSql('ALTER TABLE job_application RENAME INDEX job_offre_id TO IDX_C737C6882B8FF521');
        $this->addSql('ALTER TABLE job_offre DROP FOREIGN KEY fk_user_offre');
        $this->addSql('ALTER TABLE job_offre DROP active, DROP deleted_at, DROP archived_at, CHANGE title title VARCHAR(255) NOT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE location location VARCHAR(255) DEFAULT NULL, CHANGE salary salary DOUBLE PRECISION DEFAULT NULL, CHANGE publishedAt publishedAt DATETIME DEFAULT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE status status VARCHAR(20) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE employment_type employment_type VARCHAR(100) DEFAULT NULL, CHANGE is_salary_negotiable is_salary_negotiable TINYINT(1) NOT NULL, CHANGE advantages advantages LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_offre ADD CONSTRAINT FK_AEDA3B1FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE job_offre RENAME INDEX fk_user_offre TO IDX_AEDA3B1FA76ED395');
        $this->addSql('ALTER TABLE notification CHANGE message message LONGTEXT NOT NULL, CHANGE is_read is_read TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification RENAME INDEX user_id TO IDX_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE user CHANGE firstName firstName VARCHAR(255) DEFAULT NULL, CHANGE lastName lastName VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(180) NOT NULL, CHANGE password password VARCHAR(255) NOT NULL, CHANGE discr discr VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE cv (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, full_name VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, email VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, phone VARCHAR(50) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, address VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, birth_date DATE DEFAULT NULL, title VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, summary TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, education TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, experience TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, skills TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, cv_file VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, is_public TINYINT(1) DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX fk_cv_user (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT fk_cv_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE document_candidate');
        $this->addSql('DROP TABLE document_contract');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE type_contrat');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859520D03A');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F285991BD8781');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859156BE243');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F28592B8FF521');
        $this->addSql('DROP INDEX IDX_E98F2859520D03A ON contract');
        $this->addSql('DROP INDEX IDX_E98F2859156BE243 ON contract');
        $this->addSql('DROP INDEX IDX_E98F28592B8FF521 ON contract');
        $this->addSql('ALTER TABLE contract CHANGE status status VARCHAR(50) DEFAULT \'En Attente\', CHANGE is_signed is_signed TINYINT(1) DEFAULT 0, CHANGE job_offre_id job_offer_id INT NOT NULL, CHANGE type_contrat_id contract_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT fk_contract_type FOREIGN KEY (contract_type_id) REFERENCES contract_type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT fk_contract_job FOREIGN KEY (job_offer_id) REFERENCES job_offre (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_contract_job ON contract (job_offer_id)');
        $this->addSql('CREATE INDEX fk_contract_type ON contract (contract_type_id)');
        $this->addSql('ALTER TABLE contract RENAME INDEX idx_e98f285991bd8781 TO fk_contract_candidate');
        $this->addSql('ALTER TABLE cover_letter ADD user_id INT NOT NULL, ADD company_name VARCHAR(255) DEFAULT NULL, ADD position VARCHAR(255) DEFAULT NULL, ADD recipient_name VARCHAR(255) DEFAULT NULL, ADD recipient_title VARCHAR(255) DEFAULT NULL, ADD company_address VARCHAR(255) DEFAULT NULL, ADD letter_content TEXT DEFAULT NULL, ADD letter_file VARCHAR(255) DEFAULT NULL, ADD is_public TINYINT(1) DEFAULT 0, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE cover_letter ADD CONSTRAINT fk_cl_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_cl_user ON cover_letter (user_id)');
        $this->addSql('DROP INDEX UNIQ_21BF94265E237E06 ON forum_category');
        $this->addSql('ALTER TABLE forum_category CHANGE name name VARCHAR(100) NOT NULL, CHANGE description description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE forum_comment ADD user_id INT DEFAULT NULL, CHANGE content content TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE forum_comment ADD CONSTRAINT fk_comment_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX fk_comment_user ON forum_comment (user_id)');
        $this->addSql('ALTER TABLE forum_comment RENAME INDEX idx_65b81f1d4b89032c TO fk_comment_post');
        $this->addSql('ALTER TABLE forum_like ADD post_id INT NOT NULL, ADD user_id INT NOT NULL, DROP id, DROP PRIMARY KEY, ADD PRIMARY KEY (post_id, user_id)');
        $this->addSql('ALTER TABLE forum_like ADD CONSTRAINT fk_like_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE forum_like ADD CONSTRAINT fk_like_post FOREIGN KEY (post_id) REFERENCES forum_post (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_like_user ON forum_like (user_id)');
        $this->addSql('CREATE INDEX IDX_6F82E0644B89032C ON forum_like (post_id)');
        $this->addSql('ALTER TABLE forum_post DROP FOREIGN KEY FK_996BCC5AA76ED395');
        $this->addSql('ALTER TABLE forum_post CHANGE content content TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE status status VARCHAR(50) DEFAULT \'actif\', CHANGE active active TINYINT(1) DEFAULT 1, CHANGE image_path image_path VARCHAR(500) DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE forum_post ADD CONSTRAINT fk_post_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE forum_post RENAME INDEX idx_996bcc5aa76ed395 TO fk_post_user');
        $this->addSql('ALTER TABLE forum_post RENAME INDEX idx_996bcc5a12469de2 TO fk_post_category');
        $this->addSql('ALTER TABLE interview CHANGE status status VARCHAR(50) DEFAULT \'Prévue\' NOT NULL, CHANGE notes notes TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE interview RENAME INDEX idx_cf1d3c343e030acd TO application_id');
        $this->addSql('ALTER TABLE job_application DROP FOREIGN KEY FK_C737C688A76ED395');
        $this->addSql('ALTER TABLE job_application DROP FOREIGN KEY FK_C737C6882B8FF521');
        $this->addSql('ALTER TABLE job_application CHANGE application_status application_status VARCHAR(50) DEFAULT \'Pending\', CHANGE apply_date apply_date DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE cover_letter cover_letter TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_application ADD CONSTRAINT job_application_ibfk_1 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_application ADD CONSTRAINT job_application_ibfk_2 FOREIGN KEY (job_offre_id) REFERENCES job_offre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_application RENAME INDEX idx_c737c688a76ed395 TO user_id');
        $this->addSql('ALTER TABLE job_application RENAME INDEX idx_c737c6882b8ff521 TO job_offre_id');
        $this->addSql('ALTER TABLE job_offre DROP FOREIGN KEY FK_AEDA3B1FA76ED395');
        $this->addSql('ALTER TABLE job_offre ADD active TINYINT(1) DEFAULT 1 NOT NULL, ADD deleted_at DATETIME DEFAULT NULL, ADD archived_at DATETIME DEFAULT NULL, CHANGE title title VARCHAR(50) NOT NULL, CHANGE description description VARCHAR(1000) NOT NULL, CHANGE location location VARCHAR(50) NOT NULL, CHANGE salary salary NUMERIC(5, 0) NOT NULL, CHANGE publishedAt publishedAt DATE NOT NULL, CHANGE status status VARCHAR(20) DEFAULT \'PUBLISHED\', CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE employment_type employment_type VARCHAR(50) DEFAULT NULL, CHANGE is_salary_negotiable is_salary_negotiable TINYINT(1) DEFAULT 0, CHANGE advantages advantages TEXT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_offre ADD CONSTRAINT fk_user_offre FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offre RENAME INDEX idx_aeda3b1fa76ed395 TO fk_user_offre');
        $this->addSql('ALTER TABLE notification CHANGE message message TEXT NOT NULL, CHANGE is_read is_read TINYINT(1) DEFAULT 0, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE notification RENAME INDEX idx_bf5476caa76ed395 TO user_id');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON `user`');
        $this->addSql('ALTER TABLE `user` CHANGE email email VARCHAR(50) NOT NULL, CHANGE password password VARCHAR(50) NOT NULL, CHANGE firstName firstName VARCHAR(100) NOT NULL, CHANGE lastName lastName VARCHAR(100) NOT NULL, CHANGE discr discr VARCHAR(255) DEFAULT \'user\' NOT NULL');
    }
}
