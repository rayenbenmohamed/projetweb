-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Dim 05 Avril 2026 à 16:07
-- Version du serveur :  5.6.17
-- Version de PHP :  5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `cc`
--

-- --------------------------------------------------------

--
-- Structure de la table `contract`
--

CREATE TABLE IF NOT EXISTS `contract` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `salary` int(11) NOT NULL,
  `salaire_net` double DEFAULT NULL,
  `status` varchar(50) DEFAULT 'En Attente',
  `is_signed` tinyint(1) DEFAULT '0',
  `signed_at` timestamp NULL DEFAULT NULL,
  `recruiter_id` int(11) DEFAULT NULL,
  `candidate_id` int(11) NOT NULL,
  `job_offer_id` int(11) NOT NULL,
  `contract_type_id` int(11) DEFAULT NULL,
  `signature_base64` longtext,
  `google_event_id_start` varchar(255) DEFAULT NULL,
  `google_event_id_end` varchar(255) DEFAULT NULL,
  `google_event_id_trial` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_contract_candidate` (`candidate_id`),
  KEY `fk_contract_job` (`job_offer_id`),
  KEY `fk_contract_type` (`contract_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `contract`
--

INSERT INTO `contract` (`id`, `start_date`, `end_date`, `salary`, `salaire_net`, `status`, `is_signed`, `signed_at`, `recruiter_id`, `candidate_id`, `job_offer_id`, `contract_type_id`, `signature_base64`, `google_event_id_start`, `google_event_id_end`, `google_event_id_trial`) VALUES
(1, '2026-03-01', '2027-03-07', 50000, 38500, 'Actif', 0, NULL, 1, 6, 9, 1, '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', NULL, NULL, NULL),
(2, '2026-03-08', '2027-03-14', 3000, 2310, 'Actif', 1, NULL, 1, 6, 9, 2, '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', NULL, NULL, NULL),
(3, '2026-03-01', '2026-05-28', 9999, 7699.2300000000005, 'Actif', 0, NULL, 1, 6, 9, 2, 'iVBORw0KGgoAAAANSUhEUgAAAyAAAAB4CAYAAAAKRZZvAAACkElEQVR4Xu3XoQEAMAyAsP7/dOvnh0okHzALAAAQmTcAAAD8YkAAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIGBAAAyBgQAAAgY0AAAICMAQEAADIHVQp7i8/TFAQAAAAASUVORK5CYII=', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `contract_type`
--

CREATE TABLE IF NOT EXISTS `contract_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=5 ;

--
-- Contenu de la table `contract_type`
--

INSERT INTO `contract_type` (`id`, `name`, `description`) VALUES
(1, 'CDI', 'Contrat à durée indéterminée'),
(2, 'CDD', 'Contrat à durée déterminée'),
(3, 'SIVP', 'Contrat de stage d''initiation à la vie professionnelle'),
(4, 'Karama', 'Contrat de dignité');

-- --------------------------------------------------------

--
-- Structure de la table `cover_letter`
--

CREATE TABLE IF NOT EXISTS `cover_letter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `recipient_title` varchar(255) DEFAULT NULL,
  `company_address` varchar(255) DEFAULT NULL,
  `letter_content` text,
  `letter_file` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_cl_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `cv`
--

CREATE TABLE IF NOT EXISTS `cv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `summary` text,
  `education` text,
  `experience` text,
  `skills` text,
  `cv_file` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_cv_user` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `cv`
--

INSERT INTO `cv` (`id`, `user_id`, `full_name`, `email`, `phone`, `address`, `birth_date`, `title`, `summary`, `education`, `experience`, `skills`, `cv_file`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 6, 'rayen med', 'rayenbenmohamed169@gmail.com', '3454353478', 'Soliman, Gouvernorat Nabeul, Tunisie', '2026-03-04', NULL, 'Experienced Engineer with a strong specialization in DevSecOps within cloud-native environments. Drives the seamless integration of security across the entire software development lifecycle, building robust and compliant solutions. Proficient in automating security workflows and designing scalable, resilient cloud infrastructure to enhance organizational security posture and operational efficiency.', 'Hajvery University Lahore for Women (Pakistan)', 'google - dev (2020)', 'Java, Python', '', 1, '2026-03-04 03:31:58', '2026-03-04 03:31:58'),
(2, 7, 'oussema belhajsghair', 'oussemabhs5@gmail.com', '55489661', 'soliman', '2026-03-05', NULL, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '', 'google - dev (2020)', 'Java, Python', '', 1, '2026-03-05 20:18:48', '2026-03-05 20:18:48');

-- --------------------------------------------------------

--
-- Structure de la table `forum_category`
--

CREATE TABLE IF NOT EXISTS `forum_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `forum_comment`
--

CREATE TABLE IF NOT EXISTS `forum_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_comment_post` (`post_id`),
  KEY `fk_comment_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `forum_like`
--

CREATE TABLE IF NOT EXISTS `forum_like` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`post_id`,`user_id`),
  KEY `fk_like_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `forum_post`
--

CREATE TABLE IF NOT EXISTS `forum_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(50) DEFAULT 'actif',
  `active` tinyint(1) DEFAULT '1',
  `category_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_post_category` (`category_id`),
  KEY `fk_post_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `interview`
--

CREATE TABLE IF NOT EXISTS `interview` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Prévue',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `meeting_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_id` (`application_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=31 ;

