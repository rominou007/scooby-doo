<?php
session_start();
require('db.php');

// Vérification connexion
if (!isset($_SESSION['user_id'])) {
    die("Veuillez vous connecter.");
}

// Récupération de l'utilisateur
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM user WHERE id_user = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

if (!$user) die("Utilisateur introuvable.");

// Changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if ($_POST['new_password'] === $_POST['confirm_password']) {
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE user SET mdp = :password WHERE id_user = :id_user")
            ->execute(['password' => $hashed, 'id_user' => $user_id]);
        $password_success = "Mot de passe mis à jour.";
    } else {
        $password_error = "Les mots de passe ne correspondent pas.";
    }
}

// Suppression de son propre compte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_own_account'])) {
    $pdo->prepare("DELETE FROM user WHERE user_id = :user_id")
        ->execute(['user_id' => $user_id]);
    session_destroy();
    header("Location: login.php");
    exit;
}

// Admin : supprimer un autre utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account']) && $user['role'] === 'admin') {
    $target = $_POST['account_to_delete'];
    if ($target != $user_id) {
        $pdo->prepare("DELETE FROM user WHERE user_id = :user_id")->execute(['user_id' => $target]);
    }
}

// Supprimer un cours (admin + prof)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course']) && in_array($user['role'], ['admin', 'professor'])) {
    $pdo->prepare("DELETE FROM modules WHERE module_id = :id")->execute(['id' => $_POST['course_to_delete']]);
}

// Données
if ($user['role'] === 'admin') {
    $users = $pdo->query("SELECT * FROM user WHERE user_id != $user_id")->fetchAll();
}
if (in_array($user['role'], ['admin', 'professor'])) {
    $modules = $pdo->query("SELECT * FROM modules")->fetchAll();
}
function getRoleName($role) {
    switch ($role) {
        case 0: return 'Étudiant';
        case 1: return 'Professeur';
        case 2: return 'Administrateur';
        default: return 'Inconnu';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres</title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5">
    <h1 class="text-white">Paramètres</h1>

    <!-- Message de bienvenue -->
    <div class="alert alert-info">
        <p>Connecté en tant que <?= getRoleName($user['role']) ?>.</p>
    </div>

    <!-- Infos compte + actions personnelles -->
    <div class="card mb-4">
        <div class="card-header"><h5>Informations du compte</h5></div>
        <div class="card-body">
            <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>

            <button class="btn btn-secondary mb-3" data-bs-toggle="collapse" data-bs-target="#changePasswordForm">Changer le mot de passe</button>

            <form method="POST" class="collapse mb-3" id="changePasswordForm">
                <div class="mb-2">
                    <input type="password" name="new_password" placeholder="Nouveau mot de passe" class="form-control" required>
                </div>
                <div class="mb-2">
                    <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" class="form-control" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary">Valider</button>
            </form>

            <?php if (isset($password_success)): ?>
                <div class="alert alert-success"><?= $password_success ?></div>
            <?php elseif (isset($password_error)): ?>
                <div class="alert alert-danger"><?= $password_error ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return confirm('Supprimer votre compte ? Cette action est irréversible.');">
                <button type="submit" name="delete_own_account" class="btn btn-danger">Supprimer mon compte</button>
            </form>
        </div>
    </div>

    <?php if ($user['role'] === 'admin'): ?>
    <!-- Gestion utilisateurs -->
    <div class="card mb-4">
        <div class="card-header"><h5>Gérer les utilisateurs</h5></div>
        <div class="card-body">
            <form method="POST">
                <select name="account_to_delete" class="form-control mb-2" required>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['username']) ?> (<?= $u['role'] ?>)</option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="delete_account" class="btn btn-danger">Supprimer</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if (in_array($user['role'], ['admin', 'professor'])): ?>
    <!-- Gestion cours -->
    <div class="card mb-4">
        <div class="card-header"><h5>Gérer les cours</h5></div>
        <div class="card-body">
            <form method="POST">
                <select name="course_to_delete" class="form-control mb-2" required>
                    <?php foreach ($modules as $m): ?>
                        <option value="<?= $m['module_id'] ?>"><?= htmlspecialchars($m['module_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="delete_course" class="btn btn-danger">Supprimer</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
