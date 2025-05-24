<?php
session_start();
require('db.php');

if (!isset($_GET['devoir_id'])) {
    die("Devoir non spécifié.");
}

$devoir_id = (int) $_GET['devoir_id'];

// Récupérer le devoir
$stmt = $pdo->prepare("
    SELECT d.*, m.nom_module
    FROM devoirs d
    JOIN modules m ON d.id_module = m.id_module
    WHERE d.id_devoir = :id_devoir
");
$stmt->execute(['id_devoir' => $devoir_id]);
$devoir = $stmt->fetch();

if (!$devoir) {
    die("Devoir introuvable.");
}

$is_prof = isset($_SESSION['role']) && in_array($_SESSION['role'], [1, 2]);
$is_student = isset($_SESSION['role']) && $_SESSION['role'] === 0;

$soumission_exist = false;
if ($is_student) {
    $stmt2 = $pdo->prepare("SELECT * FROM soumission WHERE id_devoir = :id_devoir AND id_etudiant = :id_etudiant");
    $stmt2->execute([
        'id_devoir' => $devoir_id,
        'id_etudiant' => $_SESSION['user_id']
    ]);
    $soumission_exist = $stmt2->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($devoir['titre']) ?> - <?= htmlspecialchars($devoir['nom_module']) ?></title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>
<div class="container mt-5 text-white">
    <h1><?= htmlspecialchars($devoir['titre']) ?></h1>
    <p><?= nl2br(htmlspecialchars($devoir['description'])) ?></p>
    <p><strong>Date limite :</strong> <?= date('d/m/Y H:i', strtotime($devoir['date_limite'])) ?></p>

    <?php if ($is_student): ?>
        <h3>Soumettre votre devoir</h3>

        <?php if ($soumission_exist): ?>
            <div class="alert alert-info">
                Vous avez déjà soumis ce devoir le <?= htmlspecialchars($soumission_exist['date_soumission']) ?>.
            </div>
            <a href="<?= htmlspecialchars($soumission_exist['chemin_fichier']) ?>" target="_blank" class="btn btn-secondary mb-3">Voir votre soumission</a>

            <!-- Formulaire de suppression -->
            <form method="post" action="supprimer_soumission.php" onsubmit="return confirm('Voulez-vous vraiment supprimer votre soumission ?');" class="mb-3">
                <input type="hidden" name="devoir_id" value="<?= $devoir_id ?>">
                <button type="submit" class="btn btn-danger">Supprimer la soumission</button>
            </form>
        <?php else: ?>
            <form method="post" enctype="multipart/form-data" action="soumettre_devoir.php">
                <input type="hidden" name="devoir_id" value="<?= $devoir_id ?>">
                <div class="mb-3">
                    <label for="fichier" class="form-label">Votre fichier (PDF, DOCX...)</label>
                    <input type="file" name="fichier" id="fichier" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                    <textarea name="commentaire" id="commentaire" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Soumettre</button>
            </form>
        <?php endif; ?>

    <?php elseif ($is_prof): ?>
        <h3>Soumissions des étudiants</h3>
        <ul>
        <?php
            $stmt = $pdo->prepare("
                SELECT s.*, u.nom, u.prenom
                FROM soumission s
                JOIN user u ON s.id_etudiant = u.id_user
                WHERE s.id_devoir = :id_devoir
                ORDER BY s.date_soumission DESC
            ");
            $stmt->execute(['id_devoir' => $devoir_id]);
            $soumissions = $stmt->fetchAll();

            if (empty($soumissions)) {
                echo "<li>Aucune soumission pour le moment.</li>";
            } else {
                foreach ($soumissions as $s) {
                    echo "<li><strong>" . htmlspecialchars($s['prenom']) . " " . htmlspecialchars($s['nom']) . "</strong> - Soumis le " . htmlspecialchars($s['date_soumission']) . " : 
                        <a href='" . htmlspecialchars($s['chemin_fichier']) . "' target='_blank'>Télécharger</a></li>";
                }
            }
        ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-warning">Vous devez être connecté en tant qu'étudiant ou professeur pour accéder à ce devoir.</div>
    <?php endif; ?>
</div>
</body>
</html>