--
-- Contenu de la table `interview`
--

INSERT INTO `interview` (`id`, `application_id`, `scheduled_at`, `status`, `notes`, `meeting_link`) VALUES
(3, 11, '2026-02-28 00:00:00', 'Confirmée', 'linkteam', NULL),
(4, 12, '2026-02-06 10:00:00', 'Confirmée', 'googlemeet.com', NULL),
(7, 11, '2026-03-03 10:00:00', 'Prévue', 'Entretien technique standard.', 'https://meet.google.com/new'),
(8, 11, '2026-02-25 10:00:00', 'Confirmée', 'Entretien technique standard.', 'https://meet.google.com/new'),
(14, 17, '2026-03-04 10:00:00', 'Confirmée', 'Entretien technique standard.', 'https://meet.google.com/new'),
(16, 21, '2026-03-12 10:00:00', 'Prévue', 'Entretien technique standard.', 'https://meet.google.com/new'),
(17, 12, '2026-03-01 11:00:00', 'Confirmée', 'Entretien technique standard.', 'https://meet.google.com/new'),
(19, 19, '2026-03-01 09:00:00', 'Confirmée', 'Entretien technique standard.', 'https://meet.google.com/new'),
(20, 12, '2026-03-01 09:00:00', 'Prévue', 'Entretien technique standard.', 'https://meet.google.com/new'),
(21, 12, '2026-03-01 09:00:00', 'Prévue', 'Entretien technique standard.', 'https://meet.google.com/new'),
(22, 20, '2026-03-01 10:00:00', 'Confirmée', 'Entretien technique standard.', 'https://meet.google.com/new'),
(23, 20, '2026-03-01 12:00:00', 'Confirmée', 'Entretien technique standard.', 'https://meet.google.com/new'),
(24, 17, '2026-03-02 12:00:00', 'Confirmée', 'Entretien technique standard.', 'https://meet.google.com/new'),
(25, 20, '2026-03-02 10:00:00', 'Prévue', 'Entretien technique standard.', 'https://meet.google.com/new'),
(26, 17, '2026-03-07 10:00:00', 'Confirmée', 'Entretien technique standard.', 'https://meet.google.com/new'),
(27, 17, '2026-03-05 10:00:00', 'Confirmée', 'Entretien technique standard.', 'https://meet.google.com/new'),
(28, 20, '2026-03-16 10:00:00', 'Prévue', 'Entretien technique standard.', 'https://meet.google.com/new'),
(29, 12, '2026-03-08 10:00:00', 'Prévue', 'Entretien technique standard.', 'https://meet.google.com/new'),
(30, 23, '2026-03-04 16:00:00', 'Prévue', 'Entretien technique standard.', 'https://meet.google.com/new');

-- --------------------------------------------------------

--
-- Structure de la table `job_application`
--

CREATE TABLE IF NOT EXISTS `job_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `job_offre_id` int(11) NOT NULL,
  `application_status` varchar(50) DEFAULT 'Pending',
  `apply_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `cover_letter` text,
  `cv_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `job_offre_id` (`job_offre_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=24 ;

--
-- Contenu de la table `job_application`
--

