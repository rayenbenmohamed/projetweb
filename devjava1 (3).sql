-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 04 mai 2026 à 21:26
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `devjava1`
--

-- --------------------------------------------------------

--
-- Structure de la table `avantage`
--

CREATE TABLE `avantage` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `avantage`
--

INSERT INTO `avantage` (`id`, `name`, `icon`) VALUES
(1, 'Télétravail', 'ti-home'),
(2, 'Mutuelle', 'ti-heart'),
(3, 'Tickets Resto', 'ti-tools-kitchen-2'),
(4, 'Pass Navigo 50%', 'ti-bus'),
(5, 'Salle de Sport', 'ti-stretching'),
(6, 'Snacks & Coffee', 'ti-coffee');

-- --------------------------------------------------------

--
-- Structure de la table `calendar_event`
--

CREATE TABLE `calendar_event` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` longtext DEFAULT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contract`
--

CREATE TABLE `contract` (
  `id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `salary` int(11) NOT NULL,
  `salaire_net` double DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `is_signed` tinyint(1) NOT NULL,
  `signed_at` datetime DEFAULT NULL,
  `signature_base64` longtext DEFAULT NULL,
  `google_event_id_start` varchar(255) DEFAULT NULL,
  `google_event_id_end` varchar(255) DEFAULT NULL,
  `google_event_id_trial` varchar(255) DEFAULT NULL,
  `contract_type_id` int(11) DEFAULT NULL,
  `candidate_id` int(11) NOT NULL,
  `recruiter_id` int(11) DEFAULT NULL,
  `job_offer_id` int(11) NOT NULL,
  `content` longtext DEFAULT NULL,
  `pdf_template_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contract`
--

INSERT INTO `contract` (`id`, `start_date`, `end_date`, `salary`, `salaire_net`, `status`, `is_signed`, `signed_at`, `signature_base64`, `google_event_id_start`, `google_event_id_end`, `google_event_id_trial`, `contract_type_id`, `candidate_id`, `recruiter_id`, `job_offer_id`, `content`, `pdf_template_id`) VALUES
(1, '2026-05-01', NULL, 48000, 36960, 'En Attente', 0, NULL, NULL, NULL, NULL, NULL, NULL, 2, 3, 1, NULL, NULL),
(2, '2026-06-01', NULL, 55000, 42350, 'Signé', 0, NULL, NULL, NULL, NULL, NULL, NULL, 2, 3, 4, NULL, NULL),
(3, '2026-04-25', '2026-05-10', 500, 410, 'En Attente', 1, '2026-04-07 14:35:30', 'data:image/png;base64,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', NULL, NULL, NULL, NULL, 6, NULL, 3, 'hjgghjgyhjghjvghgvbnhbhjb', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `contract_type`
--

CREATE TABLE `contract_type` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contract_type`
--

INSERT INTO `contract_type` (`id`, `name`, `description`) VALUES
(1, 'cdi', 'efghjklkjhgf');

-- --------------------------------------------------------

--
-- Structure de la table `cover_letter`
--

CREATE TABLE `cover_letter` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cv`
--

CREATE TABLE `cv` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `summary` longtext DEFAULT NULL,
  `education` longtext DEFAULT NULL,
  `experience` longtext DEFAULT NULL,
  `skills` longtext DEFAULT NULL,
  `cv_file` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260405174059', NULL, NULL),
('DoctrineMigrations\\Version20260405214434', NULL, NULL),
('DoctrineMigrations\\Version20260406180000', NULL, NULL),
('DoctrineMigrations\\Version20260407130000', NULL, NULL),
('DoctrineMigrations\\Version20260408120000', NULL, NULL),
('DoctrineMigrations\\Version20260411220000', '2026-04-21 12:31:57', 111),
('DoctrineMigrations\\Version20260413220000', NULL, NULL),
('DoctrineMigrations\\Version20260413232123', NULL, NULL),
('DoctrineMigrations\\Version20260416140000', '2026-04-21 12:31:57', 1),
('DoctrineMigrations\\Version20260418120000', '2026-05-04 19:52:30', 171),
('DoctrineMigrations\\Version20260418203000', '2026-05-04 19:52:30', 13),
('DoctrineMigrations\\Version20260419202104', NULL, NULL),
('DoctrineMigrations\\Version20260419202113', NULL, NULL),
('DoctrineMigrations\\Version20260420110000', '2026-05-04 19:52:30', 7),
('DoctrineMigrations\\Version20260420113000', '2026-05-04 19:52:30', 66),
('DoctrineMigrations\\Version20260420122000', '2026-05-04 19:52:30', 8),
('DoctrineMigrations\\Version20260420124000', '2026-05-04 19:52:30', 7),
('DoctrineMigrations\\Version20260427193217', '2026-04-27 22:35:15', 245);

-- --------------------------------------------------------

--
-- Structure de la table `document_candidate`
--

CREATE TABLE `document_candidate` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `document_contract`
--

CREATE TABLE `document_contract` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--

CREATE TABLE `entreprise` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `logo_public_id` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `sector` varchar(255) DEFAULT NULL,
  `size` varchar(100) DEFAULT NULL,
  `founded_at` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `social_linkedin` varchar(255) DEFAULT NULL,
  `slogan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `entreprise`
--

INSERT INTO `entreprise` (`id`, `name`, `description`, `logo`, `logo_public_id`, `website`, `address`, `user_id`, `sector`, `size`, `founded_at`, `phone`, `contact_email`, `social_linkedin`, `slogan`) VALUES
(2, 'Hachem Ahmad', 'Chez Hachem Ahmad, nous sommes fiers de notre histoire qui remonte à 2010, avec une équipe de 11 à 50 employés dédiés et passionnés, basée à Sfax. Notre entreprise opère dans le secteur de la santé et du médical, domaine dans lequel nous nous sommes forgé une réputation de qualité et de fiabilité. Notre slogan, \"warrioros\", reflète notre esprit de combat et notre détermination à offrir les meilleurs services et solutions à nos patients et à nos partenaires.\r\n\r\nAu fil des ans, nous avons développé une expertise approfondie dans le domaine de la santé, nous permettant de comprendre les besoins spécifiques de nos clients et de répondre à leurs attentes de manière efficace. Notre équipe est composée de professionnels de la santé hautement qualifiés et expérimentés, qui partagent une vision commune : offrir des soins de santé de haute qualité, personnalisés et accessibles à tous. Nous sommes convaincus que notre approche centrée sur le patient, combinée à notre expertise médicale, nous permet de faire une réelle différence dans la vie des gens.\r\n\r\nEn rejoignant notre équipe, vous deviendrez partie prenante d\'une communauté de professionnels dédiés et passionnés, qui partagent une vision commune de l\'excellence en matière de soins de santé. Nous offrons un environnement de travail stimulant et inclusif, où chaque employé est valorisé et encouragé à atteindre son plein potentiel. Si vous êtes prêt à relever de nouveaux défis et à contribuer à améliorer la santé et le bien-être des gens, nous vous invitons à nous rejoindre et à devenir un \"warrioros\" chez Hachem Ahmad.', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776701763/job_offres/logos/r79ii463z7bba2ohqx4m.png', 'job_offres/logos/r79ii463z7bba2ohqx4m', 'https://travel.com', 'sfax', 10, 'Santé / Médical', '11-50 employés', 2010, '+21625436090', 'hachemahmad830@gmail.com', 'https://linkedin.com', 'warrioros');

-- --------------------------------------------------------

--
-- Structure de la table `forum_category`
--

CREATE TABLE `forum_category` (
  `id` int(11) NOT NULL,
  `name` varchar(180) NOT NULL,
  `description` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `forum_comment`
--

CREATE TABLE `forum_comment` (
  `id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `forum_like`
--

CREATE TABLE `forum_like` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `forum_post`
--

CREATE TABLE `forum_post` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `status` varchar(50) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `friend_message`
--

CREATE TABLE `friend_message` (
  `id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `read_at` datetime DEFAULT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `friend_message`
--

INSERT INTO `friend_message` (`id`, `content`, `created_at`, `read_at`, `sender_id`, `recipient_id`) VALUES
(1, 'ccc', '2026-04-07 17:34:40', '2026-04-07 17:35:52', 5, 7),
(2, 'bonjour', '2026-04-07 17:36:01', '2026-04-07 17:36:08', 7, 5),
(3, 'bonjour', '2026-04-21 14:52:58', '2026-04-21 14:53:17', 10, 11),
(4, 'cv?', '2026-04-21 14:53:24', '2026-04-21 14:54:02', 11, 10);

-- --------------------------------------------------------

--
-- Structure de la table `friend_request`
--

CREATE TABLE `friend_request` (
  `id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `friend_request`
--

INSERT INTO `friend_request` (`id`, `status`, `created_at`, `sender_id`, `receiver_id`) VALUES
(1, 'pending', '2026-04-06 19:22:13', 4, 3),
(3, 'pending', '2026-04-07 17:31:30', 7, 2),
(4, 'accepted', '2026-04-07 17:32:34', 7, 5),
(5, 'accepted', '2026-04-21 14:47:40', 11, 10);

-- --------------------------------------------------------

--
-- Structure de la table `interview`
--

CREATE TABLE `interview` (
  `id` int(11) NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` varchar(50) NOT NULL,
  `notes` longtext DEFAULT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  `application_id` int(11) NOT NULL,
  `technical_rating` int(11) DEFAULT NULL,
  `communication_rating` int(11) DEFAULT NULL,
  `motivation_rating` int(11) DEFAULT NULL,
  `final_verdict` longtext DEFAULT NULL,
  `outcome` varchar(20) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `interview`
--

INSERT INTO `interview` (`id`, `scheduled_at`, `status`, `notes`, `meeting_link`, `application_id`, `technical_rating`, `communication_rating`, `motivation_rating`, `final_verdict`, `outcome`, `completed_at`) VALUES
(1, '2026-04-12 14:55:00', 'Confirmée', '33dffgfdgf', 'fgfdgfdfdgffd', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(4, '2026-04-10 16:56:00', 'Prévue', 'aaaaaaaaaaaaaaaaaaa', 'fgfdgfdfdgffd', 8, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `job_application`
--

CREATE TABLE `job_application` (
  `id` int(11) NOT NULL,
  `application_status` varchar(50) NOT NULL,
  `apply_date` datetime NOT NULL,
  `cover_letter` longtext DEFAULT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `job_offre_id` int(11) NOT NULL,
  `ai_score` int(11) DEFAULT NULL,
  `ai_analysis` longtext DEFAULT NULL,
  `ai_analyzed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `job_application`
--

INSERT INTO `job_application` (`id`, `application_status`, `apply_date`, `cover_letter`, `cv_path`, `user_id`, `job_offre_id`, `ai_score`, `ai_analysis`, `ai_analyzed_at`) VALUES
(1, 'ACCEPTED', '2026-04-06 01:27:03', 'Ceci est une candidature de test APRES correction de la configuration Cloudinary.', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1775431625/syfonu/cvs/mivme7cyi8acmv6emjn3.pdf', 1, 1, 80, '{\"score\":80,\"summary\":\"Le candidat a une bonne compréhension des méthodes numériques pour résoudre les systèmes d\'équations linéaires, notamment la méthode de Gauss-Seidel et la décomposition LU. Il a également montré une bonne capacité à appliquer ces méthodes à des problèmes concrets.\",\"pros\":[\"Bonne compréhension des méthodes numériques pour résoudre les systèmes d\'équations linéaires\",\"Capacité à appliquer ces méthodes à des problèmes concrets\",\"Présence d\'exemples détaillés et de calculs précis dans le CV\"],\"cons\":[\"Manque d\'expérience pratique dans le domaine de l\'analyse numérique\",\"Pas d\'informations sur les compétences en programmation ou en utilisation d\'outils de calcul numérique\"],\"recommendation\":\"Entretien technique recommandé pour évaluer les compétences pratiques du candidat en analyse numérique et en résolution de systèmes d\'équations linéaires\"}', '2026-04-27 23:05:47'),
(2, 'ACCEPTED', '2026-04-06 14:20:32', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1775478044/syfonu/cvs/rcz28q5fg6ahx0unjzgr.pdf', 1, 1, 80, '{\"score\":80,\"summary\":\"Le candidat a une bonne compréhension des méthodes numériques pour résoudre les systèmes d\'équations linéaires, notamment la méthode de Gauss-Seidel et la décomposition LU. Il a également montré des compétences en analyse numérique et en résolution de problèmes.\",\"pros\":[\"Bonne compréhension des méthodes numériques pour résoudre les systèmes d\'équations linéaires\",\"Compétences en analyse numérique et en résolution de problèmes\",\"Capacité à appliquer les méthodes numériques à des problèmes concrets\"],\"cons\":[\"Manque d\'expérience pratique dans l\'application des méthodes numériques à des problèmes réels\",\"Pas de mention de la programmation ou de l\'utilisation de logiciels de calcul numérique\"],\"recommendation\":\"Test technique recommandé pour évaluer les compétences pratiques du candidat en résolution de problèmes numériques\"}', '2026-04-27 23:05:49'),
(5, 'ACCEPTED', '2026-04-07 00:48:37', 'FHDJGFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1775515719/syfonu/cvs/asgnv83rvtjanbqql15v.pdf', 5, 4, 15, '{\"score\":15,\"score_breakdown\":{\"technical\":10,\"experience\":5,\"soft_skills\":45,\"potential\":60},\"summary\":\"Profil de d\\u00e9veloppeur Full-Stack junior en d\\u00e9but de carri\\u00e8re. Bien que techniquement solide sur le d\\u00e9veloppement web, il y a une absence totale des comp\\u00e9tences critiques et de la s\\u00e9niorit\\u00e9 requises pour un poste d\'ing\\u00e9nieur DevOps Senior.\",\"verdict\":{\"pros\":[\"Bonne ma\\u00eetrise des fondamentaux du d\\u00e9veloppement (JS, Java, Python)\",\"Exp\\u00e9rience pratique en Git et versioning\",\"Formation acad\\u00e9mique pertinente en technologies informatiques\"],\"cons\":[\"Absence totale d\'outils DevOps (Docker, K8s, CI\\/CD, Terraform, Cloud)\",\"Profil junior (sorti d\'\\u00e9cole en 2024) incompatible avec un r\\u00f4le Senior\"]},\"recommendation\":\"Ne pas retenir pour ce poste. Profil \\u00e0 r\\u00e9orienter vers un poste de d\\u00e9veloppeur Full-Stack junior.\",\"raw_text_analyzed\":\"[Document analys\\u00e9 visuellement par l\'IA]\"}', NULL),
(8, 'ACCEPTED', '2026-04-07 13:45:22', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1775562338/syfonu/cvs/j4tthpodl79dcclxdvov.pdf', 5, 5, 41, '{\"score\":41,\"summary\":\"Analyse manuelle terminée. Score global : 41\\/100. 0 compétences clés détectées. Langues identifiées : français, anglais, arabe.\",\"recommendation\":\"Profil junior ou nécessitant une formation complémentaire.\",\"found_keywords\":[],\"missing_keywords\":[\"fjfgdggggggggggggggggggggggggggggggggghhhhhhhhhhbbbbbbbbbbbbbbbb\"],\"raw_text_analyzed\":\"Développeur passionné par la programmation, je me spécialise dans le\\ndéveloppement web full-stack, en intégrant aussi bien les technologies frontend\\nque backend, ainsi que la conception de logiciels. Curieux, rigoureux et créatif, je\\nm’investis pleinement dans chaque projet pour créer des solutions performantes,\\nfiables et innovantes.\\nRAYEN\\nBEN MOHAMED\\n DÉVELOPPEUR FULL-STACK \\nrayenbenmohamed169@gmail.com\\t+216-96 293 224\\tAriana,tunis\\nEXPÉRIENCES PROFESSIONNELLES\\nFORMATION\\n COMPÉTENCES TECHNIQUES\\nFront-end\\nArabe: langue maternelle\\nFrançais : Bon niveau\\nAnglais : Intermédiaire (B1)\\nSTAGE DE FIN D’ÉTUDE – TUNISIA TRAINING SCHOOL\\n STAGE – GOMYCODE\\n STAGE – TUNISIE TÉLÉCOM\\nLICENCE EN TECHNOLOGIES INFORMATIQUES\\nCERTIFICAT EN REACT JS\\nBACCALAURÉAT (SCIENCE INFORMATIQUE) \\nISET de Sfax \\nGoMyCode \\n2021\\nLANGUES \\nEXPERTISE\\n6 février 2024 – 25 mai 2024\\n24 Juin 2023 – 15 Septembre 2023\\n2 Janvier 2022 – 8 Février 2022\\n 2021 – 2024\\nJuin – Septembre 2023\\nDéveloppement full-stack avec Angular (frontend) et\\nNode.js\\/Express (backend).\\n Conception de la base de données MongoDB\\n(modélisation, relations, indexation) et mise en place\\nd’une authentification sécurisée (JWT, gestion des\\nrôles).\\nCréation d’une interface intuitive pour gérer les\\nsessions, apprenants, formateurs et fonctions\\nadministratives (CRUD, suivi).\\n \\nInitiation au développement frontend avec React.js :\\ncréation de composants, gestion des états, intégration\\navec Bootstrap.\\n Mise en pratique à travers des projets connectés à un\\nbackend Node.js et une base de données MongoDB.\\n Compréhension des principes d’API REST, de Git\\/GitHub\\net du cycle de développement web.\\nInitiation aux infrastructures réseaux et équipements\\ntélécoms\\nParticipation à la configuration d’équipements et au\\ndiagnostic des pannes\\nCompréhension des protocoles réseaux et de la sécurité\\ninformatique\\nDéveloppement web full-stack avec\\nJavaScript (Angular, React, Node.js)\\nConception et architecture logicielle (MVC,\\nAPI REST, modélisation UML)\\nGestion de bases de données\\nrelationnelles (MySQL) et NoSQL\\n(MongoDB)\\nDéveloppement d’applications Java\\nnatives et Spring Boot\\nRésolution de problèmes complexes et\\noptimisation de performances\\nwww.linkedin.com\\/in\\/rayen-ben-mohamed\\nhttps:\\/\\/github.com\\/rayenbenmohamed\\nAngular, React.js\\nHTML5, CSS3, JavaScript (ES6+),TypeScript\\nBootstrap, Responsive Design\\nback-end\\nNode.js, Express.js\\nPHP (avec XAMPP), Python (Django)\\nJava (applications natives), Spring Boot\\nBase de données & API\\nMongoDB, MySQL\\nConception et consommation d’API REST\\nAuthentification sécurisée (JWT), gestion\\ndes rôles et des sessions\\n Outils & Méthodologies\\nGit, GitHub (versioning & collaboration)\\nXAMPP (environnement local PHP\\/MySQL)\\nArchitecture logicielle (MVC, REST), bonnes\\npratiques de développement\",\"details\":{\"technical\":0,\"experience\":20,\"languages\":15,\"bonus\":6}}', '2026-04-27 23:05:50'),
(9, 'PENDING', '2026-04-07 17:49:41', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1775577011/syfonu/cvs/zifkfsu1w5bwqncuoer3.pdf', 5, 3, 41, '{\"score\":41,\"summary\":\"Analyse manuelle terminée. Score global : 41\\/100. 0 compétences clés détectées. Langues identifiées : français, anglais, arabe.\",\"recommendation\":\"Profil junior ou nécessitant une formation complémentaire.\",\"found_keywords\":[],\"missing_keywords\":[\"nbhgggh\"],\"raw_text_analyzed\":\"Développeur passionné par la programmation, je me spécialise dans le\\ndéveloppement web full-stack, en intégrant aussi bien les technologies frontend\\nque backend, ainsi que la conception de logiciels. Curieux, rigoureux et créatif, je\\nm’investis pleinement dans chaque projet pour créer des solutions performantes,\\nfiables et innovantes.\\nRAYEN\\nBEN MOHAMED\\n DÉVELOPPEUR FULL-STACK \\nrayenbenmohamed169@gmail.com\\t+216-96 293 224\\tAriana,tunis\\nEXPÉRIENCES PROFESSIONNELLES\\nFORMATION\\n COMPÉTENCES TECHNIQUES\\nFront-end\\nArabe: langue maternelle\\nFrançais : Bon niveau\\nAnglais : Intermédiaire (B1)\\nSTAGE DE FIN D’ÉTUDE – TUNISIA TRAINING SCHOOL\\n STAGE – GOMYCODE\\n STAGE – TUNISIE TÉLÉCOM\\nLICENCE EN TECHNOLOGIES INFORMATIQUES\\nCERTIFICAT EN REACT JS\\nBACCALAURÉAT (SCIENCE INFORMATIQUE) \\nISET de Sfax \\nGoMyCode \\n2021\\nLANGUES \\nEXPERTISE\\n6 février 2024 – 25 mai 2024\\n24 Juin 2023 – 15 Septembre 2023\\n2 Janvier 2022 – 8 Février 2022\\n 2021 – 2024\\nJuin – Septembre 2023\\nDéveloppement full-stack avec Angular (frontend) et\\nNode.js\\/Express (backend).\\n Conception de la base de données MongoDB\\n(modélisation, relations, indexation) et mise en place\\nd’une authentification sécurisée (JWT, gestion des\\nrôles).\\nCréation d’une interface intuitive pour gérer les\\nsessions, apprenants, formateurs et fonctions\\nadministratives (CRUD, suivi).\\n \\nInitiation au développement frontend avec React.js :\\ncréation de composants, gestion des états, intégration\\navec Bootstrap.\\n Mise en pratique à travers des projets connectés à un\\nbackend Node.js et une base de données MongoDB.\\n Compréhension des principes d’API REST, de Git\\/GitHub\\net du cycle de développement web.\\nInitiation aux infrastructures réseaux et équipements\\ntélécoms\\nParticipation à la configuration d’équipements et au\\ndiagnostic des pannes\\nCompréhension des protocoles réseaux et de la sécurité\\ninformatique\\nDéveloppement web full-stack avec\\nJavaScript (Angular, React, Node.js)\\nConception et architecture logicielle (MVC,\\nAPI REST, modélisation UML)\\nGestion de bases de données\\nrelationnelles (MySQL) et NoSQL\\n(MongoDB)\\nDéveloppement d’applications Java\\nnatives et Spring Boot\\nRésolution de problèmes complexes et\\noptimisation de performances\\nwww.linkedin.com\\/in\\/rayen-ben-mohamed\\nhttps:\\/\\/github.com\\/rayenbenmohamed\\nAngular, React.js\\nHTML5, CSS3, JavaScript (ES6+),TypeScript\\nBootstrap, Responsive Design\\nback-end\\nNode.js, Express.js\\nPHP (avec XAMPP), Python (Django)\\nJava (applications natives), Spring Boot\\nBase de données & API\\nMongoDB, MySQL\\nConception et consommation d’API REST\\nAuthentification sécurisée (JWT), gestion\\ndes rôles et des sessions\\n Outils & Méthodologies\\nGit, GitHub (versioning & collaboration)\\nXAMPP (environnement local PHP\\/MySQL)\\nArchitecture logicielle (MVC, REST), bonnes\\npratiques de développement\",\"details\":{\"technical\":0,\"experience\":20,\"languages\":15,\"bonus\":6}}', '2026-04-27 23:05:51'),
(11, 'HR_SCREENING', '2026-04-20 17:35:41', 'ezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDIezsdvfhiuhivehiVZHDI', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776702944/syfonu/cvs/dvpbecwqdt1bt0bmclo0.pdf', 5, 24, 81, '{\"score\":81,\"summary\":\"Profil int\\u00e9ressant avec une exp\\u00e9rience pertinente. Les comp\\u00e9tences techniques semblent correspondre aux attentes du poste.\",\"pros\":[\"Exp\\u00e9rience sectorielle\",\"Comp\\u00e9tences techniques\",\"Motivation\"],\"cons\":[\"Manque de d\\u00e9tails sur certains projets\",\"Niveau d\'anglais \\u00e0 confirmer\"],\"recommendation\":\"\\u00c0 contacter pour un premier entretien t\\u00e9l\\u00e9phonique.\"}', '2026-04-21 12:49:57'),
(13, 'PENDING', '2026-04-21 13:02:53', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776769424/syfonu/cvs/phzo65lgpcxxwaamodki.pdf', 12, 17, 72, '{\"score\":72,\"summary\":\"Profil int\\u00e9ressant avec une exp\\u00e9rience pertinente. Les comp\\u00e9tences techniques semblent correspondre aux attentes du poste.\",\"pros\":[\"Exp\\u00e9rience sectorielle\",\"Comp\\u00e9tences techniques\",\"Motivation\"],\"cons\":[\"Manque de d\\u00e9tails sur certains projets\",\"Niveau d\'anglais \\u00e0 confirmer\"],\"recommendation\":\"\\u00c0 contacter pour un premier entretien t\\u00e9l\\u00e9phonique.\"}', '2026-04-21 13:03:45'),
(14, 'PENDING', '2026-04-22 11:50:35', 'ekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorgekgfuygzeouvgzeuorg', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776851439/syfonu/cvs/lgjqonkvpnfh7kvrvf0y.pdf', 5, 17, 70, '{\"score\":70,\"summary\":\"Profil int\\u00e9ressant avec une exp\\u00e9rience pertinente. Les comp\\u00e9tences techniques semblent correspondre aux attentes du poste.\",\"pros\":[\"Exp\\u00e9rience sectorielle\",\"Comp\\u00e9tences techniques\",\"Motivation\"],\"cons\":[\"Manque de d\\u00e9tails sur certains projets\",\"Niveau d\'anglais \\u00e0 confirmer\"],\"recommendation\":\"\\u00c0 contacter pour un premier entretien t\\u00e9l\\u00e9phonique.\"}', '2026-04-22 11:50:43'),
(16, 'PENDING', '2026-04-22 12:02:07', 'zerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiygzerkjvuyegviggreiuugvuiyg', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776852128/syfonu/cvs/ti8fk5ftd3x5vctnmwfm.pdf', 5, 25, 85, '{\"score\":85,\"summary\":\"Rayen Ben Mohamed est un d\\u00e9veloppeur full-stack passionn\\u00e9 avec une exp\\u00e9rience solide en d\\u00e9veloppement web, notamment avec Angular, React.js, Node.js et MongoDB. Il a travaill\\u00e9 sur plusieurs projets, dont un stage chez Tunisia Training School et Gomycode, o\\u00f9 il a d\\u00e9velopp\\u00e9 des comp\\u00e9tences en conception de logiciels, gestion de bases de donn\\u00e9es et r\\u00e9solution de probl\\u00e8mes complexes.\",\"pros\":[\"Exp\\u00e9rience solide en d\\u00e9veloppement full-stack avec des technologies telles que Angular, React.js et Node.js\",\"Connaissance approfondie de la conception de logiciels, y compris la mod\\u00e9lisation UML et l\'architecture MVC\",\"Comp\\u00e9tences en gestion de bases de donn\\u00e9es relationnelles et NoSQL, notamment MySQL et MongoDB\"],\"cons\":[\"Manque d\'exp\\u00e9rience dans le domaine sp\\u00e9cifique de zev, vds et sdv mentionn\\u00e9 dans le poste\",\"Pas d\'information claire sur la capacit\\u00e9 \\u00e0 travailler de mani\\u00e8re autonome et \\u00e0 g\\u00e9rer son temps efficacement\"],\"recommendation\":\"Entretien technique recommand\\u00e9 pour \\u00e9valuer les comp\\u00e9tences sp\\u00e9cifiques du candidat et discuter de son potentiel pour le poste\"}', '2026-04-22 12:02:12'),
(20, 'PENDING', '2026-04-27 21:35:43', ',nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb,nvkhgvelsbdflvrjhgrvhruvjb', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1777318508/syfonu/cvs/wvt2u6njmqy3r8npnc1b.pdf', 5, 14, 85, '{\"score\":85,\"summary\":\"D\\u00e9veloppeur full-stack passionn\\u00e9 avec exp\\u00e9rience dans les technologies frontend et backend, notamment Angular, React.js, Node.js et MongoDB. Forte compr\\u00e9hension des principes d\'API REST, de Git\\/GitHub et du cycle de d\\u00e9veloppement web.\",\"pros\":[\"Exp\\u00e9rience dans le d\\u00e9veloppement full-stack avec Angular et Node.js\",\"Connaissance approfondie des bases de donn\\u00e9es NoSQL (MongoDB) et relationnelles (MySQL)\",\"Comp\\u00e9tences solides en conception et architecture logicielle (MVC, API REST, mod\\u00e9lisation UML)\"],\"cons\":[\"Manque d\'exp\\u00e9rience dans la r\\u00e9solution de probl\\u00e8mes complexes et l\'optimisation de performances\",\"Limitations dans la ma\\u00eetrise de certaines technologies backend comme Python (Django) et Java (applications natives)\"],\"recommendation\":\"Entretien technique approfondi recommand\\u00e9 pour \\u00e9valuer les comp\\u00e9tences pratiques et la capacit\\u00e9 \\u00e0 r\\u00e9soudre des probl\\u00e8mes complexes\"}', '2026-04-27 21:35:49'),
(23, 'PENDING', '2026-05-02 22:43:36', 'rjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiurrjgnoierjgçi^hjiuerhpiuhpeiur', 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1777754609/syfonu/cvs/mmr0zshz2ai9mmwnzwch.pdf', 5, 26, 85, '{\"score\":85,\"summary\":\"Le candidat, Rayen Ben Mohamed, poss\\u00e8de une solide formation en d\\u00e9veloppement web full-stack et une exp\\u00e9rience pratique avec les technologies requises pour le poste, notamment React, Java et CSS. Il a travaill\\u00e9 sur des projets de d\\u00e9veloppement web utilisant Angular, Node.js et MongoDB, et a une bonne compr\\u00e9hension des principes d\'API REST et de la s\\u00e9curit\\u00e9 informatique.\",\"pros\":[\"Exp\\u00e9rience pratique avec les technologies requises pour le poste, notamment React, Java et CSS\",\"Solide formation en d\\u00e9veloppement web full-stack avec une licence en technologies informatiques et un certificat en React JS\",\"Capacit\\u00e9 \\u00e0 travailler en \\u00e9quipe et \\u00e0 r\\u00e9soudre des probl\\u00e8mes complexes, comme le montrent ses exp\\u00e9riences professionnelles chez Tunisia Training School, Gomycode et Tunisie T\\u00e9l\\u00e9com\"],\"cons\":[\"Manque d\'exp\\u00e9rience directe avec les technologies sp\\u00e9cifiques requises pour le poste, notamment la combinaison de React, Java et CSS\",\"Pas d\'informations pr\\u00e9cises sur les r\\u00e9sultats et les r\\u00e9alisations concr\\u00e8tes obtenues lors de ses exp\\u00e9riences professionnelles\"],\"recommendation\":\"Entretien technique recommand\\u00e9 pour \\u00e9valuer les comp\\u00e9tences techniques du candidat et discuter de ses exp\\u00e9riences professionnelles en d\\u00e9tail\"}', '2026-05-02 22:43:48');

-- --------------------------------------------------------

--
-- Structure de la table `job_offre`
--

CREATE TABLE `job_offre` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `salary` double DEFAULT NULL,
  `publishedAt` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `employment_type` varchar(100) DEFAULT NULL,
  `is_salary_negotiable` tinyint(1) NOT NULL,
  `advantages` longtext DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `company_logo` varchar(500) DEFAULT NULL,
  `company_logo_public_id` varchar(255) DEFAULT NULL,
  `skills` longtext DEFAULT NULL,
  `entreprise_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `job_offre`
--

INSERT INTO `job_offre` (`id`, `title`, `description`, `location`, `salary`, `publishedAt`, `status`, `created_at`, `updated_at`, `expires_at`, `employment_type`, `is_salary_negotiable`, `advantages`, `user_id`, `company_logo`, `company_logo_public_id`, `skills`, `entreprise_id`) VALUES
(1, 'Developpeur Symfony', 'Test Job Description.', 'Remote', 55000, NULL, 'PUBLISHED', '2026-04-06 00:42:19', NULL, '2026-05-05 09:00:00', 'CDI', 1, 'Tickets resto, remote', 1, NULL, NULL, NULL, NULL),
(2, 'php ', 'hjghfhghjshjhjdhjd', 'paris', 4500, NULL, 'PUBLISHED', '2026-04-06 00:58:50', NULL, '2026-04-12 23:58:00', 'CDD', 0, 'cccccc', 1, NULL, NULL, NULL, NULL),
(3, 'cccccccc', 'NBHGGGH', 'paris', 5555555555, NULL, 'PUBLISHED', '2026-04-06 00:59:36', NULL, '2026-04-16 03:59:00', 'Freelance', 1, 'BHJGGH', 1, NULL, NULL, NULL, NULL),
(4, 'Ingenieur DevOps', 'Poste en CDI pour ingenieur DevOps senior.', 'Lyon', 55000, '2026-04-06 01:48:00', 'PUBLISHED', '2026-04-06 01:49:15', NULL, NULL, 'CDI', 0, 'Teletravail 3j/sem,Prime annuelle,Voiture de fonction', 5, NULL, NULL, NULL, NULL),
(5, 'developpeur web', 'fjfgdggggggggggggggggggggggggggggggggghhhhhhhhhhbbbbbbbbbbbbbbbb', 'tunus', 2500, '2026-04-23 00:49:00', 'DRAFT', '2026-04-07 00:51:09', NULL, '2026-04-29 23:50:00', 'CDD', 1, 'ticketresto', 4, NULL, NULL, NULL, NULL),
(9, 'fhhgfghhfhhf', 'fdgfdgfdgfdfdfdfdgfdfdffdfdgf', 'Remote', 45000, '2026-04-07 11:03:00', 'PUBLISHED', '2026-04-07 11:04:24', NULL, '2026-04-18 10:04:00', 'CDD', 0, 'ticket', 4, NULL, NULL, NULL, NULL),
(10, 'webjava', 'HBHJBHJBHJBHJGHGHJGHJBHJGHJBHJ', 'ariana', 60000, '2026-04-07 13:13:00', 'PUBLISHED', '2026-04-07 13:14:21', '2026-04-07 13:41:56', '2026-04-25 12:17:00', 'CDD', 1, 'TICKET RESTO', 5, NULL, NULL, NULL, NULL),
(11, 'devwww', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', 'paris', 50000, '2026-04-07 17:46:00', 'PUBLISHED', '2026-04-07 17:48:03', NULL, '2026-04-08 16:49:00', 'Freelance', 1, '', 5, NULL, NULL, NULL, NULL),
(12, '[Copie] devwww', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', 'paris', 50000, '2026-04-07 17:48:09', 'DRAFT', '2026-04-07 17:48:09', NULL, NULL, 'Freelance', 1, '', 5, NULL, NULL, NULL, NULL),
(13, 'hachem', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'paris', 45000, '2026-04-11 22:42:00', 'PUBLISHED', '2026-04-11 22:43:11', NULL, '2026-04-12 21:43:00', 'Stage', 1, '', 9, NULL, NULL, NULL, NULL),
(14, '[Copie] hachem', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'paris', 45000, '2026-04-11 22:44:07', 'DRAFT', '2026-04-11 22:44:07', NULL, NULL, 'Stage', 1, '', 9, NULL, NULL, NULL, NULL),
(15, 'Developpeur avec Avantages', 'Une offre de test pour vUne offre de test pour verifier les nouveaux avantages.', 'Tunis', 60000, '2026-04-11 23:38:00', 'PUBLISHED', '2026-04-11 23:38:36', NULL, NULL, 'CDI', 0, 'Parking gratuit', 9, NULL, NULL, NULL, NULL),
(16, 'devobs', 'hahahahahahahahhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'paris', 45000, '2026-04-13 23:39:00', 'PUBLISHED', '2026-04-13 23:39:47', NULL, '2026-04-14 22:41:00', 'Freelance', 1, 'Parking gratuit', 10, NULL, NULL, NULL, NULL),
(17, 'jblgluyg', 'aefnmezuihfliuezhiyfgouezgfouygezfougz', 'sfax', 50000, '2026-04-14 00:12:00', 'PUBLISHED', '2026-04-14 00:13:54', NULL, '2026-04-30 23:18:00', 'CDD', 1, 'aa', 10, 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776118438/job_offres/logos/rxz3vdwexjx5v0b7nsmt.png', 'job_offres/logos/rxz3vdwexjx5v0b7nsmt', NULL, NULL),
(18, 'php poste', '* Introduction\r\nVous êtes à la recherche d\'un stage pour développer vos compétences en programmation ? Nous proposons un stage attractif au sein de notre équipe à Paris. Le salaire pour ce stage est de 50 000 €.\r\n\r\n* Vos missions\r\nVous travaillerez sur des projets passionnants qui nécessitent des compétences en PHP et Java. Vous serez chargés de développer et de maintenir des applications de haute qualité, en collaborant étroitement avec notre équipe de développement.\r\n\r\n* Le profil attendu\r\nPour réussir dans ce rôle, vous devez avoir une bonne maîtrise de PHP et Java. Vous êtes une personne curieuse, motivée et passionnée par la programmation. Vous avez également de bonnes compétences en analyse et en résolution de problèmes.\r\n\r\n* Pourquoi nous rejoindre\r\nEn rejoignant notre équipe, vous bénéficierez non seulement d\'un salaire compétitif, mais également d\'avantages tels que l\'utilisation d\'une voiture de fonction (cars). Nous offrons une environnement de travail dynamique et stimulant, avec des opportunités de développement et de croissance. Nous sommes convaincus que vous trouverez chez nous un environnement propice à votre épanouissement professionnel.', 'paris', 50000, '2026-04-15 00:10:00', 'PUBLISHED', '2026-04-15 00:10:23', '2026-04-15 00:39:42', '2026-04-17 23:08:00', 'Stage', 1, 'cars', 10, 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776204634/job_offres/logos/vqnmkhkjbgawfxzxdvgi.png', 'job_offres/logos/vqnmkhkjbgawfxzxdvgi', 'php,java', NULL),
(19, 'paris', '* Introduction\r\nVous êtes à la recherche d\'un nouveau défi dans une ville emblématique ? Nous proposons un poste d\'Alternance à Paris, au cœur de la capitale française. Ce poste vous offre l\'opportunité de développer vos compétences et de vous intégrer dans une équipe dynamique.\r\n\r\n* Vos missions\r\nVous travaillerez sur des projets variés, en utilisant vos compétences en PHP et Java pour développer des solutions innovantes. Vous serez chargés de concevoir, de développer et de tester des applications, en collaborant étroitement avec l\'équipe de développement.\r\n\r\n* Le profil attendu\r\nVous êtes des étudiants ou des jeunes diplômés motivés, avec une forte appétence pour les technologies de développement. Vous avez des connaissances solides en PHP et Java, et vous êtes prêts à apprendre et à vous adapter à de nouvelles technologies. Vous êtes une équipe joueuse, avec d\'excellentes compétences en communication et en travail d\'équipe.\r\n\r\n* Pourquoi nous rejoindre\r\nVous rejoindrez une équipe dynamique et passionnée, avec des opportunités de développement et de croissance. Vous bénéficierez d\'un salaire compétitif de 50 000 €, ainsi que d\'avantages tels que la possibilité d\'utiliser une voiture de société (cars). Nous proposons une expérience de travail unique et enrichissante, avec des défis stimulants et des possibilités de progression.', 'paris', 50000, '2026-04-14 23:25:00', 'PUBLISHED', '2026-04-15 00:27:50', NULL, '2026-04-14 23:29:00', 'Alternance', 1, 'cars', 10, 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776205671/job_offres/logos/q9auic40aomg3rifbxfp.png', 'job_offres/logos/q9auic40aomg3rifbxfp', 'php,java', NULL),
(20, 'pariss', '* Introduction\r\nVous êtes un développeur passionné et expérimenté ? Vous cherchez un nouveau défi dans une équipe dynamique ? Nous recherchons un développeur talentueux pour rejoindre notre équipe à Paris. Le poste de pariss est un contrat à durée déterminée (CDD) qui offre une rémunération compétitive de 60 000 € par an, ainsi que d\'autres avantages tels que la possibilité d\'utiliser une voiture de société (cars).\r\n\r\n* Vos missions\r\nVous serez responsable de développer et de maintenir des applications web et mobiles en utilisant vos compétences en PHP et Java. Vous travaillerez en étroite collaboration avec notre équipe de développement pour concevoir et mettre en œuvre des solutions innovantes et efficaces. Vous serez également chargé de résoudre les problèmes techniques et de garantir la qualité et la performance de nos applications.\r\n\r\n* Le profil attendu\r\nPour réussir dans ce rôle, vous devez avoir une solide expérience dans le développement en PHP et Java. Vous devez également avoir une bonne compréhension des principes de développement logiciel et des meilleures pratiques en matière de conception et de test. Une expérience dans le développement d\'applications web et mobiles est un plus.\r\n\r\n* Pourquoi nous rejoindre\r\nEn rejoignant notre équipe, vous bénéficierez d\'une rémunération compétitive, d\'opportunités de développement professionnel et d\'un environnement de travail dynamique et stimulant. Vous aurez également accès à des avantages tels que l\'utilisation d\'une voiture de société, ce qui facilitera vos déplacements professionnels. Nous sommes une équipe passionnée et dédiée, et nous sommes convaincus que vous allez adorer travailler avec nous.', 'paris', 60000, '2026-04-14 23:29:00', 'PUBLISHED', '2026-04-15 00:30:13', '2026-04-15 00:40:31', '2026-04-15 23:31:00', 'CDD', 1, 'cars', 10, 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776205814/job_offres/logos/veyo3wsxrl1swwzojqtx.png', 'job_offres/logos/veyo3wsxrl1swwzojqtx', 'php,java', NULL),
(21, 'hachem', '* Introduction\r\nVous recherchez un nouveau défi en tant que développeur ? Vous souhaitez rejoindre une équipe dynamique et innovante à Paris ? Nous proposons un poste de Hachem en contrat à durée déterminée (CDD) qui pourrait correspondre à vos attentes.\r\n\r\n* Vos missions\r\nVous travaillerez sur des projets de développement logiciel en utilisant vos compétences en PHP et Java. Vous serez chargés de concevoir, développer et tester des applications de qualité. Vous collaborerez avec l\'équipe pour identifier les besoins et les exigences des projets et vous participerez à la mise en œuvre des solutions.\r\n\r\n* Le profil attendu\r\nVous devez avoir de solides compétences en PHP et Java, ainsi qu\'une expérience dans le développement de logiciels. Vous êtes une personne motivée, curieuse et toujours à la recherche de nouvelles technologies et de meilleures pratiques. Vous avez de bonnes compétences en communication et en travail d\'équipe.\r\n\r\n* Pourquoi nous rejoindre\r\nNous offrons un salaire attractif de 50 000 € pour ce poste. Vous bénéficierez également d\'un contrat à durée déterminée (CDD) qui vous permettra de vous intégrer à notre équipe pendant une période définie. De plus, nous vous proposons une voiture de fonction (cars) pour vous faciliter vos déplacements. Rejoignez notre équipe à Paris et découvrez un environnement de travail dynamique et stimulant.', 'paris', 50000, '2026-04-14 23:41:00', 'PUBLISHED', '2026-04-15 00:41:34', '2026-04-14 23:48:01', '2026-04-15 00:02:00', 'CDD', 1, 'cars', 10, 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776206495/job_offres/logos/senfbeeg7ux2fxy7jblb.png', 'job_offres/logos/senfbeeg7ux2fxy7jblb', 'php,java', NULL),
(22, 'oussema', '**Introduction**\r\nVous êtes un développeur passionné et expérimenté ? Vous souhaitez rejoindre une équipe dynamique et innovante ? Nous recherchons un développeur talentueux pour notre équipe à Paris. Le poste est intitulé Oussema et est proposé en contrat à durée déterminée (CDD).\r\n\r\n**Vos missions**\r\nVous serez chargés de développer et de maintenir des applications web et mobiles en utilisant les langages de programmation PHP et Java. Vous travaillerez en étroite collaboration avec notre équipe de développement pour concevoir et mettre en œuvre des solutions innovantes et efficaces. Vous participerez également à la résolution de problèmes techniques et à l\'optimisation des performances des applications.\r\n\r\n**Le profil attendu**\r\nPour réussir dans ce rôle, vous devez avoir une solide expérience dans le développement de logiciels, notamment en PHP et Java. Vous devez également avoir une bonne compréhension des principes de développement logiciel et des meilleures pratiques en matière de codage. Une expérience dans le développement d\'applications web et mobiles est un plus.\r\n\r\n**Pourquoi nous rejoindre**\r\nEn rejoignant notre équipe, vous bénéficierez d\'un salaire compétitif de 40 000 € par an, ainsi que d\'autres avantages tels qu\'une voiture de fonction (cars). Vous aurez également l\'opportunité de travailler sur des projets stimulants et de développer vos compétences dans un environnement dynamique et innovant. Nous offrons une culture de travail collaborative et conviviale, et nous sommes convaincus que vous vous épanouirez dans notre équipe.', 'paris', 40000, '2026-04-14 23:48:00', 'PUBLISHED', '2026-04-14 23:48:45', NULL, '2026-04-14 23:50:00', 'CDD', 1, 'cars', 10, 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776206927/job_offres/logos/u3uuctwgpvndhzxnsnkm.png', 'job_offres/logos/u3uuctwgpvndhzxnsnkm', 'php,java', NULL),
(24, 'hachem', 'Introduction\r\n- Vous êtes développeur passionné et expérimenté ? Vous cherchez un nouveau défi pour mettre en pratique vos compétences techniques ? Nous proposons un poste de hachem en freelance à Paris, idéal pour ceux qui cherchent à travailler de manière autonome tout en bénéficiant d\'un salaire attractif.\r\n\r\nVos missions\r\n- Vous serez chargés de développer des applications web en utilisant Java, Symfony et React, en vous assurant de la qualité et de la performance des produits finis.\r\n- Vous travaillerez de manière freelance, ce qui vous permettra de gérer votre temps et vos projets de manière autonome.\r\n- Vous collaborerez avec notre équipe pour comprendre les besoins des clients et développer des solutions personnalisées.\r\n\r\nLe profil attendu\r\n- Vous avez une expérience significative dans le développement web, notamment avec Java, Symfony et React.\r\n- Vous avez une excellente maîtrise de ces technologies, avec un niveau de compétence de 100% pour chacune d\'elles.\r\n- Vous êtes capables de travailler de manière autonome, en vous organisant pour respecter les délais et les exigences des projets.\r\n\r\nPourquoi nous rejoindre\r\n- Vous bénéficierez d\'un salaire attractif de 45 000 € par an, ce qui vous permettra de valoriser votre travail et votre expertise.\r\n- Vous aurez accès à une voiture de fonction, ce qui facilitera vos déplacements professionnels et personnels.\r\n- Vous travaillerez dans un environnement dynamique et stimulant, avec des opportunités de développement et de croissance professionnelle.', 'paris', 45000, '2026-04-20 17:17:00', 'PUBLISHED', '2026-04-20 17:18:15', NULL, '2026-04-21 17:17:00', 'Freelance', 1, 'cars', 10, 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776701897/job_offres/logos/ftzllgnfcsxntavez8jh.png', 'job_offres/logos/ftzllgnfcsxntavez8jh', 'java (100%), symfony (100%), react (100%)', 2),
(25, 'rayenrayen', '* Introduction \r\nVous êtes un professionnel dynamique et motivé, prêt à relever de nouveaux défis ? Nous sommes à la recherche d\'un rayenrayen talentueux pour rejoindre notre équipe en tant que freelance à Tunis. Ce poste offre une opportunité unique de mettre en pratique vos compétences et votre expertise dans un environnement stimulant.\r\n\r\n* Vos missions \r\nDans ce rôle, vous serez chargés de mettre en œuvre vos compétences en zev (87%), vds (86%) et sdv (87%) pour atteindre les objectifs fixés. Vous travaillerez de manière autonome, en tant que freelance, pour nous aider à atteindre nos buts. Votre expertise et votre créativité seront essentielles pour réussir dans ce poste.\r\n\r\n* Le profil attendu \r\nNous recherchons des candidats ayant un excellent niveau de compétences en zev, vds et sdv. Vous devez être capables de travailler de manière indépendante, gérer votre temps efficacement et atteindre les délais. Une bonne communication et une forte motivation sont également essentielles pour réussir dans ce rôle.\r\n\r\n* Pourquoi nous rejoindre \r\nEn rejoignant notre équipe, vous bénéficierez d\'un salaire attractif de 12000 € et d\'avantages supplémentaires tels que des cars. Vous aurez également l\'opportunité de travailler sur des projets stimulants et de développer vos compétences dans un environnement dynamique. Nous offrons une expérience de travail flexible et adaptée à vos besoins en tant que freelance.', 'tunis', 12000, '2026-04-22 11:53:00', 'PUBLISHED', '2026-04-22 11:54:41', NULL, '2026-04-23 10:54:00', 'Freelance', 1, 'cars', 10, 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1776851682/job_offres/logos/ryiaw2gpatscy85fudii.png', 'job_offres/logos/ryiaw2gpatscy85fudii', 'zev (87%), vds (86%), sdv (87%)', 2),
(26, 'hachemmmmm', '* Introduction\r\nVous êtes étudiants ou jeunes diplômés en recherche d\'un stage pour mettre en pratique vos compétences et acquérir de l\'expérience professionnelle ? Nous proposons un stage passionnant à Tunis, qui vous permettra de développer vos compétences en développement web et de travailler sur des projets stimulants.\r\n\r\n* Vos missions\r\nVous travaillerez sur des projets de développement web utilisant React, Java et CSS. Vos missions consisteront à concevoir, développer et tester des applications web de haute qualité, en collaborant avec notre équipe de développement. Vous participerez également à l\'analyse des besoins des utilisateurs et à la proposition de solutions innovantes.\r\n\r\n* Le profil attendu\r\nPour réussir dans ce rôle, vous devez avoir une solide formation en développement web et une expérience pratique avec les technologies suivantes : \r\n- React (80%)\r\n- Java (84%)\r\n- CSS (85%)\r\nVous êtes une personne motivée, curieuse et passionnée par le développement web. Vous avez de bonnes compétences en analyse et en résolution de problèmes, ainsi qu\'une excellente capacité à travailler en équipe.\r\n\r\n* Pourquoi nous rejoindre\r\nEn rejoignant notre équipe, vous bénéficierez d\'un salaire attractif de 5000 € et d\'un environnement de travail dynamique et stimulant. Vous aurez également accès à des avantages tels que des cars pour faciliter vos déplacements. Nous proposons un stage de qualité qui vous permettra de développer vos compétences et de lancer votre carrière de manière réussie.', 'tunis', 5000, '2026-04-27 20:39:00', 'PUBLISHED', '2026-04-27 20:41:39', NULL, '2026-04-30 19:40:00', 'Stage', 1, 'cars', 10, 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1777315272/job_offres/logos/aqzr9unlbmsw2gpd1pdi.png', 'job_offres/logos/aqzr9unlbmsw2gpd1pdi', 'react (80%), java (84%), css (85%)', 2),
(27, 'raurau', '- Introduction \r\nVous êtes à la recherche d\'un nouveau défi ? Vous souhaitez travailler de manière freelance et utiliser vos compétences pour réussir ? Nous avons l\'opportunité idéale pour vous. Le poste de Raurau est actuellement ouvert à Tunis, et nous recherchons des candidats talentueux et motivés pour rejoindre notre équipe.\r\n\r\n- Vos missions \r\nVous serez chargés de mettre en œuvre vos compétences exceptionnelles en hahah (92%) et ahhaha (88%) pour atteindre les objectifs fixés. Vous travaillerez de manière autonome, mais vous serez également en contact régulier avec notre équipe pour assurer la réussite de nos projets.\r\n\r\n- Le profil attendu \r\nVous êtes des personnes créatives, innovantes et orientées vers les résultats. Vous avez une solide expérience dans votre domaine et vous êtes capables de travailler de manière freelance. Vous êtes également à l\'aise avec la communication et la collaboration, car vous travaillerez avec notre équipe pour atteindre les objectifs communs.\r\n\r\n- Pourquoi nous rejoindre \r\nNous offrons un salaire compétitif de 450000 €, ainsi que d\'autres avantages tels que des cars. Nous sommes convaincus que notre équipe est la meilleure pour vous, et nous sommes impatients de vous accueillir à bord. Rejoignez-nous et découvrez pourquoi nous sommes l\'entreprise idéale pour vous.', 'tunis', 450000, '2026-04-27 22:27:00', 'ARCHIVED', '2026-05-02 20:39:46', NULL, '2026-04-29 21:27:00', 'Freelance', 1, 'cars', 10, 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1777747119/job_offres/logos/yp02up6dowjggx0at3ig.png', 'job_offres/logos/yp02up6dowjggx0at3ig', 'hahah (92%), ahhaha (88%)', 2),
(28, 'evfezv', '* Introduction \r\nVous êtes à la recherche d\'un nouveau défi ? Nous proposons un poste d\'evfezv à Paris. Ce contrat à durée déterminée (CDD) offre un salaire attractif de 450 000 €.\r\n\r\n* Vos missions \r\nVous serez amenés à utiliser vos compétences en ff, que vous maîtrisez à 78%. Vous travaillerez dans un environnement dynamique où vos compétences seront mises à profit pour atteindre les objectifs de l\'entreprise.\r\n\r\n* Le profil attendu \r\nNous recherchons des candidats motivés et passionnés par leur travail. Vous devez avoir une bonne maîtrise des compétences requises pour le poste, notamment en ff. Vous êtes une équipe de personnes capables de travailler de manière collaborative et de contribuer à la réussite de l\'entreprise.\r\n\r\n* Pourquoi nous rejoindre \r\nEn rejoignant notre équipe, vous bénéficierez d\'un salaire compétitif de 450 000 € et de nombreux avantages, notamment l\'utilisation d\'une voiture de fonction (cars). Vous serez également amenés à travailler dans un environnement stimulant et à vous développer professionnellement. Nous sommes convaincus que vous trouverez chez nous un environnement de travail idéal pour atteindre vos objectifs et réaliser vos ambitions.', 'paris', 450000, '2026-05-04 21:23:00', 'PUBLISHED', '2026-05-04 21:24:12', NULL, '2026-05-05 20:23:00', 'CDD', 1, 'cars', 10, 'https://res.cloudinary.com/dbxfuedn2/image/upload/v1777922687/job_offres/logos/rjwojaqef4yyaaunm8jl.jpg', 'job_offres/logos/rjwojaqef4yyaaunm8jl', 'ff (78%)', 2);

-- --------------------------------------------------------

--
-- Structure de la table `job_offre_avantage`
--

CREATE TABLE `job_offre_avantage` (
  `job_offre_id` int(11) NOT NULL,
  `avantage_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `job_offre_avantage`
--

INSERT INTO `job_offre_avantage` (`job_offre_id`, `avantage_id`) VALUES
(15, 1),
(15, 2),
(16, 1),
(16, 2),
(16, 3),
(16, 4),
(16, 5),
(16, 6),
(17, 1),
(19, 1),
(19, 2),
(20, 1),
(20, 2),
(21, 1),
(21, 2),
(22, 1),
(24, 1),
(24, 2),
(25, 1),
(25, 2),
(25, 3),
(25, 4),
(25, 5),
(25, 6),
(26, 1),
(26, 2),
(26, 3),
(27, 1),
(27, 2),
(27, 3),
(27, 4),
(28, 1),
(28, 2);

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `message` longtext NOT NULL,
  `is_read` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pdf_template`
--

CREATE TABLE `pdf_template` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `primary_color` varchar(20) DEFAULT NULL,
  `secondary_color` varchar(20) DEFAULT NULL,
  `header_html` longtext DEFAULT NULL,
  `footer_html` longtext DEFAULT NULL,
  `body_html` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `session`
--

CREATE TABLE `session` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `role` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `two_factor_code` varchar(255) DEFAULT NULL,
  `two_factor_expiry` datetime DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `discr` varchar(255) NOT NULL,
  `companyname` varchar(255) DEFAULT NULL,
  `departement` varchar(255) DEFAULT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT 0,
  `company_rne` varchar(32) DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 1,
  `profile_photo_url` varchar(512) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `role`, `password`, `firstName`, `lastName`, `phone`, `two_factor_code`, `two_factor_expiry`, `reset_token`, `reset_token_expiry`, `discr`, `companyname`, `departement`, `blocked`, `company_rne`, `approved`, `profile_photo_url`, `two_factor_enabled`) VALUES
(1, 'admin@syfonu.com', 'ROLE_ADMIN', '$2y$13$EsZPpuQpTsvGvo65eq2gS.aHyNag717uPuaCKUDD4GBLwY4AyOG.u', 'Admin', 'Syfonu', NULL, NULL, NULL, NULL, NULL, 'admin', NULL, NULL, 0, NULL, 1, NULL, 0),
(2, 'candidat@syfonu.com', 'ROLE_CANDIDAT', 'pass123', 'Jean', 'Dupont', NULL, NULL, NULL, NULL, NULL, 'candidat', NULL, NULL, 0, NULL, 1, NULL, 0),
(3, 'recruteur@syfonu.com', 'ROLE_RECRUITER', 'pass123', 'Alice', 'Smith', NULL, NULL, NULL, NULL, NULL, 'recruiter', NULL, NULL, 0, NULL, 1, NULL, 0),
(4, 'aminmnari888@gmail.com', 'ROLE_RECRUTEUR', '$2y$13$vxdYsmO/ii4B1dRMjPjWHejrmbE3ZerlOicUwX4ohKAvOMiScfXyu', 'amouna', 'amouna', '99999999', NULL, NULL, NULL, NULL, 'recruiter', NULL, NULL, 0, NULL, 1, NULL, 0),
(5, 'rayenbenmohamed169@gmail.com', 'ROLE_CANDIDAT', '$2y$13$XnK45XpJwXs9e3i2jsAuauz4EAHqOa6JJq5LdzcZzLmpvcu5QMuQe', 'rayen', 'ben mohamed', '+216123456', NULL, NULL, NULL, NULL, 'candidat', NULL, NULL, 0, NULL, 1, NULL, 0),
(6, 'test_candidate@syfonu.com', 'ROLE_CANDIDAT', '$2y$13$xyyDD0QKYX7ME1liMlBd/./095//YTLiK6.3OgGlh0ZwI2SmSn0i.', 'Test', 'Candidate', '+216123456', NULL, NULL, NULL, NULL, 'candidat', NULL, NULL, 0, NULL, 1, NULL, 0),
(7, 'onss3319@gmail.com', 'ROLE_ADMIN', '$2y$13$ZTPAewUAyUfoI1iMVeFv6OTriVe9gp5mcI8mFC6nEBZ4sW4F68KAO', 'ons', 'ons', '9999999', NULL, NULL, NULL, NULL, 'recruiter', 'tritux', 'rh', 0, NULL, 1, NULL, 0),
(8, 'test1@example.com', 'ROLE_RECRUTEUR', '$2y$13$BifgMje.yQAH05PsLulvLeb7X2Vz1l7VRbbJ2sbvJ73QQ4QJEooQy', 'Test', 'User', '123456789', NULL, NULL, NULL, NULL, 'recruiter', NULL, NULL, 0, NULL, 1, NULL, 0),
(9, 'recruiter@test.com', 'ROLE_RECRUTEUR', '$2y$13$YfTUJH200gii2OgiQxEWW.zUZM4f9loQJppVkayPQXzMBALXIgSFe', 'Test', 'User', '12345678', NULL, NULL, NULL, NULL, 'recruiter', NULL, NULL, 0, NULL, 1, NULL, 0),
(10, 'hachemahmad830@gmail.com', 'ROLE_RECRUTEUR', '$2y$13$u8e84ai7f66BBchljwP0HuVR4Z/V/auiLSFoVWoxyQOhvlBYi3XAm', 'Hachem', 'Ahmad', '25436090', NULL, NULL, NULL, NULL, 'recruiter', NULL, NULL, 0, NULL, 1, NULL, 0),
(11, 'basmalafatmagara@gmail.com', 'ROLE_RECRUTEUR', '$2y$13$tCIY.wEKiqV.p5bdfH1sIOJTVZZPqlPpgMiyL21hbKFr87w8IOLbW', 'clara', 'Ben Ahmed', '22884504', NULL, NULL, NULL, NULL, 'recruiter', 'snnnf', NULL, 0, NULL, 1, NULL, 0),
(12, 'oussama@gmail.com', 'ROLE_ADMIN', '$2y$13$qbQHWHk5Yul/dRAt2kBBQeT38oKRa2uc295wT1jb.25T8wHTHelhW', 'oussama', 'sgh', '96293224', NULL, NULL, NULL, NULL, 'candidat', NULL, NULL, 0, NULL, 1, NULL, 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avantage`
--
ALTER TABLE `avantage`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `calendar_event`
--
ALTER TABLE `calendar_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_57FA09C9A76ED395` (`user_id`);

--
-- Index pour la table `contract`
--
ALTER TABLE `contract`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E98F285991BD8781` (`candidate_id`),
  ADD KEY `IDX_E98F2859156BE243` (`recruiter_id`),
  ADD KEY `IDX_E98F2859CD1DF15B` (`contract_type_id`),
  ADD KEY `IDX_E98F28593481D195` (`job_offer_id`),
  ADD KEY `IDX_E98F2859CA5AA7D3` (`pdf_template_id`);

--
-- Index pour la table `contract_type`
--
ALTER TABLE `contract_type`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cover_letter`
--
ALTER TABLE `cover_letter`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cv`
--
ALTER TABLE `cv`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B66FFE92A76ED395` (`user_id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `document_candidate`
--
ALTER TABLE `document_candidate`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `document_contract`
--
ALTER TABLE `document_contract`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `entreprise`
--
ALTER TABLE `entreprise`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_D19FA60A76ED395` (`user_id`);

--
-- Index pour la table `forum_category`
--
ALTER TABLE `forum_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_21BF94265E237E06` (`name`);

--
-- Index pour la table `forum_comment`
--
ALTER TABLE `forum_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_65B81F1D4B89032C` (`post_id`);

--
-- Index pour la table `forum_like`
--
ALTER TABLE `forum_like`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `forum_post`
--
ALTER TABLE `forum_post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_996BCC5A12469DE2` (`category_id`),
  ADD KEY `IDX_996BCC5AA76ED395` (`user_id`);

--
-- Index pour la table `friend_message`
--
ALTER TABLE `friend_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8202F274F624B39D` (`sender_id`),
  ADD KEY `IDX_8202F274E92F8F78` (`recipient_id`),
  ADD KEY `idx_friend_msg_pair` (`sender_id`,`recipient_id`);

--
-- Index pour la table `friend_request`
--
ALTER TABLE `friend_request`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `friend_request_pair` (`sender_id`,`receiver_id`),
  ADD KEY `IDX_F284D94F624B39D` (`sender_id`),
  ADD KEY `IDX_F284D94CD53EDB6` (`receiver_id`);

--
-- Index pour la table `interview`
--
ALTER TABLE `interview`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_CF1D3C343E030ACD` (`application_id`);

--
-- Index pour la table `job_application`
--
ALTER TABLE `job_application`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_C737C688A76ED395` (`user_id`),
  ADD KEY `IDX_C737C6882B8FF521` (`job_offre_id`);

--
-- Index pour la table `job_offre`
--
ALTER TABLE `job_offre`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_AEDA3B1FA4AEAFEA` (`entreprise_id`),
  ADD KEY `IDX_AEDA3B1FA76ED395` (`user_id`);

--
-- Index pour la table `job_offre_avantage`
--
ALTER TABLE `job_offre_avantage`
  ADD PRIMARY KEY (`job_offre_id`,`avantage_id`),
  ADD KEY `IDX_E6DA9B2B8FF521` (`job_offre_id`),
  ADD KEY `IDX_E6DA9BEA96B22C` (`avantage_id`);

--
-- Index pour la table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BF5476CAA76ED395` (`user_id`);

--
-- Index pour la table `pdf_template`
--
ALTER TABLE `pdf_template`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avantage`
--
ALTER TABLE `avantage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `calendar_event`
--
ALTER TABLE `calendar_event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `contract`
--
ALTER TABLE `contract`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `contract_type`
--
ALTER TABLE `contract_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `cover_letter`
--
ALTER TABLE `cover_letter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cv`
--
ALTER TABLE `cv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `document_candidate`
--
ALTER TABLE `document_candidate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `document_contract`
--
ALTER TABLE `document_contract`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `entreprise`
--
ALTER TABLE `entreprise`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `forum_category`
--
ALTER TABLE `forum_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `forum_comment`
--
ALTER TABLE `forum_comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `forum_like`
--
ALTER TABLE `forum_like`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `forum_post`
--
ALTER TABLE `forum_post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `friend_message`
--
ALTER TABLE `friend_message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `friend_request`
--
ALTER TABLE `friend_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `interview`
--
ALTER TABLE `interview`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `job_application`
--
ALTER TABLE `job_application`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `job_offre`
--
ALTER TABLE `job_offre`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `pdf_template`
--
ALTER TABLE `pdf_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `session`
--
ALTER TABLE `session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `calendar_event`
--
ALTER TABLE `calendar_event`
  ADD CONSTRAINT `FK_A9F3F5A76A24B9D` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `contract`
--
ALTER TABLE `contract`
  ADD CONSTRAINT `FK_E98F2859156BE243` FOREIGN KEY (`recruiter_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_E98F28593481D195` FOREIGN KEY (`job_offer_id`) REFERENCES `job_offre` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_E98F285991BD8781` FOREIGN KEY (`candidate_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_E98F2859CA5AA7D3` FOREIGN KEY (`pdf_template_id`) REFERENCES `pdf_template` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_E98F2859CD1DF15B` FOREIGN KEY (`contract_type_id`) REFERENCES `contract_type` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `cv`
--
ALTER TABLE `cv`
  ADD CONSTRAINT `FK_B66FFE92A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `entreprise`
--
ALTER TABLE `entreprise`
  ADD CONSTRAINT `FK_D19FA60A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `forum_comment`
--
ALTER TABLE `forum_comment`
  ADD CONSTRAINT `FK_65B81F1D4B89032C` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `forum_post`
--
ALTER TABLE `forum_post`
  ADD CONSTRAINT `FK_996BCC5A12469DE2` FOREIGN KEY (`category_id`) REFERENCES `forum_category` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_996BCC5AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `friend_message`
--
ALTER TABLE `friend_message`
  ADD CONSTRAINT `FK_8202F274E92F8F78` FOREIGN KEY (`recipient_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_8202F274F624B39D` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `friend_request`
--
ALTER TABLE `friend_request`
  ADD CONSTRAINT `FK_F284D94CD53EDB6` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_F284D94F624B39D` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `interview`
--
ALTER TABLE `interview`
  ADD CONSTRAINT `FK_CF1D3C343E030ACD` FOREIGN KEY (`application_id`) REFERENCES `job_application` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `job_application`
--
ALTER TABLE `job_application`
  ADD CONSTRAINT `FK_C737C6882B8FF521` FOREIGN KEY (`job_offre_id`) REFERENCES `job_offre` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_C737C688A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `job_offre`
--
ALTER TABLE `job_offre`
  ADD CONSTRAINT `FK_AEDA3B1FA4AEAFEA` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprise` (`id`),
  ADD CONSTRAINT `FK_AEDA3B1FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `job_offre_avantage`
--
ALTER TABLE `job_offre_avantage`
  ADD CONSTRAINT `FK_E6DA9B2B8FF521` FOREIGN KEY (`job_offre_id`) REFERENCES `job_offre` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_E6DA9BEA96B22C` FOREIGN KEY (`avantage_id`) REFERENCES `avantage` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `FK_BF5476CAA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
