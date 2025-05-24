-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 24 mai 2025 à 17:15
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `student_five`
--

-- --------------------------------------------------------

--
-- Structure de la table `classes`
--

CREATE TABLE `classes` (
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `annee_scolaire` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`, `description`, `annee_scolaire`, `created_at`) VALUES
(1, 'Nouvelle classe', 'description', '24/25', '2025-05-21 08:01:23'),
(2, 'Testent classe', 'description de testent classse', '2425', '2025-05-23 17:37:10');

-- --------------------------------------------------------

--
-- Structure de la table `cours`
--

CREATE TABLE `cours` (
  `id_cours` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `id_prof` bigint(20) UNSIGNED DEFAULT NULL,
  `titre` varchar(200) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_cours` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cours`
--

INSERT INTO `cours` (`id_cours`, `id_module`, `id_prof`, `titre`, `contenu`, `date_creation`, `date_cours`, `heure_debut`, `heure_fin`) VALUES
(1, 1, 2, 'Titre', '', '2025-05-20 16:32:50', '2025-05-22', '09:30:00', '12:30:00'),
(2, 1, 2, 'test eleve@gmail', '', '2025-05-21 08:06:34', '2025-05-22', '11:00:00', '12:00:00'),
(12, 1, 1, 'initiation à la programmation', 'Le premier cours d\'initiation à la programmation en C vise à introduire les concepts fondamentaux du langage. Il couvre généralement les bases de la programmation, la structure d\'un programme C, les types de données, les variables, et les opérations simples. On y apprend également la syntaxe, l\'utilisation des bibliothèques standard, et les premières fonctions essentielles comme l\'affichage avec printf() et la lecture d\'entrée avec scanf(). Ce cours met l\'accent sur la logique algorithmique et la compréhension des principes de la programmation structurée. Un bon point de départ pour se lancer dans le développement logiciel !', '2025-05-23 23:13:07', '0000-00-00', '00:00:00', '00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `devoirs`
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
-- Déchargement des données de la table `devoirs`
--

INSERT INTO `devoirs` (`id_devoir`, `id_module`, `id_prof`, `titre`, `description`, `date_limite`, `date_creation`) VALUES
(1, 2, 2, 'test devoir', 'description test devoir', '2025-06-30', '2025-05-23 19:52:59');

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `id_document` bigint(20) UNSIGNED NOT NULL,
  `id_etudiant` bigint(20) UNSIGNED NOT NULL,
  `type_document` varchar(50) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_televersement` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`id_document`, `id_etudiant`, `type_document`, `chemin_fichier`, `date_televersement`) VALUES
(1, 1, 'module_doc_2', 'uploads/kaido_tete.bmp', '2025-05-21 08:43:46');

-- --------------------------------------------------------

--
-- Structure de la table `exercices`
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
-- Structure de la table `forum_articles`
--