INSERT INTO `job_application` (`id`, `user_id`, `job_offre_id`, `application_status`, `apply_date`, `cover_letter`, `cv_path`) VALUES
(9, 1, 1, 'Pending', '2026-02-24 00:00:00', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1771895817/letters/hmblgz45turrevttflyk.pdf', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1771895816/cvs/mas24wf3myfjqu1kavd3.pdf'),
(10, 1, 1, 'Pending', '2026-02-24 00:00:00', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1771896960/letters/swztmhiqd4bcnxpoafyc.pdf', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1771896959/cvs/ilfbrmjimb9afi1robqi.pdf'),
(11, 1, 9, 'Rejected', '2026-02-24 00:00:00', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1771897111/letters/kdkxzj7lo4xxmfzbvgja.pdf', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1771897110/cvs/mkqm2emddyapxrpjhuqd.pdf'),
(12, 1, 8, 'Pending', '2026-02-24 00:00:00', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1771898419/letters/vn1hnl2s6efly3pvcfpb.pdf', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1771898417/cvs/zec0by2rmzuvu7qn71td.pdf'),
(17, 6, 8, 'Accepted', '2026-02-28 00:00:00', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1772310436/letters/faqpyyi7x8rksvwvxfeh.pdf', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1772310434/cvs/affahojsjucevx4xwhpn.pdf'),
(19, 6, 10, 'Accepted', '2026-02-28 00:00:00', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1772312056/letters/jxdogmv82dx3udbwero1.pdf', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1772312055/cvs/o85freppfic4c4ft0izd.pdf'),
(20, 6, 9, 'Accepted', '2026-02-28 00:00:00', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1772312829/letters/ejxpptbb7gbjnaetdgwv.pdf', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1772312828/cvs/yj597az0hqy4yfici7vp.pdf'),
(21, 6, 15, 'Accepted', '2026-02-28 00:00:00', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1772318358/letters/pkfs4fkr2vkbitfgzrah.pdf', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1772318355/cvs/cjlat0vekenfnpp0naqo.pdf'),
(22, 7, 4, 'Pending', '2026-03-04 00:00:00', 'https://res.cloudinary.com/dbxfuedn2/raw/upload/v1772596614/letters/sbmf2nhxniktci9vlt2o.docx', 'https://res.cloudinary.com/dbxfuedn2/raw/upload/v1772596612/cvs/ota4iq2dnvjnxk0rtbv3.docx'),
(23, 7, 17, 'Pending', '2026-03-04 00:00:00', 'https://res.cloudinary.com/dbxfuedn2/raw/upload/v1772596651/letters/vt9wblxvmgw9yv5jwta7.docx', 'https://res.cloudinary.com/dbxfuedn2/raw/upload/v1772596650/cvs/hz1c5st4gfmlwgm4lyfy.docx');

-- --------------------------------------------------------

--
-- Structure de la table `job_offre`
--

