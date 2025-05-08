-- Base de données complète pour la plateforme éducative avec gestion par classes

-- Utilisateurs : étudiants, professeurs, admins, personnel
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    role VARCHAR(20) NOT NULL,  -- Exemples: 'student', 'professor', 'admin', 'staff'
    phone_number VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes / Promotions / Groupes d'étudiants
CREATE TABLE classes (
    class_id SERIAL PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,     -- Ex: "L1 Informatique", "Promo 2025"
    description TEXT,
    enrollment_year INT,                   -- Année d'entrée de la classe
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Affectation des étudiants à une classe (relation many-to-one)
CREATE TABLE student_classes (
    id SERIAL PRIMARY KEY,
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    class_id INT NOT NULL REFERENCES classes(class_id) ON DELETE CASCADE,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_id)                     -- Un étudiant appartient à une seule classe
);

-- Modules (cours)
CREATE TABLE modules (
    module_id SERIAL PRIMARY KEY,
    module_code VARCHAR(20) UNIQUE NOT NULL,
    module_name VARCHAR(100) NOT NULL,
    description TEXT,
    credits INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Association entre classes et modules — une classe suit plusieurs modules
CREATE TABLE class_modules (
    id SERIAL PRIMARY KEY,
    class_id INT NOT NULL REFERENCES classes(class_id) ON DELETE CASCADE,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    UNIQUE(class_id, module_id)
);

-- Relation many-to-many entre professeurs et modules qu'ils enseignent
CREATE TABLE professor_modules (
    id SERIAL PRIMARY KEY,
    professor_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    UNIQUE(professor_id, module_id)
);

-- Cours - contenus pédagogiques
CREATE TABLE courses (
    course_id SERIAL PRIMARY KEY,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    professor_id INT NOT NULL REFERENCES users(user_id) ON DELETE SET NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Exercices liés à un module
CREATE TABLE exercises (
    exercise_id SERIAL PRIMARY KEY,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    professor_id INT NOT NULL REFERENCES users(user_id) ON DELETE SET NULL,
    title VARCHAR(200) NOT NULL,
    instructions TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Quiz liés à un module
CREATE TABLE quizzes (
    quiz_id SERIAL PRIMARY KEY,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    professor_id INT NOT NULL REFERENCES users(user_id) ON DELETE SET NULL,
    title VARCHAR(200) NOT NULL,
    questions JSON NOT NULL,  -- Stockage flexible des questions en JSON
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notes / résultats des étudiants pour chaque module
CREATE TABLE grades (
    grade_id SERIAL PRIMARY KEY,
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    grade DECIMAL(5,2) NOT NULL,  -- Ex: 18.50
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_id, module_id)
);

-- Documents officiels (certificats, attestations, relevés, etc.)
CREATE TABLE documents (
    document_id SERIAL PRIMARY KEY,
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    document_type VARCHAR(50) NOT NULL, -- ex: 'certificate', 'transcript'
    file_path VARCHAR(255) NOT NULL,    -- chemin ou URL vers le fichier stocké
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Messagerie interne entre utilisateurs
CREATE TABLE messages (
    message_id SERIAL PRIMARY KEY,
    sender_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    receiver_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    subject VARCHAR(200),
    body TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read BOOLEAN DEFAULT FALSE
);

-- Index utiles pour les performances
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_classes_name ON classes(class_name);
CREATE INDEX idx_modules_code ON modules(module_code);
CREATE INDEX idx_class_modules_class ON class_modules(class_id);
CREATE INDEX idx_class_modules_module ON class_modules(module_id);
CREATE INDEX idx_prof_modules_professor ON professor_modules(professor_id);
CREATE INDEX idx_prof_modules_module ON professor_modules(module_id);
CREATE INDEX idx_grades_student ON grades(student_id);
CREATE INDEX idx_grades_module ON grades(module_id);
