<?php
session_start();
require('db.php');

// Récupérer tous les modules avec les bons noms de colonnes
$stmt = $pdo->query("SELECT * FROM modules ORDER BY date_creation DESC");
$modules = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Modules</title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5">
    <h1 class="mb-4 text-white">Liste des modules</h1>

    <!-- Ajouter un module si admin ou professeur -->
    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], [0, 1])): ?>
        <a href="ajouter_module.php" class="btn btn-success mb-3">Ajouter un module</a>
    <?php endif; ?>

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
</div>

</body>
</html>