CREATE TABLE IF NOT EXISTS `job_offre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `location` varchar(50) NOT NULL,
  `salary` decimal(5,0) NOT NULL,
  `publishedAt` date NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'PUBLISHED',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  `employment_type` varchar(50) DEFAULT NULL,
  `is_salary_negotiable` tinyint(1) DEFAULT '0',
  `advantages` text,
  PRIMARY KEY (`id`),
  KEY `fk_user_offre` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=19 ;

--
-- Contenu de la table `job_offre`
--

INSERT INTO `job_offre` (`id`, `title`, `description`, `location`, `salary`, `publishedAt`, `active`, `user_id`, `status`, `created_at`, `updated_at`, `expires_at`, `deleted_at`, `archived_at`, `employment_type`, `is_salary_negotiable`, `advantages`) VALUES
(1, 'Senior Java Developer', 'Poste senior avec expérience Spring Boot', 'Ariana soghra', '4000', '2026-02-12', 1, NULL, 'PUBLISHED', '2026-02-18 12:20:09', '2026-03-03 20:42:16', NULL, NULL, NULL, NULL, 0, 'hghjgh'),
(4, 'offre developpeur javafx', 'passione par le developpemtn moderne', 'rades', '300', '2026-02-14', 1, NULL, 'PUBLISHED', '2026-02-18 12:20:09', '2026-02-18 12:20:09', NULL, NULL, NULL, NULL, 0, NULL),
(5, 'post ingeunieur informatique', 'specialise de power bi ', 'ben arous', '1400', '2026-02-14', 1, NULL, 'PUBLISHED', '2026-02-18 12:20:09', '2026-02-18 12:20:09', NULL, NULL, NULL, NULL, 0, NULL),
(6, 'fdhjfdhjkfdjk', 'jnnsdjnds', 'ndsjkndfj', '20', '2026-02-14', 1, NULL, 'PUBLISHED', '2026-02-18 12:20:09', '2026-02-18 12:20:09', NULL, NULL, NULL, NULL, 0, NULL),
(7, 'Développeur Java', 'Développement d''applications JavaFX et Spring Boot', 'Tunis', '2500', '2026-02-14', 1, 3, 'PUBLISHED', '2026-02-18 12:20:09', '2026-02-18 12:20:09', NULL, NULL, NULL, NULL, 0, NULL),
(8, 'Analyste Data', 'Analyse et traitement de données', 'Sfax', '2200', '2026-02-14', 1, 3, 'PUBLISHED', '2026-02-18 12:20:09', '2026-02-18 12:20:09', NULL, NULL, NULL, NULL, 0, NULL),
(9, 'UI/UX Designer', 'Conception d''interfaces et expérience utilisateur', 'Sousse', '2000', '2026-02-14', 1, 3, 'PUBLISHED', '2026-02-18 12:20:09', '2026-02-18 12:20:09', NULL, NULL, NULL, NULL, 0, NULL),
(10, 'DevOps Engineer', 'Mise en place de CI/CD et gestion des serveurs', 'Tunis', '2800', '2026-02-14', 1, 3, 'PUBLISHED', '2026-02-18 12:20:09', '2026-02-18 12:20:09', NULL, NULL, NULL, NULL, 0, NULL),
(11, 'fdjkhsdjkfhhjsdf', 'bsdhfhdsbfhsvdfgh', 'TUNIS', '4000', '2026-02-16', 1, 1, 'PUBLISHED', '2026-02-18 12:20:09', '2026-02-18 12:20:09', NULL, NULL, NULL, NULL, 0, NULL),
(12, 'Développeur Java Fullstack', 'Mission de 6 mois pour un projet e-commerce.', 'Tunis', '2500', '2026-02-28', 1, 3, 'PUBLISHED', '2026-02-28 22:24:20', '2026-02-28 22:24:20', NULL, NULL, NULL, NULL, 0, NULL),
(13, 'Expert Sécurité Cloud', 'Audit et mise en place de solutions Azure.', 'Sousse', '4500', '2026-02-28', 1, 3, 'PUBLISHED', '2026-02-28 22:24:20', '2026-02-28 22:24:20', NULL, NULL, NULL, NULL, 0, NULL),
(14, 'Designer UI/UX', 'Refonte d une application mobile de santé.', 'Ariana', '1800', '2026-02-28', 1, 3, 'PUBLISHED', '2026-02-28 22:24:20', '2026-02-28 22:24:20', NULL, NULL, NULL, NULL, 0, NULL),
(15, 'Chef de Projet Agile', 'Accompagnement d une équipe de 10 personnes.', 'Bizerte', '3500', '2026-02-28', 1, 3, 'PUBLISHED', '2026-02-28 22:24:20', '2026-02-28 22:24:20', NULL, NULL, NULL, NULL, 0, NULL),
(16, 'Data Scientist Senior', 'Analyse de données massives pour le secteur bancaire.', 'Tunis', '5000', '2026-02-28', 1, 3, 'PUBLISHED', '2026-02-28 22:24:20', '2026-02-28 22:24:20', NULL, NULL, NULL, NULL, 0, NULL),
(17, 'test', 'testettttttttt', 'soliman', '5000', '2026-03-04', 1, 7, 'PUBLISHED', '2026-03-04 04:48:56', '2026-03-04 04:49:16', NULL, NULL, NULL, 'Temps partiel', 0, ''),
(18, 'validation', 'aaaaaaaaaaaaaaaaaaa', 'Ariana, Tunisie', '5000', '2026-03-04', 1, 6, 'PUBLISHED', '2026-03-04 10:36:20', '2026-03-04 10:36:20', '2026-03-21 00:00:00', NULL, NULL, 'Stage', 1, '');

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE IF NOT EXISTS `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=15 ;

--
-- Contenu de la table `notification`
--

