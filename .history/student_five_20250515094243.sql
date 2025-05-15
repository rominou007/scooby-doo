-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 09:26 AM
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
-- Database: `etudiant_cinq`
--

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id_classe` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom_classe` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `annee_inscription` int(11) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_classe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id_module` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code_module` varchar(20) NOT NULL UNIQUE,
  `nom_module` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `professeurs_modules`
--

CREATE TABLE `professeurs_modules` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_professeur` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `professeur_module_unique` (`id_professeur`, `id_module`),
  FOREIGN KEY (`id_professeur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE,
  FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cours`
--

CREATE TABLE `cours` (
  `id_cours` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `id_professeur` bigint(20) UNSIGNED NOT NULL,
  `titre` varchar(200) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_cours`),
  FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  FOREIGN KEY (`id_professeur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exercices`
--

CREATE TABLE `exercices` (
  `id_exercice` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `id_professeur` bigint(20) UNSIGNED NOT NULL,
  `titre` varchar(200) NOT NULL,
  `instructions` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_exercice`),
  FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  FOREIGN KEY (`id_professeur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quizz`
--

CREATE TABLE `quizz` (
  `id_quiz` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `id_professeur` bigint(20) UNSIGNED NOT NULL,
  `titre` varchar(200) NOT NULL,
  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`questions`)),
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_quiz`),
  FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE,
  FOREIGN KEY (`id_professeur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id_document` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_etudiant` bigint(20) UNSIGNED NOT NULL,
  `type_document` varchar(50) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_televersement` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_document`),
  FOREIGN KEY (`id_etudiant`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateur` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom_utilisateur` varchar(50) NOT NULL UNIQUE,
  `mot_de_passe` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `prenom` varchar(50) DEFAULT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `role` int(11) NOT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `sexe` varchar(10) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id_note` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_etudiant` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `note` decimal(5,2) NOT NULL,
  `date_attribution` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_note`),
  UNIQUE KEY `etudiant_module_unique` (`id_etudiant`, `id_module`),
  FOREIGN KEY (`id_etudiant`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE,
  FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
