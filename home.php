<?php
    session_start();
    require("db.php");
    if(!isset($_SESSION['id'])){
        header("location: index.php");
        return;
    }
    $listUsers = $pdo->query("SELECT * FROM users")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <?php include("link.php"); ?>
</head>
<?php include("navbar.php"); ?>
<body>
    <h1 class="text-center mt-1">Bienvenue <?php echo $_SESSION['username']; ?></h1>

</body>
</html> 