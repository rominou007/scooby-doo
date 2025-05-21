<?php
session_start();
require('db.php');

// Récupérer les modules pour la liste déroulante
$modules = $pdo->query("SELECT id_module, nom_module FROM modules ORDER BY nom_module")->fetchAll();

// Traitement de l'enregistrement du quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titre'], $_POST['module_id'], $_POST['questions'])) {
    $titre = trim($_POST['titre']);
    $id_module = intval($_POST['module_id']);
    $id_prof = $_SESSION['user_id'] ?? null;
    $questions = $_POST['questions'];

    // Construction du tableau de questions au format JSON
    $questions_json = [];
    foreach ($questions as $q) {
        $answers = [];
        if (isset($q['answers'])) {
            foreach ($q['answers'] as $a) {
                $answers[] = [
                    'text' => $a['text'],
                    'correct' => isset($a['correct']) && $a['correct'] == '1'
                ];
            }
        }
        $questions_json[] = [
            'text' => $q['text'],
            'type' => 'qcm',
            'answers' => $answers
        ];
    }

    // Insertion en base
    $stmt = $pdo->prepare("INSERT INTO quiz (id_module, id_prof, titre, questions) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $id_module,
        $id_prof,
        $titre,
        json_encode($questions_json, JSON_UNESCAPED_UNICODE)
    ]);
    header("Location: modules.php?success=quiz");
    exit;
}

$visible_etudiants = isset($_POST['visible_etudiants']) ? 1 : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un quiz manuellement</title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5 text-white">
    <h1>Créer un quiz manuellement</h1>
    <form action="creer_quiz_manuel.php" method="post" id="quizForm">
        <div class="mb-3">
            <label for="titre" class="form-label">Titre du quiz</label>
            <input type="text" class="form-control" name="titre" id="titre" required>
        </div>
        <div class="mb-3">
            <label for="module_id" class="form-label">Module</label>
            <select class="form-control" name="module_id" id="module_id" required>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= $module['id_module'] ?>"><?= htmlspecialchars($module['nom_module']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <hr>
        <div id="questions-container"></div>
        <div class="d-flex justify-content-between gap-2 mt-4">
            <button type="button" class="btn btn-secondary" onclick="addQuestion()">
                <i class="fas fa-plus"></i> Ajouter une question
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Enregistrer le quiz
            </button>
        </div>
    </form>
</div>

<script>
let questionIndex = 0;

function addQuestion() {
    const container = document.getElementById('questions-container');
    const qIdx = questionIndex++;
    const questionDiv = document.createElement('div');
    questionDiv.className = "border rounded p-3 mb-4 bg-dark";
    questionDiv.innerHTML = `
        <h4>Question</h4>
        <div class="mb-3">
            <label>Intitulé de la question</label>
            <input type="text" class="form-control" name="questions[${qIdx}][text]" required>
        </div>
        <div class="mb-3">
            <label>Nombre de réponses</label>
            <input type="number" min="2" max="6" value="3" class="form-control" onchange="updateAnswers(this, ${qIdx})" id="nb-rep-${qIdx}">
        </div>
        <div class="mb-3" id="answers-${qIdx}">
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentNode.remove()">Supprimer cette question</button>
    `;
    container.appendChild(questionDiv);
    updateAnswers(document.getElementById('nb-rep-' + qIdx), qIdx);
}

function updateAnswers(input, qIdx) {
    const nb = parseInt(input.value) || 2;
    const answersDiv = document.getElementById('answers-' + qIdx);
    answersDiv.innerHTML = '';
    for (let i = 0; i < nb; i++) {
        answersDiv.innerHTML += `
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="questions[${qIdx}][answers][${i}][text]" placeholder="Réponse ${i+1}" required>
                <span class="input-group-text">
                    <input type="checkbox" name="questions[${qIdx}][answers][${i}][correct]" value="1">
                    Bonne réponse
                </span>
            </div>
        `;
    }
}

// Ajoute une première question par défaut
window.onload = function() { addQuestion(); };
</script>
</body>
</html>