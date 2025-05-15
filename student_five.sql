-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 12:05 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id_document` bigint(20) UNSIGNED NOT NULL,
  `id_etudiant` bigint(20) UNSIGNED NOT NULL,
  `type_document` varchar(50) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_televersement` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `id_user` bigint(20) UNSIGNED NOT NULL,
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
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id_module` bigint(20) UNSIGNED NOT NULL,
  `code_module` varchar(20) NOT NULL,
  `nom_module` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
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
-- Table structure for table `profs_modules`
--

CREATE TABLE `profs_modules` (
  `id_prof_module` bigint(20) UNSIGNED NOT NULL,
  `id_prof` bigint(20) UNSIGNED NOT NULL,
  `id_module` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `nom_user` varchar(50) NOT NULL,
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
-- Indexes for dumped tables
--

--
-- Indexes for table `cours`
--
ALTER TABLE `cours`
  ADD PRIMARY KEY (`id_cours`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_prof` (`id_prof`);

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
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `forum_commentaires`
--
ALTER TABLE `forum_commentaires`
  ADD PRIMARY KEY (`commentaire_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id_module`),
  ADD UNIQUE KEY `code_module` (`code_module`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id_note`),
  ADD UNIQUE KEY `etudiant_module_unique` (`id_etudiant`,`id_module`),
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
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `nom_user` (`nom_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cours`
--
ALTER TABLE `cours`
  MODIFY `id_cours` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id_document` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id_module` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id_note` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profs_modules`
--
ALTER TABLE `profs_modules`
  MODIFY `id_prof_module` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id_quiz` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `forum_articles_user_fk` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `forum_commentaires`
--
ALTER TABLE `forum_commentaires`
  ADD CONSTRAINT `forum_commentaires_article_fk` FOREIGN KEY (`article_id`) REFERENCES `forum_articles` (`article_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_commentaires_user_fk` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id_module`) ON DELETE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
