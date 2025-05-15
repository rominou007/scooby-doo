<<<<<<< HEAD
-- Suppression de la base si elle existe et création
DROP DATABASE IF EXISTS nouvelle_base;
CREATE DATABASE nouvelle_base CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE nouvelle_base;
=======
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 15 mai 2025 à 09:51
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12
>>>>>>> 8675e1abf45ee9f23f967a5b5db43a66408acbe1

-- Suppression des tables existantes si elles existent (ordre inverse des dépendances)
DROP TABLE IF EXISTS `notes`;
DROP TABLE IF EXISTS `documents`;
DROP TABLE IF EXISTS `quiz`;
DROP TABLE IF EXISTS `exercices`;
DROP TABLE IF EXISTS `cours`;
DROP TABLE IF EXISTS `profs_modules`;
DROP TABLE IF EXISTS `modules`;
DROP TABLE IF EXISTS `user`;

<<<<<<< HEAD
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
=======

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : student_five
--

-- --------------------------------------------------------

--
-- Structure de la table classes
--

CREATE TABLE classes (
  class_id bigint(20) UNSIGNED NOT NULL,
  class_name varchar(100) NOT NULL,
  description text DEFAULT NULL,
  enrollment_year int(11) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table class_modules
--

CREATE TABLE class_modules (
  id bigint(20) UNSIGNED NOT NULL,
  class_id int(11) NOT NULL,
  module_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table courses
--

CREATE TABLE courses (
  course_id bigint(20) UNSIGNED NOT NULL,
  module_id int(11) NOT NULL,
  professor_id int(11) NOT NULL,
  title varchar(200) NOT NULL,
  content text NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table documents
--

CREATE TABLE documents (
  document_id bigint(20) UNSIGNED NOT NULL,
  student_id int(11) NOT NULL,
  document_type varchar(50) NOT NULL,
  file_path varchar(255) NOT NULL,
  uploaded_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table documents
--

INSERT INTO documents (document_id, student_id, document_type, file_path, uploaded_at) VALUES
(1, 0, 'module_doc_1', 'uploads/gearMotor.stl', '2025-05-13 08:35:42');

-- --------------------------------------------------------

--
-- Structure de la table exercises
--

CREATE TABLE exercises (
  exercise_id bigint(20) UNSIGNED NOT NULL,
  module_id int(11) NOT NULL,
  professor_id int(11) NOT NULL,
  title varchar(200) NOT NULL,
  instructions text NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table grades
--

CREATE TABLE grades (
  grade_id bigint(20) UNSIGNED NOT NULL,
  student_id int(11) NOT NULL,
  module_id int(11) NOT NULL,
  grade decimal(5,2) NOT NULL,
  graded_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table modules
--

CREATE TABLE modules (
  module_id bigint(20) UNSIGNED NOT NULL,
  module_code varchar(20) NOT NULL,
  module_name varchar(100) NOT NULL,
  description text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table modules
--

INSERT INTO modules (module_id, module_code, module_name, description, created_at) VALUES
(1, 'Test1', 'Test1', 'premier test', '2025-05-13 07:19:16');

-- --------------------------------------------------------

--
-- Structure de la table professor_modules
--

CREATE TABLE professor_modules (
  id bigint(20) UNSIGNED NOT NULL,
  professor_id int(11) NOT NULL,
  module_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table quizzes
--

CREATE TABLE quizzes (
  quiz_id bigint(20) UNSIGNED NOT NULL,
  module_id int(11) NOT NULL,
  professor_id int(11) NOT NULL,
  title varchar(200) NOT NULL,
  questions longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(questions)),
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table student_classes
--

CREATE TABLE student_classes (
  id bigint(20) UNSIGNED NOT NULL,
  student_id int(11) NOT NULL,
  class_id int(11) NOT NULL,
  assigned_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table users
--

CREATE TABLE users (
  user_id bigint(20) UNSIGNED NOT NULL,
  username varchar(50) NOT NULL,
  password_hash varchar(255) NOT NULL,
  email varchar(100) NOT NULL,
  first_name varchar(50) DEFAULT NULL,
  last_name varchar(50) DEFAULT NULL,
  role int(11) NOT NULL,
  phone_number varchar(15) DEFAULT NULL,
  address text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
>>>>>>> 8675e1abf45ee9f23f967a5b5db43a66408acbe1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table users
--

INSERT INTO users (user_id, username, password_hash, email, first_name, last_name, role, phone_number, address, created_at) VALUES
(1, 'AdminAntoine', '$2y$10$rWLVJR.WcIs3ZYk/wP8Jouo2RbJMavxTFB.ATjrSdka5LMm/xss42', 'adminantoine@gmail.com', 'Antoine', 'Gobron', 0, '0606060606', 'non', '2025-05-13 07:08:50');
COMMIT;
<<<<<<< HEAD
=======

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
>>>>>>> 8675e1abf45ee9f23f967a5b5db43a66408acbe1
