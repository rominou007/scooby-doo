<?php
session_start();
require('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5 text-center">
    <h1 class="mb-4 text-white">Bienvenue sur la plateforme</h1>
    <a href="modules.php" class="btn btn-primary btn-lg">Liste des modules</a>
</div>

</body>
</html>
