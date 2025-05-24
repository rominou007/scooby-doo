-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 05:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_five`
--

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `annee_scolaire` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`, `description`, `annee_scolaire`, `created_at`) VALUES
(1, 'B1 info', 'Première année bachelor en informatique', '2024/2025', '2025-05-23 14:44:24'),
(2, 'classe 2', 'casdcascas', '39/45', '2025-05-24 12:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `cours`
--

CREATE TABLE `cours` (
  `id_cours` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `id_prof` bigint(20) UNSIGNED DEFAULT NULL,
  `titre` varchar(200) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cours`
--

INSERT INTO `cours` (`id_cours`, `id_module`, `id_prof`, `titre`, `contenu`, `date_creation`) VALUES
(12, 1, 1, 'initiation à la programmation', 'Le premier cours d\'initiation à la programmation en C vise à introduire les concepts fondamentaux du langage. Il couvre généralement les bases de la programmation, la structure d\'un programme C, les types de données, les variables, et les opérations simples. On y apprend également la syntaxe, l\'utilisation des bibliothèques standard, et les premières fonctions essentielles comme l\'affichage avec printf() et la lecture d\'entrée avec scanf(). Ce cours met l\'accent sur la logique algorithmique et la compréhension des principes de la programmation structurée. Un bon point de départ pour se lancer dans le développement logiciel !', '2025-05-23 23:13:07');

-- --------------------------------------------------------

--
-- Table structure for table `devoirs`
--

CREATE TABLE `devoirs` (
  `id_devoir` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `id_prof` bigint(20) UNSIGNED NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_limite` date NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devoirs`
--

INSERT INTO `devoirs` (`id_devoir`, `id_module`, `id_prof`, `titre`, `description`, `date_limite`, `date_creation`) VALUES
(1, 2, 2, 'test devoir', 'description test devoir', '2025-06-30', '2025-05-23 19:52:59');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id_document` bigint(20) UNSIGNED NOT NULL,
  `id_etudiant` bigint(20) UNSIGNED NOT NULL,
  `type_document` varchar(50) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `date_televersement` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_cours` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id_document`, `id_etudiant`, `type_document`, `chemin_fichier`, `titre`, `date_televersement`, `id_cours`) VALUES
(10, 1, 'cours_module_1', 'uploads/68310103c5383_B1 informatique seance 1.pptx', 'initiation à la programmation', '2025-05-23 23:13:07', 0);

-- --------------------------------------------------------

--
-- Table structure for table `exercices`
--

CREATE TABLE `exercices` (
  `id_exercice` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `id_prof` bigint(20) UNSIGNED DEFAULT NULL,
  `titre` varchar(200) NOT NULL,
  `instructions` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_articles`
--

CREATE TABLE `forum_articles` (
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_commentaires`
--

CREATE TABLE `forum_commentaires` (
  `commentaire_id` bigint(20) UNSIGNED NOT NULL,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code_module` varchar(20) NOT NULL,
  `nom_module` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id_module`, `class_id`, `code_module`, `nom_module`, `description`, `date_creation`) VALUES
(1, 2, 'PCC', 'programmation C', 'Module de Programmation en C Découvrez les fondamentaux du langage C grâce à notre module interactif. Apprenez la syntaxe, les structures avancées et pratiquez avec des exercices concrets. Que vous débutiez ou souhaitiez approfondir vos compétences, notre plateforme vous propose un apprentissage progressif et structuré. Rejoignez-nous pour maîtriser les bases du développement en C !', '2025-05-16 16:51:13'),
(2, 2, 'MATH', 'mathematiques', 'modulde de math avances', '2025-05-24 15:09:43');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id_note` bigint(20) UNSIGNED NOT NULL,
  `id_etudiant` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `nom_devoir` varchar(255) NOT NULL DEFAULT 'evaluation',
  `note` decimal(5,2) NOT NULL,
  `coefficient` int(11) NOT NULL,
  `date_attribution` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profs_modules`
--

CREATE TABLE `profs_modules` (
  `id_prof_module` bigint(20) UNSIGNED NOT NULL,
  `id_prof` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profs_modules`
--

INSERT INTO `profs_modules` (`id_prof_module`, `id_prof`, `id_module`) VALUES
(1, 36, 2);

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id_quiz` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `id_prof` bigint(20) UNSIGNED DEFAULT NULL,
  `titre` varchar(200) NOT NULL,
  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`questions`)),
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`id_quiz`, `id_module`, `id_prof`, `titre`, `questions`, `date_creation`) VALUES
(5, 1, 36, 'QUIZ _2', '[{\"text\":\"quelle est la difference entre la fonction et procedure\",\"type\":\"qcm\",\"answers\":[{\"text\":\"la fonction est plus sécurisé \",\"correct\":false},{\"text\":\"la fonction retourne un resultat\",\"correct\":true}]},{\"text\":\"C\'est quoi un boolen \",\"type\":\"qcm\",\"answers\":[{\"text\":\"une varible de boule \",\"correct\":false},{\"text\":\"une variable qui contient la valeur vraie ou fausse \",\"correct\":true}]}]', '2025-05-20 16:00:21'),
(10, 1, 1, 'quiz-1', '[{\"text\":\"quelle est la difference entre la fonction et procedure\",\"type\":\"qcm\",\"answers\":[{\"text\":\"la fonction est plus sécurisé \",\"correct\":false},{\"text\":\"la fonction retourne un resultat\",\"correct\":false},{\"text\":\"aucune différence \",\"correct\":false}]}]', '2025-05-23 14:52:56'),
(11, 1, 1, 'quiz_3', '[{\"text\":\"c\'est quoi une liste chainée \",\"type\":\"qcm\",\"answers\":[{\"text\":\"c\'est une liste de noeuds qui pointent vers le prochain neoud\",\"correct\":false},{\"text\":\"c\'est une chaine avec une liste ecrite dessus\",\"correct\":false}]}]', '2025-05-23 15:53:21'),
(12, 1, 36, 'sdcascdsda', '[{\"text\":\"dasdcddsc\",\"type\":\"qcm\",\"answers\":[{\"text\":\"asdcacda\",\"correct\":true},{\"text\":\"sadcacdas\",\"correct\":false},{\"text\":\"asdcascdsadc\",\"correct\":false}]}]', '2025-05-24 13:45:15'),
(13, 1, 36, 'defne', '[{\"text\":\"quelle est son annee preferee\",\"type\":\"qcm\",\"answers\":[{\"text\":\"1905\",\"correct\":false},{\"text\":\"1907\",\"correct\":true},{\"text\":\"2003\",\"correct\":false}]}]', '2025-05-24 14:51:26');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_resultats`
--

CREATE TABLE `quiz_resultats` (
  `id_resultat` bigint(20) UNSIGNED NOT NULL,
  `id_quiz` bigint(20) UNSIGNED NOT NULL,
  `id_etudiant` bigint(20) UNSIGNED NOT NULL,
  `score` int(11) NOT NULL,
  `temps_utilise` int(11) DEFAULT NULL,
  `date_passage` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_resultats`
