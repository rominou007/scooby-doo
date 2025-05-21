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
        'type' => $q['type'],
        'answers' => []
    ];
    
    if ($q['type'] === 'qcm' && !empty($q['answers'])) {
        foreach ($q['answers'] as $index => $a) {
            if (empty($a['text'])) continue;
            
            $question['answers'][] = [
                'text' => $a['text'],
                'correct' => ($q['correct'] == $index)
            ];
        }
    } elseif ($q['type'] === 'truefalse') {
        $question['answers'] = [
            ['text' => 'Vrai', 'correct' => ($q['correct'] == 0)],
            ['text' => 'Faux', 'correct' => ($q['correct'] == 1)]
        ];
    }
    
    $questions[] = $question;
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