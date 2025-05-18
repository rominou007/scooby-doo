<?php
session_start();
require_once 'db.php'; // Assurez-vous que ce fichier contient la connexion appropriée

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], [0, 1, 2])) { // 0 pour étudiant, 1 pour professeur, 2 pour administrateur
    header('Location: login.php'); // Redirigez vers la page de connexion
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

try {
    if ($user_role === 0) { // Étudiant
        // Récupérer la classe de l'étudiant
        $class_query = "
            SELECT c.class_id, c.class_name, c.année_scolaire
            FROM classes c
            JOIN student_classes sc ON c.class_id = sc.class_id
            WHERE sc.student_id = ?
        ";
        $class_stmt = $pdo->prepare($class_query);
        $class_stmt->execute([$user_id]);
        $student_class = $class_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student_class) {
            throw new Exception("Aucune classe trouvée pour cet étudiant.");
        }

        // Récupérer les modules de la classe
        $modules_query = "
            SELECT m.id_module, m.code_module, m.nom_module, m.description,
                   CONCAT(u.prenom, ' ', u.nom) AS professor_name
            FROM modules m
            JOIN profs_modules pm ON m.id_module = pm.id_module
            JOIN user u ON pm.id_prof = u.id_user
            WHERE m.class_id = ?
        ";
        $modules_stmt = $pdo->prepare($modules_query);
        $modules_stmt->execute([$student_class['class_id']]);
        $modules = $modules_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les notes récentes
        $grades_query = "
            SELECT m.nom_module, n.note, n.date_attribution
            FROM notes n
            JOIN modules m ON n.id_module = m.id_module
            WHERE n.id_etudiant = ?
            ORDER BY n.date_attribution DESC
            LIMIT 5
        ";
        $grades_stmt = $pdo->prepare($grades_query);
        $grades_stmt->execute([$user_id]);
        $recent_grades = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les exercices récents
        $exercises_query = "
            SELECT e.id_exercice, e.titre, m.nom_module, e.date_creation
            FROM exercices e
            JOIN modules m ON e.id_module = m.id_module
            WHERE m.class_id = ?
            ORDER BY e.date_creation DESC
            LIMIT 3
        ";
        $exercises_stmt = $pdo->prepare($exercises_query);
        $exercises_stmt->execute([$student_class['class_id']]);
        $recent_exercises = $exercises_stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($user_role === 1) { // Professeur
        // Récupérer les modules enseignés par le professeur
        $modules_query = "
            SELECT m.id_module, m.code_module, m.nom_module, m.description
            FROM modules m
            JOIN profs_modules pm ON m.id_module = pm.id_module
            WHERE pm.id_prof = ?
        ";
        $modules_stmt = $pdo->prepare($modules_query);
        $modules_stmt->execute([$user_id]);
        $modules = $modules_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les devoirs récents créés par le professeur
        $exercises_query = "
            SELECT e.id_exercice, e.titre, m.nom_module, e.date_creation
            FROM exercices e
            JOIN modules m ON e.id_module = m.id_module
            WHERE e.id_prof = ?
            ORDER BY e.date_creation DESC
            LIMIT 5
        ";
        $exercises_stmt = $pdo->prepare($exercises_query);
        $exercises_stmt->execute([$user_id]);
        $recent_exercises = $exercises_stmt->fetchAll(PDO::FETCH_ASSOC);
 
    } elseif ($user_role === 2) { // Administrateur
        // Récupérer tous les utilisateurs
        $users_query = "
            SELECT u.id_user, u.prenom, u.nom, u.email, u.role
            FROM user u
            ORDER BY u.role ASC, u.nom ASC
        ";
        $users_stmt = $pdo->prepare($users_query);
        $users_stmt->execute();
        $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer tous les modules
        $modules_query = "
            SELECT m.id_module, m.code_module, m.nom_module, m.description
            FROM modules m
            ORDER BY m.nom_module ASC
        ";
        $modules_stmt = $pdo->prepare($modules_query);
        $modules_stmt->execute();
        $modules = $modules_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer tous les cours
        $courses_query = "
            SELECT c.id_cours, c.titre, m.nom_module, c.date_creation
            FROM cours c
            JOIN modules m ON c.id_module = m.id_module
            ORDER BY c.date_creation DESC
        ";
        $courses_stmt = $pdo->prepare($courses_query);
        $courses_stmt->execute();
        $courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Erreur de base de données : " . $e->getMessage());
    die("Une erreur est survenue lors du chargement des données.");
} catch (Exception $e) {
    error_log("Erreur : " . $e->getMessage());
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
    <?php include("link.php"); ?>
</head>
<body class="bg-light">
    <?php include("navbar.php"); ?>

    <div class="container mt-4">
        <div class="row mb-3">
            <div class="col">
                <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h1>
                <p class="text-muted">
                    <?php 
                    switch($_SESSION['role']) {
                        case 0: echo "Espace étudiant"; break;
                        case 1: echo "Espace professeur"; break;
                        case 2: echo "Espace administrateur"; break;
                    }
                    ?>
                </p>
            </div>
        </div>

        <div class="row">
            <?php if ($user_role === 0): ?>
                <!-- Section : Modules -->
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Mes Modules</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($modules)): ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($modules as $module): ?>
                                        <li class="mb-2">
                                            <strong><?php echo htmlspecialchars($module['code_module']); ?> - <?php echo htmlspecialchars($module['nom_module']); ?></strong>
                                            <br>
                                            <small class="text-muted">Professeur : <?php echo htmlspecialchars($module['professor_name']); ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($module['description']); ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">Aucun module trouvé.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Section : Notes récentes -->
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">Mes Notes Récentes</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_grades)): ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($recent_grades as $grade): ?>
                                        <li class="mb-2">
                                            <strong><?php echo htmlspecialchars($grade['nom_module']); ?></strong>
                                            <br>
                                            <span class="badge <?php echo $grade['note'] >= 10 ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo number_format($grade['note'], 2); ?>/20
                                            </span>
                                            <small class="text-muted ml-2">
                                                Le <?php echo date('d/m/Y', strtotime($grade['date_attribution'])); ?>
                                            </small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">Aucune note disponible.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Section : Exercices récents -->
                <div class="col-md-12 mt-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-warning text-white">
                            <h5 class="card-title mb-0">Exercices Récents</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_exercises)): ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($recent_exercises as $exercise): ?>
                                        <li class="mb-2">
                                            <strong><?php echo htmlspecialchars($exercise['titre']); ?></strong>
                                            <br>
                                            <small class="text-muted">Module : <?php echo htmlspecialchars($exercise['nom_module']); ?></small>
                                            <br>
                                            <small class="text-muted">Créé le : <?php echo date('d/m/Y', strtotime($exercise['date_creation'])); ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">Aucun exercice récent trouvé.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($user_role === 1): ?>
                <!-- Section : Modules enseignés -->
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Modules Enseignés</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($modules)): ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($modules as $module): ?>
                                        <li class="mb-2">
                                            <strong><?php echo htmlspecialchars($module['code_module']); ?> - <?php echo htmlspecialchars($module['nom_module']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($module['description']); ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">Aucun module trouvé.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Section : Devoirs récents -->
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-warning text-white">
                            <h5 class="card-title mb-0">Devoirs Récents</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_exercises)): ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($recent_exercises as $exercise): ?>
                                        <li class="mb-2">
                                            <strong><?php echo htmlspecialchars($exercise['titre']); ?></strong>
                                            <br>
                                            <small class="text-muted">Module : <?php echo htmlspecialchars($exercise['nom_module']); ?></small>
                                            <br>
                                            <small class="text-muted">Créé le : <?php echo date('d/m/Y', strtotime($exercise['date_creation'])); ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">Aucun devoir récent trouvé.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($user_role === 2): ?>
                <!-- Section : Utilisateurs -->
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">Liste des Utilisateurs</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($users)): ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($users as $user): ?>
                                        <li class="mb-2">
                                            <strong><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></strong>
                                            <br>
                                            <small class="text-muted">Email : <?php echo htmlspecialchars($user['email']); ?></small>
                                            <br>
                                            <small class="text-muted">Rôle : 
                                                <?php echo $user['role'] == 0 ? 'Étudiant' : ($user['role'] == 1 ? 'Professeur' : 'Administrateur'); ?>
                                            </small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">Aucun utilisateur trouvé.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Section : Modules -->
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Liste des Modules</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($modules)): ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($modules as $module): ?>
                                        <li class="mb-2">
                                            <strong><?php echo htmlspecialchars($module['code_module']); ?> - <?php echo htmlspecialchars($module['nom_module']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($module['description']); ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">Aucun module trouvé.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Section : Cours -->
                <div class="col-md-12 mt-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">Liste des Cours</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($courses)): ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($courses as $course): ?>
                                        <li class="mb-2">
                                            <strong><?php echo htmlspecialchars($course['titre']); ?></strong>
                                            <br>
                                            <small class="text-muted">Module : <?php echo htmlspecialchars($course['nom_module']); ?></small>
                                            <br>
                                            <small class="text-muted">Créé le : <?php echo date('d/m/Y', strtotime($course['date_creation'])); ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">Aucun cours trouvé.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="mt-5 py-3 bg-dark text-white text-center">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> StudentFive - Tous droits réservés</p>
        </div>
    </footer>
</body>
</html>