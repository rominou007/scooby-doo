<?php
session_start();
require('db.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], [1, 2])) {
    die("Accès refusé.");
}

$id_quiz = isset($_POST['id_quiz']) ? intval($_POST['id_quiz']) : 0;
if (!$id_quiz) {
    die("Quiz invalide.");
}

// Suppression du quiz (et des dépendances via ON DELETE CASCADE)
$stmt = $pdo->prepare("DELETE FROM quiz WHERE id_quiz = ?");
$stmt->execute([$id_quiz]);

header("Location: modules.php?success=quiz_deleted");
exit;