<?php
session_start();
require('db.php');

// Récupérer tous les modules
$stmt = $pdo->query("SELECT * FROM modules ORDER BY date_creation DESC");
$modules = $stmt->fetchAll();

// Récupérer les quiz existants
$quizs = $pdo->query("
    SELECT q.*, m.nom_module, u.nom AS prof_nom 
    FROM quiz q
    JOIN modules m ON q.id_module = m.id_module
    LEFT JOIN user u ON q.id_prof = u.id_user
    ORDER BY q.date_creation DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Modules</title>
    <?php include("link.php"); ?>
    <!-- Ajout de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5">
    <h1 class="mb-4 text-white">Liste des modules</h1>

    <!-- Boutons d'action -->
    <div class="d-flex gap-2 mb-4">
        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], [1, 2])): ?>
            <a href="ajouter_module.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Ajouter un module
            </a>
            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#quizModal">
                <i class="fas fa-question-circle"></i> Créer un quiz
            </button>
        <?php endif; ?>
    </div>

    <!-- Modal Quiz (à placer avant la liste des modules) -->
    <div class="modal fade" id="quizModal" tabindex="-1" aria-hidden="true">
        <!-- [Contenu du modal identique à celui fourni précédemment] -->
        <!-- Gardez exactement le même code que dans la réponse précédente -->
    </div>

    <!-- Liste des modules (votre code existant inchangé) -->
    <?php if (empty($modules)): ?>
        <div class="alert alert-info">Aucun module n’a encore été ajouté.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($modules as $module): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($module['nom_module']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($module['description']) ?></p>
                            <a href="cours.php?module_id=<?= $module['id_module'] ?>" class="btn btn-primary">Voir le module</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Section dédiée aux quiz (Nouveau) -->
    <div class="mt-5">
        <h2 class="text-white mb-4">
            <i class="fas fa-question"></i> Quiz disponibles
        </h2>
        
        <?php if (empty($quizs)): ?>
            <div class="alert alert-info">Aucun quiz disponible.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($quizs as $quiz): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 bg-dark text-white">
                            <div class="card-body">
                                <h5><?= htmlspecialchars($quiz['titre']) ?></h5>
                                <p class="text-muted small">
                                    <i class="fas fa-book"></i> <?= htmlspecialchars($quiz['nom_module']) ?><br>
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($quiz['prof_nom'] ?? 'Système') ?><br>
                                    <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($quiz['date_creation'])) ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary">
                                        <?= count(json_decode($quiz['questions'], true)) ?> questions
                                    </span>
                                    <a href="passer_quiz.php?id=<?= $quiz['id_quiz'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play"></i> Commencer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Scripts JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// [Le script JavaScript fourni précédemment reste identique]
</script>
</body>
</html>