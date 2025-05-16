<?php
session_start();
require("db.php");

// Vérifier que l'utilisateur est connecté et a le droit d'ajouter un module
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], [2, 1])) {
    header("Location: home.php");
    exit;
}

$erreur = "";
$success = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_module = trim($_POST['code_module']);
    $nom_module = trim($_POST['nom_module']);
    $description = trim($_POST['description']);

    // Validation
    if (empty($code_module) || empty($nom_module)) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO modules (code_module, nom_module, description, date_creation) VALUES (:code_module, :nom_module, :description, NOW())");
            $stmt->execute([
                'code_module' => $code_module,
                'nom_module' => $nom_module,
                'description' => $description
            ]);
            $success = "Module ajouté avec succès !";
            header("Location: home.php");
            exit;
        } catch (PDOException $e) {
            $erreur = "Erreur lors de l'ajout : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un module</title>
    <?php include("link.php"); ?>
</head>
<body class="bg-light">
<?php include("navbar.php"); ?>

<div class="container mt-5">
    <h2 class="mb-4">Ajouter un nouveau module</h2>

    <?php if ($erreur): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="post" class="p-4 bg-white rounded shadow-sm">
        <div class="mb-3">
            <label for="code_module" class="form-label">Code du module *</label>
            <input type="text" id="code_module" name="code_module" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="nom_module" class="form-label">Nom du module *</label>
            <input type="text" id="nom_module" name="nom_module" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="4"></textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="home.php" class="btn btn-outline-secondary">⬅ Retour</a>
            <button type="submit" class="btn btn-primary">Ajouter le module</button>
        </div>
    </form>
</div>
</body>
</html>
