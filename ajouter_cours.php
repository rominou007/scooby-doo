<?php
session_start();
require('db.php');

// Vérifier que l'utilisateur est admin (0)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 0) {
    header('Location: login.php');
    exit();
}

// Récupérer la liste des professeurs (role=1)
$stmt = $pdo->query("SELECT user_id, first_name, last_name FROM users WHERE role = 1");
$professeurs = $stmt->fetchAll();

// Récupérer la liste des élèves (role=2)
$stmt = $pdo->query("SELECT user_id, first_name, last_name FROM users WHERE role = 2");
$eleves = $stmt->fetchAll();

$erreur = '';
$succes = '';

// Traitement formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $module_id = intval($_POST['module_id'] ?? 0);
    $professeur_id = intval($_POST['professeur_id'] ?? 0);
    $date_cours = $_POST['date_cours'] ?? '';
    $heure_debut = $_POST['heure_debut'] ?? '';
    $heure_fin = $_POST['heure_fin'] ?? '';

    if (!$titre || !$module_id || !$professeur_id || !$date_cours || !$heure_debut || !$heure_fin) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO courses (module_id, professor_id, title, content, created_at, date_cours, heure_debut, heure_fin) VALUES (:module_id, :professor_id, :title, '', NOW(), :date_cours, :heure_debut, :heure_fin)");
        $stmt->execute([
            'module_id' => $module_id,
            'professor_id' => $professeur_id,
            'title' => $titre,
            'date_cours' => $date_cours,
            'heure_debut' => $heure_debut,
            'heure_fin' => $heure_fin,
        ]);
        $succes = "Cours ajouté avec succès.";
    }
}

// Récupérer modules pour sélection
$stmt = $pdo->query("SELECT module_id, module_name FROM modules");
$modules = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un cours</title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5">
    <h1>Ajouter un cours</h1>

    <?php if ($erreur): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php elseif ($succes): ?>
        <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
    <?php endif; ?>

    <form method="post" class="mb-4">
        <div class="mb-3">
            <label for="titre" class="form-label">Titre du cours :</label>
            <input type="text" name="titre" id="titre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="module_id" class="form-label">Module :</label>
            <select name="module_id" id="module_id" class="form-select" required>
                <option value="">-- Sélectionner un module --</option>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= $module['module_id'] ?>"><?= htmlspecialchars($module['module_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="professeur_id" class="form-label">Professeur :</label>
            <select name="professeur_id" id="professeur_id" class="form-select" required>
                <option value="">-- Sélectionner un professeur --</option>
                <?php foreach ($professeurs as $p): ?>
                    <option value="<?= $p['user_id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="date_cours" class="form-label">Date :</label>
            <input type="date" name="date_cours" id="date_cours" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="heure_debut" class="form-label">Heure de début :</label>
            <input type="time" name="heure_debut" id="heure_debut" class="form-control" required min="08:00" max="20:00">
        </div>

        <div class="mb-3">
            <label for="heure_fin" class="form-label">Heure de fin :</label>
            <input type="time" name="heure_fin" id="heure_fin" class="form-control" required min="08:00" max="20:00">
        </div>

        <button type="submit" class="btn btn-primary">Ajouter le cours</button>
    </form>
</div>

</body>
</html>
