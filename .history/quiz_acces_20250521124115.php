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

// Récupérer l'état actuel
$stmt = $pdo->prepare("SELECT visible_etudiants FROM quiz WHERE id_quiz = ?");
$stmt->execute([$id_quiz]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("Quiz non trouvé.");
}

$newState = $quiz['visible_etudiants'] ? 0 : 1;

$stmt = $pdo->prepare("UPDATE quiz SET visible_etudiants = ? WHERE id_quiz = ?");
$stmt->execute([$newState, $id_quiz]);

header("Location: modules.php");
exit;