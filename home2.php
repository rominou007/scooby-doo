<?php
session_start();    
require_once 'db.php'; // Assurez-vous que ce fichier contient la connexion appropriée
include 'navbar.php';

// Vérifier si l'utilisateur est connecté et est un étudiant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 0) {
    header('Location: ');// je sias pas ou je le redirige vraimment 
    exit();
}

$student_id = $_SESSION['user_id'];

// Récupérer la classe de l'étudiant
try {
    $class_query = "
        SELECT c.class_id, c.class_name 
        FROM classes c
        JOIN student_classes sc ON c.class_id = sc.class_id
        WHERE sc.student_id = ?
    ";
    $class_stmt = $pdo->prepare($class_query);
    $class_stmt->execute([$student_id]);
    $student_class = $class_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student_class) {
        throw new Exception("Aucune classe trouvée pour cet étudiant.");
    }

    // Récupérer les modules de la classe
    $modules_query = "
        SELECT DISTINCT m.module_id, m.module_code, m.module_name, m.description,
               u.first_name || ' ' || u.last_name AS professor_name
        FROM modules m
        JOIN class_modules cm ON m.module_id = cm.module_id
        JOIN professor_modules pm ON m.module_id = pm.module_id
        JOIN users u ON pm.professor_id = u.user_id
        WHERE cm.class_id = ?
    ";
    $modules_stmt = $pdo->prepare($modules_query);
    $modules_stmt->execute([$student_class['class_id']]);
    $modules = $modules_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les notes récentes
    $grades_query = "
        SELECT m.module_name, g.grade, g.graded_at
        FROM grades g
        JOIN modules m ON g.module_id = m.module_id
        WHERE g.student_id = ?
        ORDER BY g.graded_at DESC
        LIMIT 5
    ";
    $grades_stmt = $pdo->prepare($grades_query);
    $grades_stmt->execute([$student_id]);
    $recent_grades = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les exercices récents
    $exercises_query = "
        SELECT e.exercise_id, e.title, m.module_name, e.created_at
        FROM exercises e
        JOIN modules m ON e.module_id = m.module_id
        JOIN class_modules cm ON m.module_id = cm.module_id
        WHERE cm.class_id = ?
        ORDER BY e.created_at DESC
        LIMIT 3
    ";
    $exercises_stmt = $pdo->prepare($exercises_query);
    $exercises_stmt->execute([$student_class['class_id']]);
    $recent_exercises = $exercises_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Gestion des erreurs de base de données
    error_log("Erreur de base de données : " . $e->getMessage());
    die("Une erreur est survenue lors du chargement des données.");
} catch (Exception $e) {
    // Gestion des autres erreurs
    error_log("Erreur : " . $e->getMessage());
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Étudiant</title>
    <?php include 'link.php'; ?>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="card-title">Bienvenue, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h1>
                        <p class="card-text">Classe : <?php echo htmlspecialchars($student_class['class_name']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Mes Modules</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($modules)): ?>
                            <ul class="list-unstyled">
                                <?php foreach ($modules as $module): ?>
                                    <li class="mb-2">
                                        <strong><?php echo htmlspecialchars($module['module_code']); ?> - <?php echo htmlspecialchars($module['module_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">Professeur : <?php echo htmlspecialchars($module['professor_name']); ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">Aucun module trouvé.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="courses.php" class="btn btn-sm btn-outline-primary">Voir tous les modules</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">Mes Notes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_grades)): ?>
                            <ul class="list-unstyled">
                                <?php foreach ($recent_grades as $grade): ?>
                                    <li class="mb-2">
                                        <strong><?php echo htmlspecialchars($grade['module_name']); ?></strong>
                                        <br>
                                        <span class="badge <?php echo $grade['grade'] >= 10 ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo number_format($grade['grade'], 2); ?>/20
                                        </span>
                                        <small class="text-muted ml-2">
                                            Le <?php echo date('d/m/Y', strtotime($grade['graded_at'])); ?>
                                        </small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">Aucune note disponible.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="grades.php" class="btn btn-sm btn-outline-success">Voir toutes les notes</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <h5 class="card-title mb-0">Devoirs Récents</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_exercises)): ?>
                            <ul class="list-unstyled">
                                <?php foreach ($recent_exercises as $exercise): ?>
                                    <li class="mb-2">
                                        <strong><?php echo htmlspecialchars($exercise['title']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            Module : <?php echo htmlspecialchars($exercise['module_name']); ?>
                                            <br>
                                            Créé le : <?php echo date('d/m/Y', strtotime($exercise['created_at'])); ?>
                                        </small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">Aucun devoir récent.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="exercises.php" class="btn btn-sm btn-outline-warning">Voir tous les devoirs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
