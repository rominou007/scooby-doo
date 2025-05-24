<?php
session_start();
require('db.php');

// Vérifier que l'utilisateur est connecté et est un professeur/admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 1 && $_SESSION['role'] != 2)) {
    header("Location: login.php");
    exit();
}

// Récupérer les paramètres
$quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
$module_id = isset($_POST['module_id']) ? intval($_POST['module_id']) : 0;
$class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
$non_completes = isset($_POST['non_completes']) ? $_POST['non_completes'] : 'ignore';
$coefficient = isset($_POST['coefficient']) ? intval($_POST['coefficient']) : 1;
$nom_devoir = isset($_POST['nom_devoir']) ? trim($_POST['nom_devoir']) : '';

// Validation des entrées
if ($quiz_id <= 0 || $module_id <= 0) {
    $_SESSION['error'] = "Paramètres invalides.";
    header("Location: notes.php");
    exit();
}

// S'assurer que le coefficient est valide
if ($coefficient < 1) $coefficient = 1;
if ($coefficient > 10) $coefficient = 10;

// S'assurer que le nom du devoir est valide
if (empty($nom_devoir)) {
    $nom_devoir = "Quiz sans titre";
}

try {
    $stmt = $pdo->prepare("SELECT q.*, m.code_module, m.nom_module FROM quiz q 
                         JOIN modules m ON q.id_module = m.id_module 
                         WHERE q.id_quiz = ? AND q.id_module = ?");
    $stmt->execute([$quiz_id, $module_id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($quiz) {
        // Calculer le nombre de questions manuellement
        $questions = json_decode($quiz['questions'], true);
        $quiz['total_questions'] = is_array($questions) ? count($questions) : 0;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération du quiz.";
    header("Location: notes.php");
    exit();
}

if (!$quiz || $quiz['total_questions'] == 0) {
    $_SESSION['error'] = "Quiz non trouvé ou aucune question disponible.";
    header("Location: notes.php");
    exit();
}

// Récupérer les résultats des étudiants pour ce quiz
$sql = "SELECT u.id_user, qr.score 
        FROM user u 
        JOIN student_classes sc ON u.id_user = sc.student_id 
        LEFT JOIN quiz_resultats qr ON u.id_user = qr.id_etudiant AND qr.id_quiz = ?
        WHERE u.role = 0";

$params = [$quiz_id];

if ($class_id > 0) {
    $sql .= " AND sc.class_id = ?";
    $params[] = $class_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traiter les résultats pour mettre à jour les notes
$notes_mises_a_jour = 0;
$max_score = $quiz['total_questions'];

foreach ($resultats as $resultat) {
    $student_id = $resultat['id_user'];
    $score = null;
    
    // Déterminer le score selon l'option choisie pour ceux qui n'ont pas complété
    if (isset($resultat['score'])) {
        // Convertir le score sur 20
        $score = ($resultat['score'] / $max_score) * 20;
    } else if ($non_completes === 'zero') {
        $score = 0;
    } else {
        // Ignorer cet étudiant s'il n'a pas complété et option 'ignore' choisie
        continue;
    }
    
    // Insérer une nouvelle note
    $stmt = $pdo->prepare("INSERT INTO notes (id_etudiant, id_module, note, nom_devoir, coefficient) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$student_id, $module_id, $score, $nom_devoir, $coefficient]);
    
    $notes_mises_a_jour++;
}

// Rediriger avec un message de succès
$_SESSION['success'] = "Notes affectées avec succès pour $notes_mises_a_jour étudiant(s) avec coefficient $coefficient.";
header("Location: notes.php?quiz_id=$quiz_id" . ($class_id ? "&class_id=$class_id" : ""));
exit();
?>