CREATE TABLE `forum_articles` (
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `forum_articles`
--

INSERT INTO `forum_articles` (`article_id`, `user_id`, `titre`, `contenu`, `date_creation`) VALUES
(1, 2, 'Test', 'testons', '2025-05-21 07:41:48');

-- --------------------------------------------------------

--
-- Structure de la table `forum_commentaires`
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
-- Structure de la table `modules`
--

CREATE TABLE `modules` (
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code_module` varchar(20) NOT NULL,
  `nom_module` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `prof_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `modules`
--

INSERT INTO `modules` (`id_module`, `class_id`, `code_module`, `nom_module`, `description`, `date_creation`, `prof_id`) VALUES
(1, NULL, 'code', 'nom', 'description', '2025-05-20 16:15:50', NULL),
(2, 1, 'code du module', 'nom du module', 'description du module', '2025-05-21 08:18:14', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `notes`
--

CREATE TABLE `notes` (
  `id_note` bigint(20) UNSIGNED NOT NULL,
  `id_etudiant` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `note` decimal(5,2) NOT NULL,
  `date_attribution` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `profs_modules`
--

CREATE TABLE `profs_modules` (
  `id_prof_module` bigint(20) UNSIGNED NOT NULL,
  `id_prof` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quiz`
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
-- Déchargement des données de la table `quiz`
--

INSERT INTO `quiz` (`id_quiz`, `id_module`, `id_prof`, `titre`, `questions`, `date_creation`) VALUES
(5, 1, 1, 'QUIZ _2', '[{\"text\":\"quelle est la difference entre la fonction et procedure\",\"type\":\"qcm\",\"answers\":[{\"text\":\"la fonction est plus sécurisé \",\"correct\":false},{\"text\":\"la fonction retourne un resultat\",\"correct\":true}]},{\"text\":\"C\'est quoi un boolen \",\"type\":\"qcm\",\"answers\":[{\"text\":\"une varible de boule \",\"correct\":false},{\"text\":\"une variable qui contient la valeur vraie ou fausse \",\"correct\":true}]}]', '2025-05-20 16:00:21'),
(10, 1, 1, 'quiz-1', '[{\"text\":\"quelle est la difference entre la fonction et procedure\",\"type\":\"qcm\",\"answers\":[{\"text\":\"la fonction est plus sécurisé \",\"correct\":false},{\"text\":\"la fonction retourne un resultat\",\"correct\":false},{\"text\":\"aucune différence \",\"correct\":false}]}]', '2025-05-23 14:52:56'),
(12, 1, 2, 'quizze', '[{\"text\":\"Derek\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Martin\",\"correct\":false},{\"text\":\"681-463-5993x52362\",\"correct\":false}]},{\"text\":\"Cathy\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Brown\",\"correct\":false},{\"text\":\"(834)329-8453x668\",\"correct\":false}]},{\"text\":\"Brandon\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Keller\",\"correct\":false},{\"text\":\"836-399-1830x240\",\"correct\":false}]},{\"text\":\"James\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Wright\",\"correct\":false},{\"text\":\"+1-045-780-2213\",\"correct\":false}]},{\"text\":\"Thomas\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Hernandez\",\"correct\":false},{\"text\":\"(583)123-5702x8315\",\"correct\":false}]},{\"text\":\"Anna\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Thompson\",\"correct\":false},{\"text\":\"738.827.8710\",\"correct\":false}]},{\"text\":\"Willie\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Johnson\",\"correct\":false},{\"text\":\"(406)831-7261x83053\",\"correct\":false}]},{\"text\":\"Katherine\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Bailey\",\"correct\":false},{\"text\":\"+1-074-853-6567x338\",\"correct\":false}]},{\"text\":\"Casey\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Williams\",\"correct\":false},{\"text\":\"832-536-5244x1808\",\"correct\":false}]},{\"text\":\"Sarah\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Wilkins\",\"correct\":false},{\"text\":\"577-504-4522\",\"correct\":false}]},{\"text\":\"Adam\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Blair\",\"correct\":false},{\"text\":\"+1-396-463-2518\",\"correct\":false}]},{\"text\":\"Lisa\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Bird\",\"correct\":false},{\"text\":\"(830)897-3706\",\"correct\":false}]},{\"text\":\"Cory\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Scott\",\"correct\":false},{\"text\":\"243.955.4465x530\",\"correct\":false}]},{\"text\":\"Karen\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Henderson\",\"correct\":false},{\"text\":\"001-167-461-5291x81358\",\"correct\":false}]},{\"text\":\"Michael\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Ward\",\"correct\":false},{\"text\":\"731.993.9000x0998\",\"correct\":false}]},{\"text\":\"Michael\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Williamson\",\"correct\":false},{\"text\":\"(832)895-2573x5093\",\"correct\":false}]},{\"text\":\"Melissa\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Simmons\",\"correct\":false},{\"text\":\"(146)873-8541\",\"correct\":false}]},{\"text\":\"Kyle\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Wu\",\"correct\":false},{\"text\":\"(917)968-5133x299\",\"correct\":false}]},{\"text\":\"Karen\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Hall\",\"correct\":false},{\"text\":\"421-230-3389x16929\",\"correct\":false}]},{\"text\":\"Marc\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Patrick\",\"correct\":false},{\"text\":\"(924)542-9825\",\"correct\":false}]},{\"text\":\"Kevin\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Smith\",\"correct\":false},{\"text\":\"001-882-075-1399x617\",\"correct\":false}]},{\"text\":\"Charles\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Parker\",\"correct\":false},{\"text\":\"2209576052\",\"correct\":false}]},{\"text\":\"Samantha\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Pope\",\"correct\":false},{\"text\":\"(862)621-3206x958\",\"correct\":false}]},{\"text\":\"Andrea\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Scott\",\"correct\":false},{\"text\":\"400.996.9296\",\"correct\":false}]},{\"text\":\"Elizabeth\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Doyle\",\"correct\":false},{\"text\":\"1469465840\",\"correct\":false}]},{\"text\":\"Joseph\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Berry\",\"correct\":false},{\"text\":\"585.924.3137x5458\",\"correct\":false}]},{\"text\":\"Christine\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Villanueva\",\"correct\":false},{\"text\":\"423-166-6311\",\"correct\":false}]},{\"text\":\"Richard\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Klein\",\"correct\":false},{\"text\":\"(286)283-2659\",\"correct\":false}]},{\"text\":\"Lisa\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Morris\",\"correct\":false},{\"text\":\"+1-507-725-1031\",\"correct\":false}]},{\"text\":\"Billy\",\"type\":\"qcm\",\"answers\":[{\"text\":\"Ramirez\",\"correct\":false},{\"text\":\"024-181-2129x47076\",\"correct\":false}]}]', '2025-05-24 15:13:21');

-- --------------------------------------------------------

--
-- Structure de la table `quiz_resultats`
--

CREATE TABLE `quiz_resultats` (
  `id_resultat` bigint(20) UNSIGNED NOT NULL,
  `id_quiz` bigint(20) UNSIGNED NOT NULL,
  `id_etudiant` bigint(20) UNSIGNED NOT NULL,
  `score` int(11) NOT NULL,
  `temps_utilise` int(11) NOT NULL,
  `date_passage` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quiz_visibilite`
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
-- Déchargement des données de la table `quiz_visibilite`
--

INSERT INTO `quiz_visibilite` (`id`, `id_quiz`, `cible`, `id_cible`, `date_debut`, `date_fin`) VALUES
(7, 5, 'tous', NULL, '2025-05-23 19:13:00', '2025-05-23 20:13:00'),
(9, 10, 'tous', NULL, '2025-05-23 17:24:00', '2025-05-23 18:24:00'),
(11, 11, 'tous', NULL, '2025-05-25 00:40:00', '2025-05-27 04:39:00');

-- --------------------------------------------------------

--
-- Structure de la table `soumission`
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
-- Structure de la table `student_classes`
--

CREATE TABLE `student_classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `date-creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `student_classes`
--

INSERT INTO `student_classes` (`id`, `student_id`, `class_id`, `date-creation`) VALUES
(1, 3, 1, '2025-05-21 08:01:23'),
(2, 4, 2, '2025-05-23 17:37:11'),
(3, 5, 2, '2025-05-23 17:37:11'),
(4, 6, 2, '2025-05-23 17:37:11'),
(5, 7, 2, '2025-05-23 17:37:11'),
(6, 8, 2, '2025-05-23 17:37:11'),
(7, 9, 2, '2025-05-23 17:37:11'),
(8, 10, 2, '2025-05-23 17:37:11'),
(9, 11, 2, '2025-05-23 17:37:11'),
(10, 12, 2, '2025-05-23 17:37:11'),
(11, 13, 2, '2025-05-23 17:37:11'),
(12, 14, 2, '2025-05-23 17:37:11'),
(13, 15, 2, '2025-05-23 17:37:11'),
(14, 16, 2, '2025-05-23 17:37:11'),
(15, 17, 2, '2025-05-23 17:37:11'),
(16, 18, 2, '2025-05-23 17:37:11'),
(17, 19, 2, '2025-05-23 17:37:11'),
(18, 20, 2, '2025-05-23 17:37:11'),
(19, 21, 2, '2025-05-23 17:37:11'),
(20, 22, 2, '2025-05-23 17:37:12'),
(21, 23, 2, '2025-05-23 17:37:12'),
(22, 24, 2, '2025-05-23 17:37:12'),
(23, 25, 2, '2025-05-23 17:37:12'),
(24, 26, 2, '2025-05-23 17:37:12'),
(25, 27, 2, '2025-05-23 17:37:12'),
(26, 28, 2, '2025-05-23 17:37:12'),
(27, 29, 2, '2025-05-23 17:37:12'),
(28, 30, 2, '2025-05-23 17:37:12'),
(29, 31, 2, '2025-05-23 17:37:12'),
(30, 32, 2, '2025-05-23 17:37:12'),
(31, 33, 2, '2025-05-23 17:37:12');

-- --------------------------------------------------------

--
-- Structure de la table `user`
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
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id_user`, `mdp`, `email`, `prenom`, `nom`, `role`, `telephone`, `adresse`, `sexe`, `date_creation`) VALUES
(1, '$2y$10$Oo6QlMrIRTfOrO1CNf.8G.gyxwUG65ankneRb4vsxkQ/7P0M7uWmS', 'admin@gmail.com', 'Admin', 'Antoine', 2, '0606060606', 'zaefegrnh', '', '2025-05-20 16:13:32'),
(2, '$2y$10$DuGjsuKy3arhGUNrt1xIVO.PnwO61FU7/dPh7o70a1Psg.UJ2m2PK', 'prof@gmail.com', 'Prof', 'Antoine', 1, '0606060606', 'ztrgnht', '', '2025-05-20 16:31:55'),
(3, '$2y$10$IVE58TBlMwi0uzl5oGBjIe2bE3Ej3YdI4z5nrfflWqLZbKt5XIsGy', 'eleve@gmail.com', 'Eleve', 'Antoine', 0, '0606060606', 'adresse', '', '2025-05-21 08:01:23'),
(4, '$2y$10$.rTo.R5VLyDCfrvzUm4LgOd5vqhLxe3Eo7RgtpLxLFAulM.GqfOEq', 'derek.martin@email.com', 'Derek', 'Martin', 0, '681-463-5993x52', '9978 Leslie Gardens Apt. 415, Lake Amyshire, IL 30698', '', '2025-05-23 17:37:11'),
(5, '$2y$10$E2Ou/.n7ymcHpbBH4IDL3uSWO98slWc/JarivK7arKs8KaZqDurRG', 'cathy.brown@email.com', 'Cathy', 'Brown', 0, '(834)329-8453x6', '8272 Jessica Pike Apt. 711, North Williammouth, AK 70020', '', '2025-05-23 17:37:11'),
(6, '$2y$10$dMRGjdyE8XikyugTMLPEH.8Y/IES3DxTY4puURWpVKgUoBLS4Kshu', 'brandon.keller@email.com', 'Brandon', 'Keller', 0, '836-399-1830x24', '37495 Justin Ranch Apt. 838, Starkfurt, WV 36292', '', '2025-05-23 17:37:11'),
(7, '$2y$10$aseQkxnnNrhzHSBHZuibUelZdqiJ9y3hx0vqjDBvdiX/xrFpSwTGC', 'james.wright@email.com', 'James', 'Wright', 0, '+1-045-780-2213', '546 Megan Roads Apt. 812, Turnerview, NE 28658', '', '2025-05-23 17:37:11'),
(8, '$2y$10$z9hPahYS9tfiG9N2lhM2V.41SbavOx52Anw2miv.269nUL6oiYq.G', 'thomas.hernandez@email.com', 'Thomas', 'Hernandez', 0, '(583)123-5702x8', '257 Hall Isle Apt. 998, Arthurside, AK 57713', '', '2025-05-23 17:37:11'),
(9, '$2y$10$gB/goc.k7Hi4p6ox8aIR8ekawIEIuODYm1g0Smk5hcqdhlblWabk.', 'anna.thompson@email.com', 'Anna', 'Thompson', 0, '738.827.8710', '70799 Daniel Trafficway, New Derekhaven, CT 11932', '', '2025-05-23 17:37:11'),
(10, '$2y$10$jecY3uKHsmnFuYJm8HW2AewVvkfucen4R1wA26rZ8U7cwWdWZTYHm', 'willie.johnson@email.com', 'Willie', 'Johnson', 0, '(406)831-7261x8', 'Unit 8150 Box 1441, DPO AE 11625', '', '2025-05-23 17:37:11'),
(11, '$2y$10$p9yCZ3gxNmT7vqUHiV3OlucauxY54nuOuOEGR3k23ZLsACOHUGoZS', 'katherine.bailey@email.com', 'Katherine', 'Bailey', 0, '+1-074-853-6567', '6188 Tiffany Cove Apt. 541, South Rachael, WY 78583', '', '2025-05-23 17:37:11'),
(12, '$2y$10$18WjUNNSFzcpX1J9xxiQdeT2K2hd..bKzGwxcPNxbCNAszyABuWBW', 'casey.williams@email.com', 'Casey', 'Williams', 0, '832-536-5244x18', '598 Robles Avenue, West Williammouth, NM 62174', '', '2025-05-23 17:37:11'),
(13, '$2y$10$/T80ZX2iYvqI.CH08s7MPusSv6aJoBK20nvV0hhfAR5bfBmXNB5Ra', 'sarah.wilkins@email.com', 'Sarah', 'Wilkins', 0, '577-504-4522', '401 Barker Motorway Apt. 255, South Sarahport, TX 16199', '', '2025-05-23 17:37:11'),
(14, '$2y$10$418aAOcyWAM5aBCenvOpBOslzwJSIflWrhVjVcjniWeV6t5HdwzjW', 'adam.blair@email.com', 'Adam', 'Blair', 0, '+1-396-463-2518', '7828 Robert Row Suite 712, Rebeccaberg, OK 95661', '', '2025-05-23 17:37:11'),
(15, '$2y$10$X5tIYf3HeZcuCukTF.6aOO/vVpd5xIvSQvrAXv9/HgX6S8DP8m8Ju', 'lisa.bird@email.com', 'Lisa', 'Bird', 0, '(830)897-3706', '9649 Castro Terrace, North Deannaborough, NE 82713', '', '2025-05-23 17:37:11'),
(16, '$2y$10$/1Ss.O5G.CoAx5FQPqg1TOw1A.aJkDEZIpuBbuOkSZq5j6Nsoq0ia', 'cory.scott@email.com', 'Cory', 'Scott', 0, '243.955.4465x53', '717 Reynolds Key, Ricardoshire, CO 85429', '', '2025-05-23 17:37:11'),
(17, '$2y$10$tDeVBCF8RGaYfd7h6z1qb.kQcGFcD7uTm7D0FztsmLw07quqjSQZ.', 'karen.henderson@email.com', 'Karen', 'Henderson', 0, '001-167-461-529', '70658 Eric Crossing Apt. 006, Wendyberg, GA 35391', '', '2025-05-23 17:37:11'),
(18, '$2y$10$.EKZMmD6FvLUe/UiiKJQZ.UFUDVWhZf/AnmGUKLZKuYtdNwBblPi.', 'michael.ward@email.com', 'Michael', 'Ward', 0, '731.993.9000x09', '549 Melinda Burgs Apt. 385, West Tammychester, WV 87422', '', '2025-05-23 17:37:11'),
(19, '$2y$10$nLFU.wjT/he/pjBZyY2DguyK00ImDBKhWUcOKRrQGSyf/.sqvTyb6', 'michael.williamson@email.com', 'Michael', 'Williamson', 0, '(832)895-2573x5', '2797 Warren Creek, Robinsonfort, AL 24559', '', '2025-05-23 17:37:11'),
(20, '$2y$10$PM8KahcA/70PyQO/MJ.rA.yQ8/MRWrTDZgC.fdb7mUnq0i5Tea4qK', 'melissa.simmons@email.com', 'Melissa', 'Simmons', 0, '(146)873-8541', '7583 Knapp Centers Apt. 566, East Joseph, MT 27715', '', '2025-05-23 17:37:11'),
(21, '$2y$10$GH2a6STxDcT9NEJRea8JLOTnCLNhC/Uu4fZqw679vT2qlM9ldfDbW', 'kyle.wu@email.com', 'Kyle', 'Wu', 0, '(917)968-5133x2', '908 Brown Track Suite 471, Michaelstad, AK 93080', '', '2025-05-23 17:37:11'),
(22, '$2y$10$mUDQt4C0ivUK/a2W4fjEwuWgHQv5eEL4Q7SD7yiokFUkH1Jv7AkjW', 'karen.hall@email.com', 'Karen', 'Hall', 0, '421-230-3389x16', '90819 Hudson Hills Suite 406, Adamsshire, PA 64300', '', '2025-05-23 17:37:12'),
(23, '$2y$10$p5KSnhNUOOkUASE1bnV96.EcTEkz16W32ipmP0bsVR4CJZPzIJGuS', 'marc.patrick@email.com', 'Marc', 'Patrick', 0, '(924)542-9825', 'PSC 1817, Box 3373, APO AA 37830', '', '2025-05-23 17:37:12'),
(24, '$2y$10$STtR61TnR5YYoYtV6rW54u3phA1F/RJj2uOzVIQgcIMKP3rmVNchK', 'kevin.smith@email.com', 'Kevin', 'Smith', 0, '001-882-075-139', '0675 Nelson Inlet Suite 659, Daltontown, MD 45707', '', '2025-05-23 17:37:12'),
(25, '$2y$10$bod7ULQAZ0bIWrNszqyN9ef4TqrjgNNKarAyY7dQ1bzYmy8o00Cry', 'charles.parker@email.com', 'Charles', 'Parker', 0, '2209576052', '846 Reyes Ville, Hollandburgh, WA 59549', '', '2025-05-23 17:37:12'),
(26, '$2y$10$FT2yWPrXweCkHDYfPKRHsudoKjqkuHkBSAz/xBdDgRx/t/AR0nYMi', 'samantha.pope@email.com', 'Samantha', 'Pope', 0, '(862)621-3206x9', '7127 Kayla Freeway Apt. 801, Castillobury, ND 55440', '', '2025-05-23 17:37:12'),
(27, '$2y$10$83YE2j1sSIhJ2XpgOGyUy.eNX5mwxXrEdx1s1Phb16I5YLcgO6vPq', 'andrea.scott@email.com', 'Andrea', 'Scott', 0, '400.996.9296', '7255 Miller Forks Apt. 646, New Stevenstad, NV 67104', '', '2025-05-23 17:37:12'),
(28, '$2y$10$vJGAPghn4Jl8vEnjyjNpkuiuwcsTmWMZYiZJg1YjbfnSnkvzZdkUC', 'elizabeth.doyle@email.com', 'Elizabeth', 'Doyle', 0, '1469465840', '53247 Jacqueline Forges, South Jason, DC 87253', '', '2025-05-23 17:37:12'),
(29, '$2y$10$a3.M8vQmB2lV1/2qL9ByEO.k07Kc7nxShosR4VX0VvIu3qNNfCD/6', 'joseph.berry@email.com', 'Joseph', 'Berry', 0, '585.924.3137x54', '0370 Alice Parkway Suite 380, Port Frank, AL 45380', '', '2025-05-23 17:37:12'),
(30, '$2y$10$SH0yhjfrbTwAwXHmOZE4eeqGGmA0V0pMUuC2MVfJap5ny7kQ4aJGS', 'christine.villanueva@email.com', 'Christine', 'Villanueva', 0, '423-166-6311', '15413 Tina Brooks Suite 317, Lake Annahaven, LA 84275', '', '2025-05-23 17:37:12'),
(31, '$2y$10$BGbwI3snmTRJKXux4WHowuu.xzZnoS2TJ9Bd1p4Pi1VlSeZOkeCi6', 'richard.klein@email.com', 'Richard', 'Klein', 0, '(286)283-2659', '565 Rodriguez Throughway Apt. 911, Wilsonland, VA 06867', '', '2025-05-23 17:37:12'),
(32, '$2y$10$EgLMAoSwX2REthXSeIQuneDcBe/UqRfhVBXPve589fxZfBv5NF8Du', 'lisa.morris@email.com', 'Lisa', 'Morris', 0, '+1-507-725-1031', '698 Koch Inlet Suite 943, South Michael, RI 04714', '', '2025-05-23 17:37:12'),
(33, '$2y$10$aXSc55RsiZasdpmDyinp9.r6WrboFmcKomvVPZIusmZBC.8ZNG65S', 'billy.ramirez@email.com', 'Billy', 'Ramirez', 0, '024-181-2129x47', '0308 Potter Roads Suite 045, Fosterton, MA 20586', '', '2025-05-23 17:37:12');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`);

--
-- Index pour la table `cours`
--
ALTER TABLE `cours`
  ADD PRIMARY KEY (`id_cours`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_prof` (`id_prof`);

--
-- Index pour la table `devoirs`
--
ALTER TABLE `devoirs`
  ADD PRIMARY KEY (`id_devoir`),
  ADD KEY `fk_devoirs_module` (`id_module`),
  ADD KEY `fk_devoirs_prof` (`id_prof`);

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id_document`),
  ADD KEY `id_etudiant` (`id_etudiant`);

--
-- Index pour la table `exercices`
--
ALTER TABLE `exercices`
  ADD PRIMARY KEY (`id_exercice`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_prof` (`id_prof`);

--
-- Index pour la table `forum_articles`
--
ALTER TABLE `forum_articles`
  ADD PRIMARY KEY (`article_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `forum_commentaires`
--
ALTER TABLE `forum_commentaires`
  ADD PRIMARY KEY (`commentaire_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id_module`),
  ADD UNIQUE KEY `code_module` (`code_module`),
  ADD KEY `class_id` (`class_id`);

--
-- Index pour la table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id_note`),
  ADD UNIQUE KEY `etudiant_module_unique` (`id_etudiant`,`id_module`),
  ADD KEY `id_module` (`id_module`);

--
-- Index pour la table `profs_modules`
--
ALTER TABLE `profs_modules`
  ADD PRIMARY KEY (`id_prof_module`),
  ADD UNIQUE KEY `prof_module_unique` (`id_prof`,`id_module`),
  ADD KEY `id_module` (`id_module`);

--
-- Index pour la table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id_quiz`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_prof` (`id_prof`);

--
-- Index pour la table `quiz_resultats`
--
ALTER TABLE `quiz_resultats`
  ADD PRIMARY KEY (`id_resultat`),
  ADD UNIQUE KEY `unique_quiz_etudiant` (`id_quiz`,`id_etudiant`);

--
-- Index pour la table `quiz_visibilite`
--
ALTER TABLE `quiz_visibilite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_quiz` (`id_quiz`);

--
-- Index pour la table `soumission`
--
ALTER TABLE `soumission`
  ADD PRIMARY KEY (`id_soumission`),
  ADD UNIQUE KEY `id_devoir` (`id_devoir`,`id_etudiant`),
  ADD KEY `id_etudiant` (`id_etudiant`);

--
-- Index pour la table `student_classes`
--
ALTER TABLE `student_classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_class_unique` (`student_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `cours`
--
ALTER TABLE `cours`
  MODIFY `id_cours` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `devoirs`
--
ALTER TABLE `devoirs`
  MODIFY `id_devoir` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `documents`
--
ALTER TABLE `documents`
  MODIFY `id_document` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `exercices`
--
ALTER TABLE `exercices`
  MODIFY `id_exercice` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `forum_articles`
--
ALTER TABLE `forum_articles`
  MODIFY `article_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `forum_commentaires`
--
ALTER TABLE `forum_commentaires`
  MODIFY `commentaire_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `modules`
--
ALTER TABLE `modules`
  MODIFY `id_module` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `notes`
--
ALTER TABLE `notes`
  MODIFY `id_note` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `profs_modules`
--
ALTER TABLE `profs_modules`
  MODIFY `id_prof_module` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id_quiz` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `soumission`
--
ALTER TABLE `soumission`
  MODIFY `id_soumission` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `student_classes`
--
ALTER TABLE `student_classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `cours`
--
ALTER TABLE `cours`
  ADD CONSTRAINT `cours_ibfk_1` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  ADD CONSTRAINT `cours_ibfk_2` FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

--
-- Contraintes pour la table `devoirs`
--
ALTER TABLE `devoirs`
  ADD CONSTRAINT `fk_devoirs_module` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_devoirs_prof` FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`);

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `exercices`
--
ALTER TABLE `exercices`
  ADD CONSTRAINT `exercices_ibfk_1` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  ADD CONSTRAINT `exercices_ibfk_2` FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

--
-- Contraintes pour la table `forum_articles`
--
ALTER TABLE `forum_articles`
  ADD CONSTRAINT `forum_articles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `forum_commentaires`
--
ALTER TABLE `forum_commentaires`
  ADD CONSTRAINT `forum_commentaires_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `forum_articles` (`article_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_commentaires_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE;

--
-- Contraintes pour la table `profs_modules`
--
ALTER TABLE `profs_modules`
  ADD CONSTRAINT `profs_modules_ibfk_1` FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `profs_modules_ibfk_2` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE;

--
-- Contraintes pour la table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_ibfk_2` FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

--
-- Contraintes pour la table `soumission`
--
ALTER TABLE `soumission`
  ADD CONSTRAINT `soumission_ibfk_1` FOREIGN KEY (`id_devoir`) REFERENCES `devoirs` (`id_devoir`) ON DELETE CASCADE,
  ADD CONSTRAINT `soumission_ibfk_2` FOREIGN KEY (`id_etudiant`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `student_classes`
--
ALTER TABLE `student_classes`
  ADD CONSTRAINT `student_classes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
