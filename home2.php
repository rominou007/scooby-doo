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
            SELECT c.class_id, c.class_name, c.annee_scolaire
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
            SELECT m.id_module, m.code_module, m.nom_module,
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

// Génération de couleurs aléatoires pour les cartes
function getRandomColor() {
    $colors = ['primary', 'success', 'danger', 'warning', 'info', 'secondary'];
    return $colors[array_rand($colors)];
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
                        case 0: echo "<h3>Espace étudiant</h3>"; break;
                        case 1: echo "<h3>Espace professeur</h3>"; break;
                        case 2: echo "<h3>Espace administrateur</h3>"; break;
                    }
                    ?>
                </p>
            </div>
        </div>

        <?php if($_SESSION['role'] === 0): ?>
            <!-- Espace étudiant -->
            <section class="courses-section">
                <h2 class="section-title">COURSES LAST SEEN</h2>
                <div class="slider-container">
                    <div class="row">
                        <?php if (!empty($modules)): ?>
                            <?php foreach($modules as $index => $module): ?>
                                <?php 
                                    $color = getRandomColor();
                                    // Affiche seulement les 3 premiers modules
                                    if ($index > 2) continue;
                                ?>
                                <div class="col-md-4">
                                    <div class="card module-card shadow">
                                        <div class="card-img-top" style="height: 120px; background: url('uploads/Fond.png') center/cover no-repeat;"></div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($module['code_module'] . ' - ' . $module['nom_module']); ?></h5>
                                            <p class="card-text">
                                                <small class="text-muted">Professeur: <?php echo htmlspecialchars($module['professor_name']); ?></small>
                                            </p>
                                            
                                            <a href="cours.php?module_id=<?=$module['id_module'] ?>" class="btn btn-primary btn-sm">Voir le cours</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    Aucun module trouvé pour le moment.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="slider-navigation">
                        <?php 
                            $total_pages = ceil(count($modules) / 3);
                            for ($i = 0; $i < $total_pages; $i++):
                        ?>
                            <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>"></span>
                        <?php endfor; ?>
                    </div>
                </div>
            </section>

            <!-- Notes récentes -->
            <section class="courses-section">
                <h2 class="section-title">Mes notes récentes</h2>
                <div class="row">
                    <?php if(!empty($recent_grades)): ?>
                        <?php foreach($recent_grades as $grade): ?>
                            <div class="col-md-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header <?php echo $grade['note'] >= 10 ? 'bg-success' : 'bg-danger'; ?> text-white">
                                        <?php echo htmlspecialchars($grade['nom_module']); ?>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title text-center display-4">
                                            <?php echo number_format($grade['note'], 2); ?>/20
                                        </h5>
                                        <p class="card-text text-center text-muted">
                                            Note attribuée le <?php echo date('d/m/Y', strtotime($grade['date_attribution'])); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                Aucune note disponible pour le moment.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Exercices récents -->
            <section class="courses-section">
                <h2 class="section-title">Exercices récents</h2>
                <div class="row">
                    <?php if (!empty($recent_exercises)): ?>
                        <?php foreach ($recent_exercises as $exercise): ?>
                            <div class="col-md-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-warning text-white">
                                        Exercice
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($exercise['titre']); ?></h5>
                                        <p class="card-text">
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($exercise['nom_module']); ?></span>
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted">Créé le: <?php echo date('d/m/Y', strtotime($exercise['date_creation'])); ?></small>
                                        </p>
                                        <a href="" class="btn btn-warning btn-sm">Démarrer l'exercice</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                Aucun exercice récent trouvé.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
        
        <?php if($_SESSION['role'] === 1): ?>
            <!-- Espace professeur -->
            <section class="courses-section">
                <h2 class="section-title">Modules enseignés</h2>
                <div class="row">
                    <?php if(!empty($modules)): ?>
                        <?php foreach ($modules as $module): ?>
                            <?php $color = getRandomColor(); ?>
                            <div class="col-md-4">
                                <div class="card module-card shadow mb-4">
                                        <div class="card-img-top" style="height: 120px; background: url('uploads/Fond.png') center/cover no-repeat;"></div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($module['code_module'] . ' - ' . $module['nom_module']); ?></h5>
                                        <div class="d-flex gap-2">
                                            <a href="cours.php?module_id=<?=$module['id_module'] ?>" class="btn btn-primary btn-sm">Gérer</a>
                                            <a href="#" class="btn btn-outline-secondary btn-sm">Voir les étudiants</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                Aucun module trouvé.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Devoirs récents -->
            <section class="courses-section">
                <h2 class="section-title">Devoirs récents</h2>
                <div class="row">
                    <?php if(!empty($recent_exercises)): ?>
                        <?php foreach ($recent_exercises as $exercise): ?>
                            <div class="col-md-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-warning text-white">
                                        Exercice
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($exercise['titre']); ?></h5>
                                        <p class="card-text">
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($exercise['nom_module']); ?></span>
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted">Créé le: <?php echo date('d/m/Y', strtotime($exercise['date_creation'])); ?></small>
                                        </p>
                                        <div class="d-flex gap-2">
                                            <a href="#" class="btn btn-warning btn-sm">Voir les soumissions</a>
                                            <a href="#" class="btn btn-outline-secondary btn-sm">Modifier</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                Aucun devoir récent trouvé.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
        
        <?php if($_SESSION['role'] === 2): ?>
            <!-- Espace administrateur -->
            <section class="courses-section">
                <h2 class="section-title">Liste des Utilisateurs</h2>
                <div class="row">
                    <?php if(!empty($users)): ?>
                        <?php foreach (array_slice($users, 0, 3) as $user): ?>
                            <div class="col-md-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-info text-white">
                                        <?php echo $user['role'] == 0 ? 'Étudiant' : ($user['role'] == 1 ? 'Professeur' : 'Administrateur'); ?>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h5>
                                        <p class="card-text">
                                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                                        </p>
                                        <div class="d-flex gap-2">
                                            <a href="#" class="btn btn-info btn-sm">Profil</a>
                                            <a href="#" class="btn btn-outline-secondary btn-sm">Modifier</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($users) > 3): ?>
                            <div class="col-12 mt-3 text-center">
                                <a href="#" class="btn btn-outline-primary">Voir tous les utilisateurs (<?php echo count($users); ?>)</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                Aucun utilisateur trouvé.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Modules -->
            <section class="courses-section">
                <h2 class="section-title">Liste des Modules</h2>
                <div class="row">
                    <?php if(!empty($modules)): ?>
                        <?php foreach(array_slice($modules, 0, 3) as $module): ?>
                            <?php $color = getRandomColor(); ?>
                            <div class="col-md-4">
                                <div class="card module-card shadow">
                                        <div class="card-img-top" style="height: 120px; background: url('uploads/Fond.png') center/cover no-repeat;"></div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($module['code_module'] . ' - ' . $module['nom_module']); ?></h5>
                                        <div class="d-flex gap-2">
                                            <a href="cours.php?module_id=<?=$module['id_module'] ?>" class="btn btn-primary btn-sm">Gérer</a>
                                            <a href="#" class="btn btn-outline-secondary btn-sm">Modifier</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($modules) > 3): ?>
                            <div class="col-12 mt-3 text-center">
                                <a href="#" class="btn btn-outline-primary">Voir tous les modules (<?php echo count($modules); ?>)</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                Aucun module trouvé.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
                <section class="courses-section">
                    <h2 class="section-title">Planning</h2>
                    <div class="row">
                        <div class="col-md-4 mx-auto">
                            <div class="card shadow-sm h-100 text-center">
                                <div class="card-header bg-success text-white">
                                    <i class="fas fa-calendar-alt fa-2x"></i>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-center">
                                    <h5 class="card-title mb-3">Accéder au planning</h5>
                                    <p class="card-text">Consultez le planning général des cours et modules.</p>
                                    <a href="planning.php" class="btn btn-success mt-auto">Voir le planning</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            
                <?php endif; ?>
                </div>

    <footer class="mt-5 py-3 bg-dark text-white text-center">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> StudentFive - Tous droits réservés</p>
        </div>
    </footer>

    <script>
        // Simple script pour la navigation du slider
        document.addEventListener('DOMContentLoaded', function() {
            const dots = document.querySelectorAll('.slider-navigation .dot');
            
            dots.forEach((dot, index) => {
                dot.addEventListener('click', function() {
                    // Logique pour naviguer entre les pages du slider
                    dots.forEach(d => d.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Ici, vous pourriez ajouter la logique pour afficher les différentes pages
                    // Par exemple, avec AJAX ou en masquant/affichant des éléments
                });
            });
        });
    </script>
</body>
</html>