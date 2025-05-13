<?php
session_start();
require("db.php");

// Vérifie les autorisations
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'prof'])) {
    die("Accès interdit.");
}

if (!isset($_GET['module_id'])) {
    die("Aucun module spécifié.");
}

$module_id = intval($_GET['module_id']);

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titre = trim($_POST['titre']);
    $url = trim($_POST['url']);

    if (empty($titre) || empty($url)) {
        $error = "Tous les champs sont requis.";
    } else {
        $sql = "INSERT INTO documents (titre, url, module_id) VALUES (:titre, :url, :module_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'titre' => $titre,
            'url' => $url,
            'module_id' => $module_id
        ]);

        header("Location: cours.php?id=" . $module_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un document</title>
    <?php include("link.php"); ?>
</head>
<body class="bg-secondary">
    <?php include("navbar.php"); ?>

    <div class="container mt-5">
        <h2 class="text-white">Ajouter un document</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="bg-light p-4 rounded shadow">
            <div class="mb-3">
                <label for="titre" class="form-label">Titre du document</label>
                <input type="text" name="titre" id="titre" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="url" class="form-label">Lien (URL) du document</label>
                <input type="url" name="url" id="url" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Ajouter</button>
            <a href="cours.php?id=<?= $module_id ?>" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</body>
</html>
