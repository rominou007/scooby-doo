<?php
session_start();
require("db.php");

// Vérifier que l'utilisateur est connecté et autorisé (admin ou prof)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header("Location: home.php");
    exit;
}


$erreur = "";
$success = "";

// Récupérer les classes existantes pour le menu déroulant
try {
    $classes_stmt = $pdo->query("SELECT class_id, class_name FROM classes ORDER BY class_name");
    $classes = $classes_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erreur = "Erreur lors du chargement des classes : " . $e->getMessage();
    $classes = [];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_module = trim($_POST['code_module']);
    $nom_module = trim($_POST['nom_module']);
    $description = trim($_POST['description']);
    $class_id = isset($_POST['class_id']) && $_POST['class_id'] !== '' ? (int)$_POST['class_id'] : null;

    // Validation
    if (empty($code_module) || empty($nom_module)) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            // Vérifier que le code_module est unique
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM modules WHERE code_module = ?");
            $check_stmt->execute([$code_module]);
            if ($check_stmt->fetchColumn() > 0) {
                $erreur = "Ce code de module est déjà utilisé.";
            } else {
                // Insertion du module
                $stmt = $pdo->prepare("INSERT INTO modules (class_id, code_module, nom_module, description, date_creation) VALUES (:class_id, :code_module, :nom_module, :description, NOW())");
                $stmt->execute([
                    'class_id' => $class_id,
                    'code_module' => $code_module,
                    'nom_module' => $nom_module,
                    'description' => $description
                ]);
                $_SESSION['success_message'] = "Module ajouté avec succès !";
                header("Location: home.php");
                exit;
            }
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

        <div class="mb-3">
            <label for="class_id" class="form-label">Classe associée (optionnel)</label>
            <select name="class_id" id="class_id" class="form-select">
                <option value="">-- Aucune classe --</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= $class['class_id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <a href="home.php" class="btn btn-outline-secondary">⬅ Retour</a>
            <button type="submit" class="btn btn-primary">Ajouter le module</button>
        </div>
    </form>
</div>
</body>
</html>
