<?php
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

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    // Ici, tu peux aussi traiter la modification des questions si tu veux
    $stmt = $pdo->prepare("UPDATE quiz SET titre = ? WHERE id_quiz = ?");
    $stmt->execute([$titre, $id_quiz]);
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
        <label>Titre du quiz</label>
        <input type="text" name="titre" value="<?= htmlspecialchars($quiz['titre']) ?>" required>
        <button type="submit">Enregistrer</button>
        <a href="modules.php">Annuler</a>
    </form>
</body>
</html>