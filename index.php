<?php
session_start();
require("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        die("Veuillez remplir tous les champs !");
    }

    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];

        header("location: home.php");
    } else {
        die("Adresse email ou mot de passe incorrect !");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur Studient Five</title>
    <?php include("link.php"); ?>
</head>

<body class="bg-secondary">

    <div class="container text-center mt-5">
        <h1 class="text-light">Bienvenue sur Studient Five</h1>
        <p class="text-light">Veuillez choisir une option pour vous connecter :</p>

        <div class="row justify-content-center mt-4">
            <div class="col-md-4">
                <a href="login.php?role=admin" class="btn btn-danger btn-lg w-100">Se connecter en tant qu'Admin</a>
            </div>
            <div class="col-md-4">
                <a href="login.php?role=prof" class="btn btn-warning btn-lg w-100">Se connecter en tant que Professeur</a>
            </div>
            <div class="col-md-4">
                <a href="login.php?role=etudiant" class="btn btn-primary btn-lg w-100">Se connecter en tant qu'Étudiant</a>
            </div>
        </div>
    </div>

</body>
</html>