--

INSERT INTO `quiz_resultats` (`id_resultat`, `id_quiz`, `id_etudiant`, `score`, `temps_utilise`, `date_passage`) VALUES
(1, 13, 37, 1, 0, '2025-05-24 16:34:45');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_visibilite`
--

CREATE TABLE `quiz_visibilite` (
  `id` int(11) NOT NULL,
  `id_quiz` bigint(20) UNSIGNED NOT NULL,
  `cible` enum('tous','classe','eleve') NOT NULL,
  `id_cible` bigint(20) UNSIGNED DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_visibilite`
--

INSERT INTO `quiz_visibilite` (`id`, `id_quiz`, `cible`, `id_cible`, `date_debut`, `date_fin`) VALUES
(7, 5, 'tous', NULL, '2025-05-23 19:13:00', '2025-05-23 20:13:00'),
(9, 10, 'tous', NULL, '2025-05-23 17:24:00', '2025-05-23 18:24:00'),
(12, 11, 'tous', NULL, '2025-05-24 13:03:00', '0000-00-00 00:00:00'),
(13, 12, 'tous', NULL, '2025-05-24 14:45:00', '0000-00-00 00:00:00'),
(15, 13, 'classe', 2, '2025-05-24 16:26:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `soumission`
--

CREATE TABLE `soumission` (
  `id_soumission` int(11) NOT NULL,
  `id_devoir` bigint(20) UNSIGNED NOT NULL,
  `id_etudiant` bigint(20) UNSIGNED NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_soumission` datetime NOT NULL DEFAULT current_timestamp(),
  `commentaire` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_classes`
--

CREATE TABLE `student_classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_classes`
--

INSERT INTO `student_classes` (`id`, `student_id`, `class_id`, `date_creation`) VALUES
(1, 34, 1, '2025-05-23 15:05:06'),
(2, 37, 2, '2025-05-24 12:04:33'),
(3, 38, 2, '2025-05-24 12:04:33'),
(4, 39, 2, '2025-05-24 12:04:34'),
(5, 40, 2, '2025-05-24 12:04:34'),
(6, 41, 2, '2025-05-24 12:04:34'),
(7, 42, 2, '2025-05-24 12:04:34'),
(8, 43, 2, '2025-05-24 12:04:34'),
(9, 44, 2, '2025-05-24 12:04:34'),
(10, 45, 2, '2025-05-24 12:04:34'),
(11, 46, 2, '2025-05-24 12:04:34'),
(12, 47, 2, '2025-05-24 12:04:34'),
(13, 48, 2, '2025-05-24 12:04:35'),
(14, 49, 2, '2025-05-24 12:04:35'),
(15, 50, 2, '2025-05-24 12:04:35'),
(16, 51, 2, '2025-05-24 12:04:35'),
(17, 52, 2, '2025-05-24 12:04:35'),
(18, 53, 2, '2025-05-24 12:04:35'),
(19, 54, 2, '2025-05-24 12:04:35'),
(20, 55, 2, '2025-05-24 12:04:35'),
(21, 56, 2, '2025-05-24 12:04:35'),
(22, 57, 2, '2025-05-24 12:04:36'),
(23, 58, 2, '2025-05-24 12:04:36'),
(24, 59, 2, '2025-05-24 12:04:36'),
(25, 60, 2, '2025-05-24 12:04:36'),
(26, 61, 2, '2025-05-24 12:04:36'),
(27, 62, 2, '2025-05-24 12:04:36'),
(28, 63, 2, '2025-05-24 12:04:36'),
(29, 64, 2, '2025-05-24 12:04:36'),
(30, 65, 2, '2025-05-24 12:04:36'),
(31, 66, 2, '2025-05-24 12:04:37');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `role` int(11) NOT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `sexe` varchar(10) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `mdp`, `email`, `prenom`, `nom`, `role`, `telephone`, `adresse`, `sexe`, `date_creation`) VALUES
(1, '$2y$10$UvSyrt7XTK5iR/6SpcNcT.F9QuwWh4TxHNYudybG9pPRKorhZ/K0W', 'mariottapro@gmail.com', 'Maria', 'MOKRANE', 2, '0775723864', '33 BOULEVARD GALLIENI\r\nSTUDEFI 2, BÂTIMENT B\r\nApparemment 2102', '', '2025-05-16 15:14:51'),
(2, '$2y$10$6ZNiaxaRrxFaPF02ox2uAunwmR4Pac.kda6vFR.rZZm/wqgWDhpRe', 'zaza@gmail.com', 'zaza', 'zaza', 2, '0678905412', 'AZAZA 23', '', '2025-05-16 15:45:08'),
(34, '$2y$10$FGE2ISsTE4WykiruZIbGKuFqf4qpdC8AI3Hjhrez6ksxlT8n4gLDy', 'samysmailpro@gmail.com', 'samy', 'Smail', 0, '0768688734', '33 BOULEVARD GALLIENI\r\nSTUDEFI 2, BÂTIMENT B\r\nApparemment 2102', '', '2025-05-23 15:05:06'),
(35, '$2y$10$i4omEYoQMANxaEe84yXaZerx.gUlsF7ZSGFkPwzaqQGCGc7shyoNy', 'rom1.lautard@gmail.com', 'romain', 'lautard', 2, '0666666666', '', '', '2025-05-24 11:57:09'),
(36, '$2y$10$99l3RiApCMZJpv2viHpcfO0xgqWAePKFNmdRXODIFWOUosjYq83Fy', 'rom2@gmail.com', 'romain', 'lautaadcascasrd', 1, '0666666666', '', '', '2025-05-24 11:58:33'),
(37, '$2y$10$044cTIYP0DSLV7LUVNeLLusO5V.FNOQwf9TSsXHooLcY34LXCw9v.', 'derek.martin@email.com', 'Derek', 'Martin', 0, '681-463-5993x52', '9978 Leslie Gardens Apt. 415, Lake Amyshire, IL 30698', '', '2025-05-24 12:04:33'),
(38, '$2y$10$YZRUnWbMpc4wGKG3lt/e1umVDgoc8wfKt1aZhIic7H1zgEZRvmeVW', 'cathy.brown@email.com', 'Cathy', 'Brown', 0, '(834)329-8453x6', '8272 Jessica Pike Apt. 711, North Williammouth, AK 70020', '', '2025-05-24 12:04:33'),
(39, '$2y$10$fYCv3G9W0SkaswrJ3ZGKGu/Q9artVf/KB3C.smIGGp3i8KTjTerAC', 'brandon.keller@email.com', 'Brandon', 'Keller', 0, '836-399-1830x24', '37495 Justin Ranch Apt. 838, Starkfurt, WV 36292', '', '2025-05-24 12:04:34'),
(40, '$2y$10$9ZqpPE7u18QvCm2b8W3s2ODWrCpSeEM3.s5M2GUwFjVgZ4QCSxu16', 'james.wright@email.com', 'James', 'Wright', 0, '+1-045-780-2213', '546 Megan Roads Apt. 812, Turnerview, NE 28658', '', '2025-05-24 12:04:34'),
(41, '$2y$10$Vdz0o/yyC80z8v7yoFt.UOJWxGqeXwgdCsRz0XNRahj6Bxdo7JZU2', 'thomas.hernandez@email.com', 'Thomas', 'Hernandez', 0, '(583)123-5702x8', '257 Hall Isle Apt. 998, Arthurside, AK 57713', '', '2025-05-24 12:04:34'),
(42, '$2y$10$hFUmFQaxyzRxAKPNnUVbEem2LMgpMoP.RiEmtVuRyKtQCuy6cb5IO', 'anna.thompson@email.com', 'Anna', 'Thompson', 0, '738.827.8710', '70799 Daniel Trafficway, New Derekhaven, CT 11932', '', '2025-05-24 12:04:34'),
(43, '$2y$10$rdPCktvZVDzbzU42S4Ljh.zMuhfbNtExuOr2fZWLU9DM9PgeAwVja', 'willie.johnson@email.com', 'Willie', 'Johnson', 0, '(406)831-7261x8', 'Unit 8150 Box 1441, DPO AE 11625', '', '2025-05-24 12:04:34'),
(44, '$2y$10$4b2zMJQe8/k.zWLcPRPKMuNglUaRdg/Epr3vXhf6/BPdmSQV.JuQW', 'katherine.bailey@email.com', 'Katherine', 'Bailey', 0, '+1-074-853-6567', '6188 Tiffany Cove Apt. 541, South Rachael, WY 78583', '', '2025-05-24 12:04:34'),
(45, '$2y$10$lhgib/vzEUGJgMXvtHAYH.CZK8f1i5Cl5qeDF0EIot4iTHgGhZYZC', 'casey.williams@email.com', 'Casey', 'Williams', 0, '832-536-5244x18', '598 Robles Avenue, West Williammouth, NM 62174', '', '2025-05-24 12:04:34'),
(46, '$2y$10$44mlv3utrVuAqbchXbswdeOGrIMk/0pEeif4kjyRAHZCp.hd93.CK', 'sarah.wilkins@email.com', 'Sarah', 'Wilkins', 0, '577-504-4522', '401 Barker Motorway Apt. 255, South Sarahport, TX 16199', '', '2025-05-24 12:04:34'),
(47, '$2y$10$p3pKkQuH42/mdAcNm2./nOm0qTYSfmBDWNf/tic0hUTlbIouLNECW', 'adam.blair@email.com', 'Adam', 'Blair', 0, '+1-396-463-2518', '7828 Robert Row Suite 712, Rebeccaberg, OK 95661', '', '2025-05-24 12:04:34'),
(48, '$2y$10$ljauj5Ce9U5ieBpev5D5guq5S1BVTdHK0vZn/9J6621hI2NQ4cmaK', 'lisa.bird@email.com', 'Lisa', 'Bird', 0, '(830)897-3706', '9649 Castro Terrace, North Deannaborough, NE 82713', '', '2025-05-24 12:04:35'),
(49, '$2y$10$f/4fbTYei979Hhac2NcqQexEH4kTi8jTCp1xhq.UCmkV1l9jnTgdO', 'cory.scott@email.com', 'Cory', 'Scott', 0, '243.955.4465x53', '717 Reynolds Key, Ricardoshire, CO 85429', '', '2025-05-24 12:04:35'),
(50, '$2y$10$iZEPFE.p7vg.App6.FKLUOqnlnfSeKARRX9x.AhMqyoDc5.RZ4Uvm', 'karen.henderson@email.com', 'Karen', 'Henderson', 0, '001-167-461-529', '70658 Eric Crossing Apt. 006, Wendyberg, GA 35391', '', '2025-05-24 12:04:35'),
(51, '$2y$10$v4fVgYkPoN5.3Ex4j.lC0eFbgdkTRyMEc2PLYXA6Lj8BXlcKRUtB6', 'michael.ward@email.com', 'Michael', 'Ward', 0, '731.993.9000x09', '549 Melinda Burgs Apt. 385, West Tammychester, WV 87422', '', '2025-05-24 12:04:35'),
(52, '$2y$10$BrlfInmOqTK6L7uGS1jgU.//JbO1bHiJx7.jM34jW3PHe1F23zFQ2', 'michael.williamson@email.com', 'Michael', 'Williamson', 0, '(832)895-2573x5', '2797 Warren Creek, Robinsonfort, AL 24559', '', '2025-05-24 12:04:35'),
(53, '$2y$10$NOH8yJVXHq2wlIZRntTjZevMrdAKsTBntmXbGQDEd6.W8exj2H4yi', 'melissa.simmons@email.com', 'Melissa', 'Simmons', 0, '(146)873-8541', '7583 Knapp Centers Apt. 566, East Joseph, MT 27715', '', '2025-05-24 12:04:35'),
(54, '$2y$10$gWGg.4YgDe.GfSSZcAWz8.Mz/mr/6qs1wZ65ZpNLVUwzIsEHtuIEC', 'kyle.wu@email.com', 'Kyle', 'Wu', 0, '(917)968-5133x2', '908 Brown Track Suite 471, Michaelstad, AK 93080', '', '2025-05-24 12:04:35'),
(55, '$2y$10$ZmSchwHxzEYvnwABQxW7iu/a9X78on8Y7.GLURid.VUQOIuzfuTZy', 'karen.hall@email.com', 'Karen', 'Hall', 0, '421-230-3389x16', '90819 Hudson Hills Suite 406, Adamsshire, PA 64300', '', '2025-05-24 12:04:35'),
(56, '$2y$10$ZPEiBdr6uujL18aXYwPTUu/sb6KOy3kXZrQgpECazfDIrk5VDmQLa', 'marc.patrick@email.com', 'Marc', 'Patrick', 0, '(924)542-9825', 'PSC 1817, Box 3373, APO AA 37830', '', '2025-05-24 12:04:35'),
(57, '$2y$10$Gw1ZUmBGqQNYl4gU6WOACu64nhJWJTpz4PbufAPsyRL7zDSxprFGe', 'kevin.smith@email.com', 'Kevin', 'Smith', 0, '001-882-075-139', '0675 Nelson Inlet Suite 659, Daltontown, MD 45707', '', '2025-05-24 12:04:36'),
(58, '$2y$10$zU3.ZFN1IDjksmsiihnCXetoI3fmGrKHcVcjH0/hxMdvl0ZXcFYQS', 'charles.parker@email.com', 'Charles', 'Parker', 0, '2209576052', '846 Reyes Ville, Hollandburgh, WA 59549', '', '2025-05-24 12:04:36'),
(59, '$2y$10$dh4ZkqjFW2bvJVf/AMP2euZz/4nyvAWItZOAasxwHNj0t35KlAq0.', 'samantha.pope@email.com', 'Samantha', 'Pope', 0, '(862)621-3206x9', '7127 Kayla Freeway Apt. 801, Castillobury, ND 55440', '', '2025-05-24 12:04:36'),
(60, '$2y$10$78ydC45p.KITAu1ACLYGCeNl.Fz45Mxy/xTtK/jj6mePhtGUO8dI2', 'andrea.scott@email.com', 'Andrea', 'Scott', 0, '400.996.9296', '7255 Miller Forks Apt. 646, New Stevenstad, NV 67104', '', '2025-05-24 12:04:36'),
(61, '$2y$10$Ftfci1HXd/SFhbgd1ziH1OYiYvpeUM/UUsyzr.aZV4o0Z/lcocD/C', 'elizabeth.doyle@email.com', 'Elizabeth', 'Doyle', 0, '1469465840', '53247 Jacqueline Forges, South Jason, DC 87253', '', '2025-05-24 12:04:36'),
(62, '$2y$10$5iQdPki18FFwh5TFwTfOmewhnDcc/2OD/UWLAhhGj/nallGFnYtzW', 'joseph.berry@email.com', 'Joseph', 'Berry', 0, '585.924.3137x54', '0370 Alice Parkway Suite 380, Port Frank, AL 45380', '', '2025-05-24 12:04:36'),
(63, '$2y$10$e0nczCPoC6yucSNRThz1D..qmnsdtNrQGa0V/oi7qNyfv3ijAhs2y', 'christine.villanueva@email.com', 'Christine', 'Villanueva', 0, '423-166-6311', '15413 Tina Brooks Suite 317, Lake Annahaven, LA 84275', '', '2025-05-24 12:04:36'),
(64, '$2y$10$KvM/zVhGgvN09Td82BkD7.6lZHe/kBP1JrnjQ2oUPpqLTCVX2S28S', 'richard.klein@email.com', 'Richard', 'Klein', 0, '(286)283-2659', '565 Rodriguez Throughway Apt. 911, Wilsonland, VA 06867', '', '2025-05-24 12:04:36'),
(65, '$2y$10$kg9/AbZ7jx1ZwZ8mxiRG.OLpeMkOuV4w8x2VaAC1cWllHToOCsASO', 'lisa.morris@email.com', 'Lisa', 'Morris', 0, '+1-507-725-1031', '698 Koch Inlet Suite 943, South Michael, RI 04714', '', '2025-05-24 12:04:36'),
(66, '$2y$10$hfLMyD2RY3ILCyOIrYmI9OjrmMUc6KMxhplcJzA96/l6DESttlcDu', 'billy.ramirez@email.com', 'Billy', 'Ramirez', 0, '024-181-2129x47', '0308 Potter Roads Suite 045, Fosterton, MA 20586', '', '2025-05-24 12:04:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `cours`
--
ALTER TABLE `cours`
  ADD PRIMARY KEY (`id_cours`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_prof` (`id_prof`);

--
-- Indexes for table `devoirs`
--
ALTER TABLE `devoirs`
  ADD PRIMARY KEY (`id_devoir`),
  ADD KEY `fk_devoirs_module` (`id_module`),
  ADD KEY `fk_devoirs_prof` (`id_prof`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id_document`),
  ADD KEY `id_etudiant` (`id_etudiant`);

--
-- Indexes for table `exercices`
--
ALTER TABLE `exercices`
  ADD PRIMARY KEY (`id_exercice`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_prof` (`id_prof`);

--
-- Indexes for table `forum_articles`
--
ALTER TABLE `forum_articles`
  ADD PRIMARY KEY (`article_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `forum_commentaires`
--
ALTER TABLE `forum_commentaires`
  ADD PRIMARY KEY (`commentaire_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id_module`),
  ADD UNIQUE KEY `code_module` (`code_module`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id_note`),
  ADD KEY `id_module` (`id_module`);

--
-- Indexes for table `profs_modules`
--
ALTER TABLE `profs_modules`
  ADD PRIMARY KEY (`id_prof_module`),
  ADD UNIQUE KEY `prof_module_unique` (`id_prof`,`id_module`),
  ADD KEY `id_module` (`id_module`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id_quiz`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_prof` (`id_prof`);

--
-- Indexes for table `quiz_resultats`
--
ALTER TABLE `quiz_resultats`
  ADD PRIMARY KEY (`id_resultat`),
  ADD UNIQUE KEY `unique_quiz_etudiant` (`id_quiz`,`id_etudiant`);

--
-- Indexes for table `quiz_visibilite`
--
ALTER TABLE `quiz_visibilite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_quiz` (`id_quiz`);

--
-- Indexes for table `soumission`
--
ALTER TABLE `soumission`
  ADD PRIMARY KEY (`id_soumission`),
  ADD UNIQUE KEY `id_devoir` (`id_devoir`,`id_etudiant`),
  ADD KEY `id_etudiant` (`id_etudiant`);

--
-- Indexes for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_class_unique` (`student_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cours`
--
ALTER TABLE `cours`
  MODIFY `id_cours` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `devoirs`
--
ALTER TABLE `devoirs`
  MODIFY `id_devoir` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id_document` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `exercices`
--
ALTER TABLE `exercices`
  MODIFY `id_exercice` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_articles`
--
ALTER TABLE `forum_articles`
  MODIFY `article_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_commentaires`
--
ALTER TABLE `forum_commentaires`
  MODIFY `commentaire_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id_module` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id_note` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `profs_modules`
--
ALTER TABLE `profs_modules`
  MODIFY `id_prof_module` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id_quiz` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `quiz_resultats`
--
ALTER TABLE `quiz_resultats`
  MODIFY `id_resultat` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quiz_visibilite`
--
ALTER TABLE `quiz_visibilite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `soumission`
--
ALTER TABLE `soumission`
  MODIFY `id_soumission` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_classes`
--
ALTER TABLE `student_classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cours`
--
ALTER TABLE `cours`
  ADD CONSTRAINT `cours_ibfk_1` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  ADD CONSTRAINT `cours_ibfk_2` FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

--
-- Constraints for table `devoirs`
--
ALTER TABLE `devoirs`
  ADD CONSTRAINT `fk_devoirs_module` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_devoirs_prof` FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `exercices`
--
ALTER TABLE `exercices`
  ADD CONSTRAINT `exercices_ibfk_1` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  ADD CONSTRAINT `exercices_ibfk_2` FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

--
-- Constraints for table `forum_articles`
--
ALTER TABLE `forum_articles`
  ADD CONSTRAINT `forum_articles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `forum_commentaires`
--
ALTER TABLE `forum_commentaires`
  ADD CONSTRAINT `forum_commentaires_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `forum_articles` (`article_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_commentaires_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL;

--
-- Constraints for table `profs_modules`
--
ALTER TABLE `profs_modules`
  ADD CONSTRAINT `profs_modules_ibfk_1` FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `profs_modules_ibfk_2` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE;

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_ibfk_2` FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

--
-- Constraints for table `quiz_visibilite`
--
ALTER TABLE `quiz_visibilite`
  ADD CONSTRAINT `quiz_visibilite_ibfk_1` FOREIGN KEY (`id_quiz`) REFERENCES `quiz` (`id_quiz`) ON DELETE CASCADE;

--
-- Constraints for table `soumission`
--
ALTER TABLE `soumission`
  ADD CONSTRAINT `soumission_ibfk_1` FOREIGN KEY (`id_devoir`) REFERENCES `devoirs` (`id_devoir`) ON DELETE CASCADE,
  ADD CONSTRAINT `soumission_ibfk_2` FOREIGN KEY (`id_etudiant`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
