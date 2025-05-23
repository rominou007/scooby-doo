<?php
session_start();
require('db.php');

// Vérification des droits
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], [1, 2])) {
    die("Accès refusé.");
}

$id_quiz = isset($_POST['id_quiz']) ? intval($_POST['id_quiz']) : 0;
$cible = $_POST['cible'] ?? 'tous';
$date_debut = $_POST['date_debut'] ?? null;
$date_fin = $_POST['date_fin'] ?? null;
$id_classe = $_POST['id_classe'] ?? null;
$id_eleve = $_POST['id_eleve'] ?? null;

if (!$id_quiz || !$date_debut) {
    die("Données manquantes.");
}

// Pour l’instant, on ne gère que la visibilité globale (tous les étudiants)
if ($cible === 'tous') {
    $stmt = $pdo->prepare("UPDATE quiz SET visible_etudiants = 1 WHERE id_quiz = ?");
    $stmt->execute([$id_quiz]);
    // Tu peux aussi enregistrer date_debut/date_fin dans une table dédiée si besoin
}

// À compléter : gestion par classe ou élève

header("Location: modules.php?success=visibilite");
exit;