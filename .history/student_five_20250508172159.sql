-- Base de données complète pour la plateforme éducative avec gestion par classes

-- Table des utilisateurs : contient les informations de base pour tous les types d'utilisateurs
-- (étudiants, professeurs, administrateurs, personnel)
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY, -- Identifiant unique pour chaque utilisateur
    username VARCHAR(50) UNIQUE NOT NULL, -- Nom d'utilisateur unique
    password_hash VARCHAR(255) NOT NULL, -- Mot de passe haché pour la sécurité
    email VARCHAR(100) UNIQUE NOT NULL, -- Adresse email unique
    first_name VARCHAR(50), -- Prénom de l'utilisateur
    last_name VARCHAR(50), -- Nom de famille de l'utilisateur
    role VARCHAR(20) NOT NULL, -- Rôle de l'utilisateur (ex : 'student', 'professor', 'admin', 'staff')
    phone_number VARCHAR(15), -- Numéro de téléphone (optionnel)
    address TEXT, -- Adresse complète (optionnel)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Date de création de l'utilisateur
);

-- Table des classes : représente les groupes d'étudiants ou promotions
CREATE TABLE classes (
    class_id SERIAL PRIMARY KEY, -- Identifiant unique pour chaque classe
    class_name VARCHAR(100) NOT NULL, -- Nom de la classe (ex : "L1 Informatique", "Promo 2025")
    description TEXT, -- Description de la classe (optionnel)
    enrollment_year INT, -- Année d'entrée de la classe
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Date de création de la classe
);

-- Table pour affecter les étudiants à une classe (relation many-to-one)
CREATE TABLE student_classes (
    id SERIAL PRIMARY KEY, -- Identifiant unique pour chaque affectation
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE, -- Référence à l'étudiant
    class_id INT NOT NULL REFERENCES classes(class_id) ON DELETE CASCADE, -- Référence à la classe
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Date d'affectation
    UNIQUE(student_id) -- Un étudiant ne peut appartenir qu'à une seule classe
);

-- Table des modules (cours) : représente les matières enseignées
CREATE TABLE modules (
    module_id SERIAL PRIMARY KEY, -- Identifiant unique pour chaque module
    module_code VARCHAR(20) UNIQUE NOT NULL, -- Code unique du module (ex : "INF101")
    module_name VARCHAR(100) NOT NULL, -- Nom du module (ex : "Introduction à l'informatique")
    description TEXT, -- Description du module (optionnel)
    credits INT, -- Nombre de crédits associés au module
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Date de création du module
);

-- Table pour associer les classes et les modules (relation many-to-many)
CREATE TABLE class_modules (
    id SERIAL PRIMARY KEY, -- Identifiant unique pour chaque association
    class_id INT NOT NULL REFERENCES classes(class_id) ON DELETE CASCADE, -- Référence à la classe
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE, -- Référence au module
    UNIQUE(class_id, module_id) -- Une classe ne peut suivre un module qu'une seule fois
);

-- Table pour associer les professeurs et les modules qu'ils enseignent (relation many-to-many)
CREATE TABLE professor_modules (
    id SERIAL PRIMARY KEY, -- Identifiant unique pour chaque association
    professor_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE, -- Référence au professeur
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE, -- Référence au module
    UNIQUE(professor_id, module_id) -- Un professeur ne peut enseigner un module qu'une seule fois
);

-- Table des cours : représente les contenus pédagogiques liés à un module
CREATE TABLE courses (
    course_id SERIAL PRIMARY KEY, -- Identifiant unique pour chaque cours
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE, -- Référence au module
    professor_id INT NOT NULL REFERENCES users(user_id) ON DELETE SET NULL, -- Référence au professeur (peut être NULL si supprimé)
    title VARCHAR(200) NOT NULL, -- Titre du cours
    content TEXT NOT NULL, -- Contenu pédagogique du cours
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Date de création du cours
);

-- Table des exercices : représente les travaux pratiques liés à un module
CREATE TABLE exercises (
    exercise_id SERIAL PRIMARY KEY, -- Identifiant unique pour chaque exercice
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE, -- Référence au module
    professor_id INT NOT NULL REFERENCES users(user_id) ON DELETE SET NULL, -- Référence au professeur (peut être NULL si supprimé)
    title VARCHAR(200) NOT NULL, -- Titre de l'exercice
    instructions TEXT NOT NULL, -- Instructions pour l'exercice
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Date de création de l'exercice
);

-- Table des quiz : représente les évaluations sous forme de quiz
CREATE TABLE quizzes (
    quiz_id SERIAL PRIMARY KEY, -- Identifiant unique pour chaque quiz
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE, -- Référence au module
    professor_id INT NOT NULL REFERENCES users(user_id) ON DELETE SET NULL, -- Référence au professeur (peut être NULL si supprimé)
    title VARCHAR(200) NOT NULL, -- Titre du quiz
    questions JSON NOT NULL, -- Questions du quiz stockées en format JSON
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Date de création du quiz
);

-- Table des notes : représente les résultats des étudiants pour chaque module
CREATE TABLE grades (
    grade_id SERIAL PRIMARY KEY, -- Identifiant unique pour chaque note
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE, -- Référence à l'étudiant
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE, -- Référence au module
    grade DECIMAL(5,2) NOT NULL, -- Note obtenue (ex : 18.50)
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Date d'attribution de la note
    UNIQUE(student_id, module_id) -- Un étudiant ne peut avoir qu'une seule note par module
);

-- Table des documents officiels : représente les fichiers liés aux étudiants
CREATE TABLE documents (
    document_id SERIAL PRIMARY KEY, -- Identifiant unique pour chaque document
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE, -- Référence à l'étudiant
    document_type VARCHAR(50) NOT NULL, -- Type de document (ex : 'certificate', 'transcript')
    file_path VARCHAR(255) NOT NULL, -- Chemin ou URL vers le fichier stocké
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Date de téléversement du document
);

-- Table de messagerie interne : permet aux utilisateurs d'échanger des messages
CREATE TABLE messages (
    message_id SERIAL PRIMARY KEY, -- Identifiant unique pour chaque message
    sender_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE, -- Référence à l'expéditeur
    receiver_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE, -- Référence au destinataire
    subject VARCHAR(200), -- Sujet du message
    body TEXT NOT NULL, -- Contenu du message
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Date d'envoi du message
    read BOOLEAN DEFAULT FALSE -- Statut de lecture du message (false par défaut)
);

-- Index pour optimiser les performances des requêtes
CREATE INDEX idx_users_role ON users(role); -- Index sur le rôle des utilisateurs
CREATE INDEX idx_classes_name ON classes(class_name); -- Index sur le nom des classes
CREATE INDEX idx_modules_code ON modules(module_code); -- Index sur le code des modules
CREATE INDEX idx_class_modules_class ON class_modules(class_id); -- Index sur les classes dans class_modules
CREATE INDEX idx_class_modules_module ON class_modules(module_id); -- Index sur les modules dans class_modules
CREATE INDEX idx_prof_modules_professor ON professor_modules(professor_id); -- Index sur les professeurs dans professor_modules
CREATE INDEX idx_prof_modules_module ON professor_modules(module_id); -- Index sur les modules dans professor_modules
CREATE INDEX idx_grades_student ON grades(student_id); -- Index sur les étudiants dans grades
CREATE INDEX idx_grades_module ON grades(module_id); -- Index sur les modules dans grades
