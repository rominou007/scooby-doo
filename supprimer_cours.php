<?php
session_start();
require('db.php');

// Vérifier que l'utilisateur est un administrateur (role = 0)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 0) {
    http_response_code(403);
    echo "Accès refusé";
    exit();
}

// Vérifier que l'ID du cours est fourni et numérique
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "Requête invalide";
    exit();
}

$id_cours = intval($_POST['id']);

// Supprimer le cours dans la table `cours`
$stmt = $pdo->prepare("DELETE FROM cours WHERE id_cours = :id_cours");
$stmt->execute(['id_cours' => $id_cours]);

// Rediriger vers la page planning.php
header('Location: planning.php');
exit();
