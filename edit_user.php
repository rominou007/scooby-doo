<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header("Location: login.php");
    exit();
}

// Update user from POST data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require("db.php");
    
    // Récupération des données du formulaire
    $id_user = $_POST["id_user"];
    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $email = $_POST["email"];
    $acces = $_POST["acces"]; // Renommé de "role" à "acces" pour correspondre au formulaire

    // Validation des données
    if(empty($nom) || empty($prenom) || empty($email)){
        $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires!";
        header("Location: users.php");
        exit();
    }

    try {
        // Mise à jour de l'utilisateur
        $sql = "UPDATE user SET nom = :nom, prenom = :prenom, email = :email, role = :role WHERE id_user = :id_user";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'role' => $acces,
            'id_user' => $id_user
        ]);
        
        $_SESSION['success'] = "Les informations de l'utilisateur ont été mises à jour avec succès.";
        header("Location: users.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la mise à jour: " . $e->getMessage();
        header("Location: users.php");
        exit();
    }
} else {
    // Redirection si accès direct à la page
    header("Location: users.php");
    exit();
}