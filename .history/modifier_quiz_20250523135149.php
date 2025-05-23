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

// Décodage des questions
$questions = json_decode($quiz['questions'], true);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $id_module = intval($_POST['module_id']);

    // Reconstruit les questions depuis le formulaire
    $new_questions = [];
    foreach ($_POST['question'] as $i => $qtext) {
        $answers = [];
        foreach ($_POST['answer'][$i] as $j => $atext) {
            $answers[] = [
                'text' => $atext,
                'correct' => isset($_POST['correct'][$i][$j]) ? true : false
            ];
        }
        $new_questions[] = [
            'text' => $qtext,
            'type' => 'qcm',
            'answers' => $answers
        ];
    }

    $stmt = $pdo->prepare("UPDATE quiz SET titre = ?, id_module = ?, questions = ? WHERE id_quiz = ?");
    $stmt->execute([$titre, $id_module, json_encode($new_questions, JSON_UNESCAPED_UNICODE), $id_quiz]);
    header("Location: modules.php?success=quiz_modified");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le quiz</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container mt-5">
    <h1 class="mb-4">Modifier le quiz</h1>
    <form method="post" class="bg-secondary p-4 rounded">
        <div class="mb-3">
            <label for="titre" class="form-label">Titre du quiz</label>
            <input type="text" name="titre" id="titre" class="form-control" value="<?= htmlspecialchars($quiz['titre']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="module_id" class="form-label">Module</label>
            <select name="module_id" id="module_id" class="form-control" required>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= $module['id_module'] ?>" <?= $quiz['id_module'] == $module['id_module'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($module['nom_module']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <hr class="bg-light">
        <h4>Questions</h4>
        <?php foreach ($questions as $i => $q): ?>
            <div class="mb-4 p-3 bg-dark rounded border border-info">
                <div class="mb-2">
                    <label class="form-label">Question <?= $i+1 ?></label>
                    <input type="text" name="question[<?= $i ?>]" class="form-control" value="<?= htmlspecialchars($q['text']) ?>" required>
                </div>
                <div>
                    <label class="form-label">Réponses</label>
                    <?php foreach ($q['answers'] as $j => $a): ?>
                        <div class="input-group mb-2">
                            <input type="text" name="answer[<?= $i ?>][<?= $j ?>]" class="form-control" value="<?= htmlspecialchars($a['text']) ?>" required>
                            <span class="input-group-text">
                                <input type="checkbox" name="correct[<?= $i ?>][<?= $j ?>]" <?= $a['correct'] ? 'checked' : '' ?>>
                                <span class="ms-1">Bonne réponse</span>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="modules.php" class="btn btn-secondary ms-2">Annuler</a>
    </form>
</div>
</body>
</html>