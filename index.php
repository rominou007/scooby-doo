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


            if($user && password_verify($password, $user['password_hash'])){
                // Stocker les informations de l'utilisateur dans la session
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['user_id'];
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
                <a href="login.php" class="btn btn-danger btn-lg w-100">Se connecter</a>
            </div>
        </div>
    </div>

</body>
</html>