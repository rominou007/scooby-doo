<?php
session_start();
require('db.php');

// Vérifier que l'utilisateur est connecté et a le rôle prof/admin (1 ou 2)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], [1, 2])) {
    header('Location: modules.php');
    exit;
}

// Récupérer la liste des modules pour le select
$modules = $pdo->query("SELECT id_module, nom_module FROM modules ORDER BY nom_module")->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $id_module = (int)($_POST['id_module'] ?? 0);
    $date_limite = $_POST['date_limite'] ?? '';

    if ($titre === '') {
        $errors[] = "Le titre est obligatoire.";
    }
    if ($description === '') {
        $errors[] = "La description est obligatoire.";
    }
    if ($id_module <= 0) {
        $errors[] = "Veuillez sélectionner un module valide.";
    }
    if (!$date_limite || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_limite)) {
        $errors[] = "Veuillez saisir une date limite valide (format AAAA-MM-JJ).";
    }

    if (empty($errors)) {
        // Préparer et exécuter l'insertion
        $stmt = $pdo->prepare("
            INSERT INTO devoirs (id_module, id_prof, titre, description, date_limite, date_creation)
            VALUES (:id_module, :id_prof, :titre, :description, :date_limite, NOW())
        ");
        $stmt->execute([
            ':id_module' => $id_module,
            ':id_prof' => $_SESSION['user_id'], // on suppose que user_id est stocké en session
            ':titre' => $titre,
            ':description' => $description,
            ':date_limite' => $date_limite,
        ]);

        // Redirection après succès
        header('Location: devoirs.php?success=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un devoir</title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5">
    <h1 class="mb-4 text-white">Ajouter un devoir</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="ajouter_devoir.php" method="post" class="bg-dark p-4 rounded text-white">
        <div class="mb-3">
            <label for="titre" class="form-label">Titre du devoir</label>
            <input type="text" name="titre" id="titre" class="form-control" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" rows="5" class="form-control" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label for="id_module" class="form-label">Module</label>
            <select name="id_module" id="id_module" class="form-select" required>
                <option value="">-- Sélectionnez un module --</option>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= $module['id_module'] ?>" <?= (isset($_POST['id_module']) && $_POST['id_module'] == $module['id_module']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($module['nom_module']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="date_limite" class="form-label">Date limite</label>
            <input type="date" name="date_limite" id="date_limite" class="form-control" value="<?= htmlspecialchars($_POST['date_limite'] ?? '') ?>" required>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-plus"></i> Ajouter le devoir
        </button>
        <a href="devoirs.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
