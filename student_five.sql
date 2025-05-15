-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 15 mai 2025 à 09:51
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table users
--

INSERT INTO users (user_id, username, password_hash, email, first_name, last_name, role, phone_number, address, created_at) VALUES
(1, 'AdminAntoine', '$2y$10$rWLVJR.WcIs3ZYk/wP8Jouo2RbJMavxTFB.ATjrSdka5LMm/xss42', 'adminantoine@gmail.com', 'Antoine', 'Gobron', 0, '0606060606', 'non', '2025-05-13 07:08:50');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;