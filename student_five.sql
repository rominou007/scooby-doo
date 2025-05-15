-- Suppression de la base si elle existe et création
DROP DATABASE IF EXISTS nouvelle_base;
CREATE DATABASE nouvelle_base CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE nouvelle_base;

-- Suppression des tables existantes si elles existent (ordre inverse des dépendances)
DROP TABLE IF EXISTS `notes`;
DROP TABLE IF EXISTS `documents`;
DROP TABLE IF EXISTS `quiz`;
DROP TABLE IF EXISTS `exercices`;
DROP TABLE IF EXISTS `cours`;
DROP TABLE IF EXISTS `profs_modules`;
DROP TABLE IF EXISTS `modules`;
DROP TABLE IF EXISTS `user`;

-- Table structure pour table `user`
CREATE TABLE `user` (
  `id_user` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom_user` varchar(50) NOT NULL UNIQUE,
  `mdp` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `prenom` varchar(50) DEFAULT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `role` int(11) NOT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `sexe` varchar(10) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure pour table `modules`
CREATE TABLE `modules` (
  `id_module` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code_module` varchar(20) NOT NULL UNIQUE,
  `nom_module` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure pour table `cours`
CREATE TABLE `cours` (
  `id_cours` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `id_prof` bigint(20) UNSIGNED DEFAULT NULL,
  `titre` varchar(200) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_cours`),
  FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure pour table `profs_modules`
CREATE TABLE `profs_modules` (
  `id_prof_module` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_prof` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_prof_module`),
  UNIQUE KEY `prof_module_unique` (`id_prof`, `id_module`),
  FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure pour table `exercices`
CREATE TABLE `exercices` (
  `id_exercice` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `id_prof` bigint(20) UNSIGNED DEFAULT NULL,
  `titre` varchar(200) NOT NULL,
  `instructions` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_exercice`),
  FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure pour table `quiz`
CREATE TABLE `quiz` (
  `id_quiz` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `id_prof` bigint(20) UNSIGNED DEFAULT NULL,
  `titre` varchar(200) NOT NULL,
  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`questions`)),
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_quiz`),
  FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  FOREIGN KEY (`id_prof`) REFERENCES `user` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure pour table `documents`
CREATE TABLE `documents` (
  `id_document` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_etudiant` bigint(20) UNSIGNED NOT NULL,
  `type_document` varchar(50) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_televersement` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_document`),
  FOREIGN KEY (`id_etudiant`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure pour table `notes`
CREATE TABLE `notes` (
  `id_note` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_etudiant` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `note` decimal(5,2) NOT NULL,
  `date_attribution` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_note`),
  UNIQUE KEY `etudiant_module_unique` (`id_etudiant`, `id_module`),
  FOREIGN KEY (`id_etudiant`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
