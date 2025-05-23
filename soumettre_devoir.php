<?php
session_start();
require('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 0) {
    die("Accès non autorisé.");
}

$id_etudiant = $_SESSION['user_id'];
$devoir_id = $_POST['devoir_id'] ?? null;

if (!$devoir_id || empty($_FILES['fichier']['name'])) {
    die("Soumission invalide.");
}

$nom_fichier = basename($_FILES['fichier']['name']);
$chemin = "uploads/soumissions/" . uniqid() . "_" . $nom_fichier;

if (move_uploaded_file($_FILES['fichier']['tmp_name'], $chemin)) {
    $stmt = $pdo->prepare("
        INSERT INTO soumissions (id_devoir, id_etudiant, chemin_fichier, date_soumission)
        VALUES (:id_devoir, :id_etudiant, :chemin, NOW())
    ");
    $stmt->execute([
        'id_devoir' => $devoir_id,
        'id_etudiant' => $id_etudiant,
        'chemin' => $chemin
    ]);

    header("Location: devoir.php?devoir_id=" . $devoir_id . "&success=1");
    exit;
} else {
    die("Erreur de téléversement.");
}
