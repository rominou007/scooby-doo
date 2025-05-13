-- ============================
-- TABLE UTILISATEURS
-- ============================
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    role VARCHAR(20) NOT NULL CHECK (role IN ('student', 'professor', 'admin', 'staff')),
    phone_number VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================
-- TABLE CLASSES
-- ============================
CREATE TABLE classes (
    class_id SERIAL PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,
    description TEXT,
    enrollment_year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================
-- AFFECTATION DES ÉTUDIANTS À DES CLASSES
-- ============================
CREATE TABLE student_classes (
    id SERIAL PRIMARY KEY,
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    class_id INT NOT NULL REFERENCES classes(class_id) ON DELETE CASCADE,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_id)
);

-- ============================
-- MODULES (MATIÈRES)
-- ============================
CREATE TABLE modules (
    module_id SERIAL PRIMARY KEY,
    module_code VARCHAR(20) UNIQUE NOT NULL,
    module_name VARCHAR(100) NOT NULL,
    description TEXT,
    credits INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================
-- ASSOCIATION CLASSES - MODULES
-- ============================
CREATE TABLE class_modules (
    id SERIAL PRIMARY KEY,
    class_id INT NOT NULL REFERENCES classes(class_id) ON DELETE CASCADE,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    UNIQUE(class_id, module_id)
);

-- ============================
-- ASSOCIATION PROFESSEURS - MODULES
-- ============================
CREATE TABLE professor_modules (
    id SERIAL PRIMARY KEY,
    professor_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    UNIQUE(professor_id, module_id)
);

-- ============================
-- COURS PÉDAGOGIQUES
-- ============================
CREATE TABLE courses (
    course_id SERIAL PRIMARY KEY,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    professor_id INT REFERENCES users(user_id) ON DELETE SET NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================
-- DOCUMENTS LIÉS AUX MODULES (SUPPORTS, TD, ETC.)
-- ============================
CREATE TABLE documents (
    document_id SERIAL PRIMARY KEY,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    titre VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================
-- EXERCICES (TD, TP)
-- ============================
CREATE TABLE exercises (
    exercise_id SERIAL PRIMARY KEY,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    professor_id INT REFERENCES users(user_id) ON DELETE SET NULL,
    title VARCHAR(200) NOT NULL,
    instructions TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================
-- QUIZ (FORMAT JSON)
-- ============================
CREATE TABLE quizzes (
    quiz_id SERIAL PRIMARY KEY,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    professor_id INT REFERENCES users(user_id) ON DELETE SET NULL,
    title VARCHAR(200) NOT NULL,
    questions JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================
-- NOTES / GRADES
-- ============================
CREATE TABLE grades (
    grade_id SERIAL PRIMARY KEY,
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    module_id INT NOT NULL REFERENCES modules(module_id) ON DELETE CASCADE,
    grade DECIMAL(5,2) NOT NULL,
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_id, module_id)
);

-- ============================
-- MESSAGERIE INTERNE
-- ============================
CREATE TABLE messages (
    message_id SERIAL PRIMARY KEY,
    sender_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    receiver_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    subject VARCHAR(200),
    body TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read BOOLEAN DEFAULT FALSE
);

-- ============================
-- INDEX POUR OPTIMISATION
-- ============================
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_classes_name ON classes(class_name);
CREATE INDEX idx_modules_code ON modules(module_code);
CREATE INDEX idx_class_modules_class ON class_modules(class_id);
CREATE INDEX idx_class_modules_module ON class_modules(module_id);
CREATE INDEX idx_prof_modules_professor ON professor_modules(professor_id);
CREATE INDEX idx_prof_modules_module ON professor_modules(module_id);
CREATE INDEX idx_grades_student ON grades(student_id);
CREATE INDEX idx_grades_module ON grades(module_id);
