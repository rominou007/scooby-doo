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

        // Requêtes et variables non utilisées supprimées: recent_grades, recent_exercises
        
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

        // Requête et variable non utilisée supprimée: recent_exercises 
    } elseif ($user_role === 2) { // Administrateur
        // Ces variables ne sont pas utilisées dans l'interface, mais gardées pour référence future
        // car elles sont susceptibles d'être utilisées lors du développement ultérieur
        
        // Récupérer tous les modules (utilisé dans l'interface)
        $modules_query = "
            SELECT m.id_module, m.code_module, m.nom_module, m.description
            FROM modules m
            ORDER BY m.nom_module ASC
        ";
        $modules_stmt = $pdo->prepare($modules_query);
        $modules_stmt->execute();
        $modules = $modules_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Requêtes et variables non utilisées supprimées: users, courses
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

    <div class="container mt-2">
        <div class="row mb-2">
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
                <h2 class="section-title">MODULES</h2>
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
            
        <?php endif; ?>
        
        <?php if($_SESSION['role'] === 2): ?>
            <!-- Espace administrateur -->
            <section class="courses-section">
                <h2 class="section-title">Section Admin</h2>
                <div class="row">
                    <!-- Carte "Tous les utilisateurs" -->
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100 text-center">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center">
                                <h5 class="card-title mb-3">Tous les utilisateurs</h5>
                                <p class="card-text">Afficher la liste complète des utilisateurs.</p>
                                <a href="users.php" class="btn btn-primary mt-auto">Voir tout</a>
                            </div>
                        </div>
                    </div>

                    <!-- Carte "Planning" -->
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100 text-center">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center">
                                <h5 class="card-title mb-3">Planning</h5>
                                <p class="card-text">Consultez le planning général des cours et modules.</p>
                                <a href="planning.php" class="btn btn-success mt-auto">Voir le planning</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <script>
        // Simple script pour la navigation du slider
        document.addEventListener('DOMContentLoaded', function() {
            const dots = document.querySelectorAll('.slider-navigation .dot');
            
            dots.forEach((dot, index) => {
                dot.addEventListener('click', function() {
                    // Logique pour naviguer entre les pages du slider
                    dots.forEach(d => d.classList.remove('active'));
                    this.classList.add('active');
                    
                    
                });
            });});
    </script>
</body>
</html>