<?php
session_start();
require('db.php');

// Vérifier que l'utilisateur est un administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] != 0) {
    http_response_code(403);
    echo "Accès refusé";
    exit();
}

// Vérifier que l'ID du cours est fourni
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "Requête invalide";
    exit();
}

$course_id = intval($_POST['id']);

// Supprimer le cours
$stmt = $pdo->prepare("DELETE FROM courses WHERE course_id = :id");
$stmt->execute(['id' => $course_id]);

// Rediriger vers le planning
header('Location: planning.php');
exit();
