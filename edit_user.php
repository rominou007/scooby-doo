<?php

//update user form post

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require("db.php");
    $user_id = $_POST["id_user"];
    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $role = $_POST["role"];

    if(empty($username) || empty($name)){
        die("Veuillez remplir tous les champs !");
    }

    $sql = "UPDATE user SET nom = :nom, prenom = :prenom,  role = :role WHERE id_user = :id_user";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([, 'nom' => $nom, 'prenom' => $prenom, 'role' => $role, 'user_id' => $id_user]);
    
    header("location: users.php");
} else {
    header("location: users.php");
}