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

// Supprimer les anciennes règles de visibilité pour ce quiz (optionnel, si tu veux une seule règle à la fois)
$pdo->prepare("DELETE FROM quiz_visibilite WHERE id_quiz = ?")->execute([$id_quiz]);

$id_cible = null;
if ($cible === 'classe') $id_cible = $id_classe;
if ($cible === 'eleve') $id_cible = $id_eleve;

$stmt = $pdo->prepare("INSERT INTO quiz_visibilite (id_quiz, cible, id_cible, date_debut, date_fin) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$id_quiz, $cible, $id_cible, $date_debut, $date_fin]);

header("Location: modules.php?success=visibilite");
exit;

$visibilites = [];
$stmt = $pdo->query("SELECT * FROM quiz_visibilite");
while ($row = $stmt->fetch()) {
    $visibilites[$row['id_quiz']] = $row;
}