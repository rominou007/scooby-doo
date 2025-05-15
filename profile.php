<?php
session_start();
require('db.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Veuillez vous connecter pour voir votre profil.");
}

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM user WHERE id_user = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur introuvable");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5">
    <h1>Profil de <?= htmlspecialchars($user['username']) ?></h1>

    <table class="table">
        <tr>
            <th>ID</th>
            <td><?= htmlspecialchars($user['user_id']) ?></td>
        </tr>
        <tr>
            <th>Nom</th>
            <td><?= htmlspecialchars($user['nom_user']) ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= htmlspecialchars($user['email']) ?></td>
        </tr>
        <tr>
            <th>Rôle</th>
            <td><?= htmlspecialchars($user['role']) ?></td>
        </tr>
        <tr>
            <th>Date de création</th>
            <td><?= $user['created_at'] ?></td>
        </tr>
    </table>

    <a href="edit_profile.php" class="btn btn-warning">Modifier mon profil</a>
</div>

</body>
</html>
