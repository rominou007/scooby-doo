<?php
session_start();
require('db.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'expéditeur (utilisateur connecté)
$expediteur_id = $_SESSION['user_id'];

// Récupérer l'ID du destinataire depuis l'URL
$receveur_id = isset($_GET['receveur_id']) ? (int)$_GET['receveur_id'] : 0;

// Vérifier la validité du destinataire
if ($receveur_id <= 0) {
    die("Destinataire invalide");
}

// Vérifier que l'expéditeur ne tente pas de créer une conversation avec lui-même
if ($expediteur_id == $receveur_id) {
    die("Vous ne pouvez pas créer une conversation avec vous-même");
}

// Vérifier que le destinataire existe
$stmt = $pdo->prepare("SELECT id_user, prenom, nom FROM user WHERE id_user = :receveur_id");
$stmt->execute(['receveur_id' => $receveur_id]);
$receveur = $stmt->fetch();

if (!$receveur) {
    die("Le destinataire spécifié n'existe pas");
}

// Vérifier si une conversation existe déjà entre ces deux utilisateurs
$stmt = $pdo->prepare("SELECT conversation_id FROM conversations 
                      WHERE (user1_id = :expediteur_id AND user2_id = :receveur_id)
                      OR (user1_id = :receveur_id AND user2_id = :expediteur_id)");
$stmt->execute([
    'expediteur_id' => $expediteur_id,
    'receveur_id' => $receveur_id
]);
$existing_conversation = $stmt->fetch();

// Si une conversation existe déjà, rediriger vers cette conversation
if ($existing_conversation) {
    header("Location: messagerie.php?user_id=" . $receveur_id);
    exit();
}

// Créer une nouvelle conversation
try {
    $stmt = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (:expediteur_id, :receveur_id)");
    $stmt->execute([
        'expediteur_id' => $expediteur_id,
        'receveur_id' => $receveur_id
    ]);
    
    $conversation_id = $pdo->lastInsertId();
    
    
    
    // Rediriger vers la nouvelle conversation
    header("Location: messagerie.php?converstion_id=" . $conversation_id);
    exit();
} catch (PDOException $e) {
    die("Erreur lors de la création de la conversation : " . $e->getMessage());
}
?>