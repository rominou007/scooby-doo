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

<div class="container-fluid login-container">
    <div class="row h-100">
        <!-- Partie gauche avec image -->
        <div class="col-md-7 login-image d-none d-md-block"></div>

        <!-- Partie droite avec formulaire -->
        <div class="col-md-5 login-form">
            <!-- Logo au-dessus du formulaire -->
            <img src="uploads/logo_nwar.jpg" alt="Logo" class="logo">
            
            <!-- Formulaire -->
            <form method="POST" action="">
                <h2 class="text-center mb-4 text-dark">Connexion</h2>

                <div class="">
                    <label for="email" class="form-label">Adresse email</label>
                    <input required type="email" class="form-control" id="email" name="email" placeholder="exemple@email.com">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input required type="password" class="form-control" id="password" name="password">
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Se connecter</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>