INSERT INTO `notification` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 3, 'Nouvelle candidature pour l''offre ''UI/UX Designer'' par null null', 1, '2026-02-28 20:44:33'),
(2, 3, 'Nouvelle candidature pour l''offre ''DevOps Engineer'' par null null', 1, '2026-02-28 20:54:16'),
(3, 3, 'Nouvelle candidature pour l''offre ''UI/UX Designer'' par null null', 1, '2026-02-28 21:07:10'),
(4, 3, 'Nouvelle candidature pour l''offre ''Chef de Projet Agile'' par null null', 1, '2026-02-28 22:39:18'),
(5, 3, 'Le candidat Unknown Unknown a confirmé sa présence pour l''entretien: Analyste Data', 1, '2026-03-02 20:23:20'),
(6, 3, 'Le candidat rayen med a confirmé sa présence pour l''entretien: Analyste Data', 1, '2026-03-02 20:29:24'),
(7, 3, 'Le candidat rayen med a confirmé sa présence pour l''entretien: UI/UX Designer', 1, '2026-03-02 20:40:22'),
(8, 3, 'Le candidat rayen med a confirmé sa présence pour l''entretien: UI/UX Designer', 1, '2026-03-02 20:40:31'),
(9, 3, 'Le candidat rayen med a confirmé sa présence pour l''entretien: Analyste Data', 1, '2026-03-03 04:50:50'),
(10, 3, 'Le candidat rayen med a confirmé sa présence pour l''entretien: Analyste Data', 1, '2026-03-03 11:36:00'),
(11, 3, 'Le candidat rayen med a confirmé sa présence pour l''entretien: DevOps Engineer', 1, '2026-03-03 19:27:54'),
(13, 7, 'Nouvelle candidature pour l''offre ''test'' par oussema belhajsghair', 1, '2026-03-04 03:57:32'),
(14, 3, 'Le candidat rayen med a confirmé sa présence pour l''entretien: Analyste Data', 0, '2026-03-31 15:39:01');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL,
  `companyname` varchar(255) DEFAULT NULL,
  `Departement` varchar(255) DEFAULT NULL,
  `two_factor_code` varchar(10) DEFAULT NULL,
  `two_factor_expiry` timestamp NULL DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=9 ;

--
-- Contenu de la table `user`
--

INSERT INTO `user` (`id`, `firstName`, `lastName`, `email`, `phone`, `password`, `role`, `companyname`, `Departement`, `two_factor_code`, `two_factor_expiry`, `reset_token`, `reset_token_expiry`) VALUES
(1, 'Unknown', 'Unknown', 'candidat@candidat.com', NULL, 'candidat', 'Candidat', NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Unknown', 'Unknown', 'rayen@123.com', NULL, '1256', 'Recruteur', NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Unknown', 'Unknown', 'hachem', NULL, 'hachem@gmailCOM', 'Recruteur', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Unknown', 'Unknown', 'rayenlam', NULL, '123456', 'Candidat', NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'oussema', 'bhs', 'oussemabelhajsghair@gmail.com', '88833663', 'oussema', 'Recruteur', NULL, NULL, '626239', '2026-03-31 15:35:05', NULL, NULL),
(6, 'rayen', 'med', 'rayenbenmohamed169@gmail.com', '3454353478', 'rayen', 'Candidat', NULL, NULL, '688471', '2026-03-31 15:40:09', NULL, NULL),
(7, 'oussema', 'belhajsghair', 'oussemabhs5@gmail.com', '55489661', 'oussema', 'Recruteur', NULL, NULL, '571894', '2026-03-05 20:23:04', NULL, NULL),
(8, 'hech', 'hech', 'oussemabhs5@gmail.com', '12345678', 'hechhech', 'Candidat', NULL, NULL, '474945', '2026-03-04 04:52:19', NULL, NULL);

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `contract`
--
ALTER TABLE `contract`
  ADD CONSTRAINT `fk_contract_job` FOREIGN KEY (`job_offer_id`) REFERENCES `job_offre` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_contract_type` FOREIGN KEY (`contract_type_id`) REFERENCES `contract_type` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `cover_letter`
--
ALTER TABLE `cover_letter`
  ADD CONSTRAINT `fk_cl_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cv`
--
ALTER TABLE `cv`
  ADD CONSTRAINT `fk_cv_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `forum_comment`
--
ALTER TABLE `forum_comment`
  ADD CONSTRAINT `fk_comment_post` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `forum_like`
--
ALTER TABLE `forum_like`
  ADD CONSTRAINT `fk_like_post` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_like_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `forum_post`
--
ALTER TABLE `forum_post`
  ADD CONSTRAINT `fk_post_category` FOREIGN KEY (`category_id`) REFERENCES `forum_category` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_post_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `interview`
--
ALTER TABLE `interview`
  ADD CONSTRAINT `interview_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `job_application` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `job_application`
--
ALTER TABLE `job_application`
  ADD CONSTRAINT `job_application_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_application_ibfk_2` FOREIGN KEY (`job_offre_id`) REFERENCES `job_offre` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `job_offre`
--
ALTER TABLE `job_offre`
  ADD CONSTRAINT `fk_user_offre` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
