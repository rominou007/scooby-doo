<?php
session_start();
require('db.php');

$stmt = $pdo->query("
    SELECT d.*, m.nom_module, u.nom AS prof_nom 
    FROM devoirs d
    JOIN modules m ON d.id_module = m.id_module
    LEFT JOIN user u ON d.id_prof = u.id_user
    ORDER BY d.date_limite DESC, d.date_creation DESC
");
$devoirs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des devoirs</title>
    <?php include("link.php"); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5">
    <h1 class="mb-4 text-white">Liste des devoirs</h1>

    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], [1, 2])): ?>
        <a href="ajouter_devoir.php" class="btn btn-success mb-4">
            <i class="fas fa-plus"></i> Ajouter un devoir
        </a>
    <?php endif; ?>

    <?php if (empty($devoirs)): ?>
        <div class="alert alert-info">Aucun devoir n’a encore été ajouté.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($devoirs as $devoir): ?>
                <div class="col-md-6 mb-4">
                    <div class="card bg-dark text-white h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="devoir_zoom.php?devoir_id=<?= $devoir['id_devoir'] ?>" class="text-white text-decoration-underline">
                                    <?= htmlspecialchars($devoir['titre']) ?>
                                </a>
                            </h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars($devoir['description'])) ?></p>
                            <p>
                                <strong>Module :</strong> <?= htmlspecialchars($devoir['nom_module']) ?><br>
                                <strong>Professeur :</strong> <?= htmlspecialchars($devoir['prof_nom'] ?? 'Système') ?><br>
                                <strong>Date limite :</strong> <?= date('d/m/Y', strtotime($devoir['date_limite'])) ?><br>
                                <strong>Créé le :</strong> <?= date('d/m/Y H:i', strtotime($devoir['date_creation'])) ?>
                            </p>
                        </div>
                        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], [1, 2])): ?>
                            <div class="card-footer d-flex justify-content-end">
                                <a href="supprimer_devoir.php?id=<?= $devoir['id_devoir'] ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce devoir ?');" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
