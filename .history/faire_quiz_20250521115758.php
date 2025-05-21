<?php
session_start();
require('db.php');

// Récupérer l'ID du quiz (GET pour affichage, POST pour correction)
$id_quiz = isset($_POST['id_quiz']) ? intval($_POST['id_quiz']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
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

// Correction du quiz si formulaire soumis
$score = null;
$total = count($questions);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    foreach ($questions as $idx => $q) {
        $userAnswers = isset($_POST["q$idx"]) ? $_POST["q$idx"] : [];
        $correctAnswers = [];
        foreach ($q['answers'] as $aIdx => $a) {
            if ($a['correct']) $correctAnswers[] = (string)$aIdx;
        }
        // On compare les réponses utilisateur et les bonnes réponses
        sort($userAnswers);
        sort($correctAnswers);
        if ($userAnswers == $correctAnswers) {
            $score++;
        }
    }
}
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

    <?php if ($score !== null): ?>
        <div class="alert alert-info">
            Votre score : <strong><?= $score ?> / <?= $total ?></strong>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id_quiz" value="<?= $quiz['id_quiz'] ?>">
        <?php foreach ($questions as $idx => $q): ?>
            <div class="mb-4">
                <h5><?= ($idx+1) . '. ' . htmlspecialchars($q['text']) ?></h5>
                <?php foreach ($q['answers'] as $aIdx => $a): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="q<?= $idx ?>[]" value="<?= $aIdx ?>" id="q<?= $idx ?>a<?= $aIdx ?>"
                            <?= (isset($_POST["q$idx"]) && in_array($aIdx, (array)$_POST["q$idx"])) ? 'checked' : '' ?>
                            <?= ($score !== null) ? 'disabled' : '' ?>
                        >
                        <label class="form-check-label" for="q<?= $idx ?>a<?= $aIdx ?>">
                            <?= htmlspecialchars($a['text']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <?php if ($score === null): ?>
            <button type="submit" class="btn btn-primary">Valider</button>
        <?php endif; ?>
    </form>
</div>
</body>
</html>