<?php
session_start();
require('db.php');

// Vérification des permissions
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], [1, 2])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

// Validation des données
$id_module = filter_input(INPUT_POST, 'id_module', FILTER_VALIDATE_INT);
$titre = trim($_POST['titre'] ?? '');
$id_prof = $_SESSION['user_id'];

if (!$id_module || empty($titre) || empty($_POST['questions'])) {
    die("Données manquantes ou invalides");
}

// Construction du tableau JSON
$questions = [];
foreach ($_POST['questions'] as $q) {
    if (empty($q['text'])) continue;

    $question = [
        'text' => $q['text'],
        'type' => 'qcm',
        'answers' => []
    ];

    // Gestion QCM dynamique
    if (!empty($q['answers'])) {
        $answers = [];
        $hasCorrect = false;
        foreach ($q['answers'] as $a) {
            if (empty($a['text'])) continue;
            $isCorrect = isset($a['correct']) && $a['correct'] == '1';
            if ($isCorrect) $hasCorrect = true;
            $answers[] = [
                'text' => $a['text'],
                'correct' => $isCorrect
            ];
        }
        // Au moins 2 réponses et 1 bonne réponse
        if (count($answers) >= 2 && $hasCorrect) {
            $question['answers'] = $answers;
            $questions[] = $question;
        }
    }
}

if (count($questions) == 0) {
    die("Le quiz doit contenir au moins une question valide avec deux réponses et une bonne réponse.");
}

try {
    $stmt = $pdo->prepare("INSERT INTO quiz (id_module, id_prof, titre, questions) 
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $id_module,
        $id_prof,
        $titre,
        json_encode($questions, JSON_UNESCAPED_UNICODE)
    ]);
    
    header("Location: modules.php?success=quiz_created");
    exit;
} catch (PDOException $e) {
    die("Erreur d'enregistrement : " . $e->getMessage());
}