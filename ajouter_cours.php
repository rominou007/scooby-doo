<?php
session_start();
require('db.php');

// Vérifier que l'utilisateur est admin (role = 0)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 0) {
    header('Location: login.php');
    exit();
}

// Récupérer la liste des professeurs (role=1)
$stmt = $pdo->query("SELECT id_user, prenom, nom FROM user WHERE role = 1");
$professeurs = $stmt->fetchAll();

// Récupérer modules pour sélection
$stmt = $pdo->query("SELECT id_module, nom_module FROM modules");
$modules = $stmt->fetchAll();

$erreur = '';
$succes = '';

// Valeurs par défaut pour formulaire
$titre = '';
$module_id = 0;
$professeur_id = 0;
$date_cours = '';
$heure_debut = '';
$heure_fin = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $module_id = intval($_POST['module_id'] ?? 0);
    $professeur_id = intval($_POST['professeur_id'] ?? 0);
    $date_cours = $_POST['date_cours'] ?? '';
    $heure_debut = $_POST['heure_debut'] ?? '';
    $heure_fin = $_POST['heure_fin'] ?? '';

    // Vérification des champs
    if (!$titre || !$module_id || !$professeur_id || !$date_cours || !$heure_debut || !$heure_fin) {
        $erreur = "Veuillez remplir tous les champs.";
    } elseif ($heure_fin <= $heure_debut) {
        $erreur = "L'heure de fin doit être après l'heure de début.";
    } else {
        // Insertion en base
        $stmt = $pdo->prepare("INSERT INTO cours (id_module, id_prof, titre, contenu, date_creation, date_cours, heure_debut, heure_fin) VALUES (:module_id, :professeur_id, :titre, '', NOW(), :date_cours, :heure_debut, :heure_fin)");
        $stmt->execute([
            'module_id' => $module_id,
            'professeur_id' => $professeur_id,
            'titre' => $titre,
            'date_cours' => $date_cours,
            'heure_debut' => $heure_debut,
            'heure_fin' => $heure_fin,
        ]);
        $succes = "Cours ajouté avec succès.";

        // Réinitialiser les valeurs après succès
        $titre = '';
        $module_id = 0;
        $professeur_id = 0;
        $date_cours = '';
        $heure_debut = '';
        $heure_fin = '';
    }
}
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
            <input type="text" name="titre" id="titre" class="form-control" required value="<?= htmlspecialchars($titre) ?>">
        </div>

        <div class="mb-3">
            <label for="module_id" class="form-label">Module :</label>
            <select name="module_id" id="module_id" class="form-select" required>
                <option value="">-- Sélectionner un module --</option>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= $module['id_module'] ?>" <?= $module['id_module'] == $module_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($module['nom_module']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="professeur_id" class="form-label">Professeur :</label>
            <select name="professeur_id" id="professeur_id" class="form-select" required>
                <option value="">-- Sélectionner un professeur --</option>
                <?php foreach ($professeurs as $p): ?>
                    <option value="<?= $p['id_user'] ?>" <?= $p['id_user'] == $professeur_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="date_cours" class="form-label">Date :</label>
            <input type="date" name="date_cours" id="date_cours" class="form-control" required value="<?= htmlspecialchars($date_cours) ?>">
        </div>

        <div class="mb-3">
            <label for="heure_debut" class="form-label">Heure de début :</label>
            <input type="time" name="heure_debut" id="heure_debut" class="form-control" required min="08:00" max="20:00" value="<?= htmlspecialchars($heure_debut) ?>">
        </div>

        <div class="mb-3">
            <label for="heure_fin" class="form-label">Heure de fin :</label>
            <input type="time" name="heure_fin" id="heure_fin" class="form-control" required min="08:00" max="20:00" value="<?= htmlspecialchars($heure_fin) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Ajouter le cours</button>
    </form>
</div>

</body>
</html>
