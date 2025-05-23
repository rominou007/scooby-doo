<?php
session_start();
require('db.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], [1, 2])) {
    die("Accès refusé.");
}

$id_quiz = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id_quiz) die("Quiz invalide.");

// Récupère le quiz
$stmt = $pdo->prepare("SELECT * FROM quiz WHERE id_quiz = ?");
$stmt->execute([$id_quiz]);
$quiz = $stmt->fetch();
if (!$quiz) die("Quiz introuvable.");

// Récupère les modules pour la liste déroulante
$modules = [];
foreach ($pdo->query("SELECT id_module, nom_module FROM modules") as $row) {
    $modules[] = $row;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $id_module = intval($_POST['module_id']);
    $stmt = $pdo->prepare("UPDATE quiz SET titre = ?, id_module = ? WHERE id_quiz = ?");
    $stmt->execute([$titre, $id_module, $id_quiz]);
    header("Location: modules.php?success=quiz_modified");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le quiz</title>
</head>
<body>
    <h1>Modifier le quiz</h1>
    <form method="post">
        <div>
            <label for="titre">Titre du quiz</label>
            <input type="text" name="titre" id="titre" value="<?= htmlspecialchars($quiz['titre']) ?>" required>
        </div>
        <div>
            <label for="module_id">Module</label>
            <select name="module_id" id="module_id" required>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= $module['id_module'] ?>" <?= $quiz['id_module'] == $module['id_module'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($module['nom_module']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Enregistrer</button>
        <a href="modules.php">Annuler</a>
    </form>
</body>
</html>