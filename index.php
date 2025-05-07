<?php
    session_start();
    require("db.php");

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $mail = trim($_POST["mail"]);
            $password = $_POST["password"];

            if(empty($mail) || empty($password)){
                die("Veuillez remplir tous les champs !");
            }

            $sql = "SELECT * FROM users WHERE mail = :mail";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['mail' => $mail]);
            
            $user = $stmt->fetch();


            if($user && password_verify($password, $user['mdp'])){
            
                $_SESSION['username'] = $user['username'];
                $_SESSION['id'] = $user['id'];
                $_SESSION['access'] = $user['access'];

                header("location: home.php");
            }else{
                
                die("Nom d'utilisateur ou mot de passe incorrect !");
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
<body class="bg-secondary">

    <form action="" class="p-3 mt-3 rounded shadow container bg-light" method="post">
        
    <h1 class="text-center text-danger">Login</h1>
            <label for="mail" class="form-label">Mail</label>
            <input required type="text" class="form-control" id="mail" name="mail">
            <label for="password" class="form-label">Password</label>
            <input required type="password" class="form-control" id="password" name="password">


            <input type="submit" value="login" href="" class="mt-3 btn btn-primary">

            <a href="register.php" class="mt-3 ms-4 btn btn-outline-primary">register</a>
    </form>
</body>
</html>