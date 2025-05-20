<?php
// filepath: c:\xampp\htdocs\scooby-doo\classes.php
session_start();
require("db.php");

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer toutes les classes
$classes = $pdo->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);

// Pour chaque classe, récupérer les étudiants associés
$classes_etudiants = [];
foreach ($classes as $classe) {
    $stmt = $pdo->prepare("
        SELECT u.id_user, u.prenom, u.nom, u.email
        FROM user u
        INNER JOIN student_classes sc ON u.id_user = sc.student_id
        WHERE sc.class_id = :class_id AND u.role = 0
        ORDER BY u.nom, u.prenom
    ");
    $stmt->execute(['class_id' => $classe['id']]);
    $classes_etudiants[$classe['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des classes</title>
    <?php include("link.php"); ?>
</head>
<?php include("navbar.php"); ?>
<body>
<div class="container mt-5">
    <h1 class="mb-4 text-center">Liste des classes et étudiants</h1>
    <?php if (empty($classes)): ?>
        <div class="alert alert-warning text-center">Aucune classe trouvée.</div>
    <?php else: ?>
        <?php foreach ($classes as $classe): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 d-inline">
                            <?= htmlspecialchars($classe['class_name']) ?>
                            <small class="text-light ms-3">(Année : <?= htmlspecialchars($classe['année_scolaire']) ?>)</small>
                        </h4>
                    </div>
                    <a href="register.php?type=student&class_id=<?= urlencode($classe['class_id']) ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-user-plus"></i> Ajouter un étudiant
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($classes_etudiants[$classe['id']])): ?>
                        <p class="text-muted">Aucun étudiant dans cette classe.</p>
                    <?php else: ?>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Prénom</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes_etudiants[$classe['id']] as $etudiant): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($etudiant['id_user']) ?></td>
                                        <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                                        <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                                        <td><?= htmlspecialchars($etudiant['email']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>