<?php
session_start();
require('db.php');

// Récupérer l'ID du quiz
$id_quiz = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id_quiz) {
    die("Quiz introuvable.");
}

// Charger le quiz
$stmt = $pdo->prepare("SELECT * FROM quiz WHERE id_quiz = ?");
$stmt->execute([$id_quiz]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("Quiz non trouvé.");
}

$questions = json_decode($quiz['questions'], true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($quiz['titre']) ?></title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5 text-white">
    <h1><?= htmlspecialchars($quiz['titre']) ?></h1>
    <form>
        <?php foreach ($questions as $idx => $q): ?>
            <div class="mb-4">
                <h5><?= ($idx+1) . '. ' . htmlspecialchars($q['text']) ?></h5>
                <?php foreach ($q['answers'] as $aIdx => $a): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="q<?= $idx ?>[]" value="<?= $aIdx ?>" id="q<?= $idx ?>a<?= $aIdx ?>">
                        <label class="form-check-label" for="q<?= $idx ?>a<?= $aIdx ?>">
                            <?= htmlspecialchars($a['text']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Valider</button>
    </form>
</div>
</body>
</html>