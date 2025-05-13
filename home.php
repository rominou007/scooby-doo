<?php
session_start();
require('db.php');

// Récupérer tous les modules
$stmt = $pdo->query("SELECT * FROM modules ORDER BY created_at DESC");
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
    <h1 class="mb-4">Liste des modules</h1>

    <!-- Ajouter un module si admin ou professeur -->
    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 2 || $_SESSION['role'] == 1)): ?> <!-- 2=admin, 1=professeur -->
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
                            <h5 class="card-title"><?= htmlspecialchars($module['module_name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($module['description']) ?></p>
                            <a href="cours.php?module_id=<?= $module['module_id'] ?>" class="btn btn-primary">Voir le module</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
