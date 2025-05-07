<?php

//update user form post

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require("db.php");
    $id = $_POST["id"];
    $username = $_POST["username"];
    $name = $_POST["name"];
    $access = $_POST["access"];

    if(empty($username) || empty($name)){
        die("Veuillez remplir tous les champs !");
    }

    $sql = "UPDATE users SET username = :username, name = :name, access = :access WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username, 'name' => $name, 'access' => $access, 'id' => $id]);
    
    header("location: users.php");
} else {
    header("location: users.php");
}