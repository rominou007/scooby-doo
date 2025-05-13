<?php
    session_start();
    require("db.php");

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = trim($_POST["email"]);
            $password = $_POST["password"];

            if(empty($email) || empty($password)){
                die("Veuillez remplir tous les champs !");
            }

            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            
            $user = $stmt->fetch();


            if($user && password_verify($password, $user['password_hash'])){
            
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
    <title>Connexion</title>
    
    <?php include("link.php"); ?>
</head>

<?php include("navbar.php"); ?>
<body class="bg-secondary">

    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <form action="" class="p-4 rounded shadow bg-light" method="post">
                    
                    <h1 class="text-center text-danger mb-4">Connexion</h1>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input required type="email" class="form-control" id="email" name="email" placeholder="exemple@email.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input required type="password" class="form-control" id="password" name="password">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="submit" class="btn btn-primary">Se connecter</button>
                        <a href="register.php" class="btn btn-outline-primary">S'inscrire</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>