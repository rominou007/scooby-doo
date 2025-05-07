<?php


    require("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $name = trim($_POST["name"]);
    $email = trim($_POST["mail"]);
    $phone = trim($_POST["phone"]);
    $sexe = $_POST["sexe"];
    $password = $_POST["password"];
    $confirm_password = $_POST["password_conf"];


    if(strlen($password) < 8){
        die("Le mot de passe doit contenir au moins 8 caractères");
    }

    // Vérifier si les mots de passe correspondent
    if ($password !== $confirm_password) {
        die("Les mots de passe ne correspondent pas !");
    }

    // Hacher le mot de passe pour plus de sécurité
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    //check id user already exists
    $sql = "SELECT * FROM users WHERE mail = :mail";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['mail' => $email]);
    $user = $stmt->fetch();

    if($user){
        die("Cet utilisateur existe déjà !");
    }


    try {
        // Insérer les données dans la base de données
        $sql = "INSERT INTO users (name, username, mail, phone, mdp, sexe,access) VALUES (:name, :username, :mail, :phone, :mdp, :sexe, :access)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'mail' => $email,
            'name' => $name,
            'username' => $username,
            'phone' => $phone,
            'mdp' => $hashed_password,
            'sexe' => $sexe,
            "access" => 0
        ]);
        header("location: index.php");
    } catch (PDOException $e) {
        die("Erreur lors de l'enregistrement : " . $e->getMessage());
    }
}

    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php include("link.php"); ?>
</head>

<?php include("navbar.php"); ?>
<body class=" bg-secondary">

    <form action="" class="p-3 mt-3 rounded shadow container bg-light" method="post">
        
        <h1 class="text-center text-danger">Register</h1>
            <label for="username" class="form-label">Username</label>
            <input required type="text" class="form-control" id="username" name="username">
            <label for="username" class="form-label">Name</label>
            <input required type="text" class="form-control" id="name" name="name">
            <label for="password" class="form-label">Password</label>
            <input required type="password" class="form-control" id="password" name="password">
            <label for="password_conf" class="form-label">Confirm Password</label>
            <input required type="password_conf" class="form-control" id="password_conf" name="password_conf">
            <label for="email" class="form-label">Email</label>
            <input required type="email" class="form-control" id="mail" name="mail">
            <label for="phone" class="form-label">Phone</label>
            <input required type="text" class="form-control" id="phone" name="phone">
            <label for="sexe" class="form-label">Sexe</label>
            <select name="sexe" id="sexe" class="form-control">
                <option value="homme">Homme</option>
                <option value="femme">Femme</option>
                <option value="autre">Autre</option>    
            </select>

            <br>
        
            <input type="submit" value="register" href="" class="mt-3 btn btn-primary">

            <a href="index.php" class="mt-3 ms-4 btn btn-outline-primary">Login</a>
    </form>
</body>
</html>