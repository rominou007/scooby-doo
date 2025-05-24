<?php
session_start();
require('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 0) {
    die("Accès non autorisé.");
}

if (!isset($_POST['devoir_id'])) {
    die("Requête invalide.");
}

$devoir_id = (int) $_POST['devoir_id'];
$id_etudiant = $_SESSION['user_id'];

// Vérifier que la soumission existe et appartient bien à l'étudiant connecté
$stmt = $pdo->prepare("SELECT * FROM soumission WHERE id_devoir = :devoir_id AND id_etudiant = :id_etudiant");
$stmt->execute([
    'devoir_id' => $devoir_id,
    'id_etudiant' => $id_etudiant
]);
$soumission = $stmt->fetch();

if (!$soumission) {
    die("Soumission introuvable ou accès refusé.");
}

// Supprimer le fichier physique
$fichier = $soumission['chemin_fichier'];
if (file_exists($fichier)) {
    unlink($fichier);
}

// Supprimer l'enregistrement de la base de données
$stmt = $pdo->prepare("DELETE FROM soumission WHERE id_devoir = :devoir_id AND id_etudiant = :id_etudiant");
$stmt->execute([
    'devoir_id' => $devoir_id,
    'id_etudiant' => $id_etudiant
]);

// Rediriger vers la page du devoir
header("Location: devoir_zoom.php?devoir_id=" . $devoir_id);
exit;
?>
