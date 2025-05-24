<?php
session_start();
require("db.php");

// Vérifier que l'utilisateur est connecté et autorisé (admin ou prof)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 0) {
    header("Location: home.php");
    exit;
}

$erreur = "";
$success = "";

// Récupérer les classes existantes
try {
    $classes_stmt = $pdo->query("SELECT class_id, class_name FROM classes ORDER BY class_name");
    $classes = $classes_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erreur = "Erreur lors du chargement des classes : " . $e->getMessage();
    $classes = [];
}

// Récupérer les professeurs existants
try {
    $profs_stmt = $pdo->prepare("SELECT id_user as user_id, nom, prenom FROM user WHERE role = 1 ORDER BY nom");
    $profs_stmt->execute();
    $profs = $profs_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erreur = "Erreur lors du chargement des professeurs : " . $e->getMessage();
    $profs = [];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_module = trim($_POST['code_module']);
    $nom_module = trim($_POST['nom_module']);
    $description = trim($_POST['description']);
    $class_id = isset($_POST['class_id']) && $_POST['class_id'] !== '' ? (int)$_POST['class_id'] : null;
    $profs_ids = isset($_POST['prof_ids']) ? $_POST['prof_ids'] : [];

    // Validation
    if (empty($code_module) || empty($nom_module)) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            // Commencer une transaction pour assurer la cohérence des données
            $pdo->beginTransaction();
            
            // Vérifier que le code_module est unique
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM modules WHERE code_module = ?");
            $check_stmt->execute([$code_module]);
            if ($check_stmt->fetchColumn() > 0) {
                $erreur = "Ce code de module est déjà utilisé.";
            } else {
                // Insertion du module
                $stmt = $pdo->prepare("INSERT INTO modules (class_id, code_module, nom_module, description, date_creation)
                                     VALUES (:class_id, :code_module, :nom_module, :description, NOW())");
                $stmt->execute([
                    'class_id' => $class_id,
                    'code_module' => $code_module,
                    'nom_module' => $nom_module,
                    'description' => $description
                ]);
                
                $module_id = $pdo->lastInsertId();
                
                // Associer les professeurs sélectionnés au module
                if (!empty($profs_ids)) {
                    $insert_prof_module = $pdo->prepare("INSERT INTO profs_modules (id_prof, id_module) VALUES (:prof_id, :module_id)");
                    
                    foreach ($profs_ids as $prof_id) {
                        $insert_prof_module->execute([
                            'prof_id' => $prof_id,
                            'module_id' => $module_id
                        ]);
                    }
                }
                
                // Valider la transaction
                $pdo->commit();
                
                $_SESSION['success_message'] = "Module ajouté avec succès !";
                header("Location: modules.php");
                exit;
            }
        } catch (PDOException $e) {
            // En cas d'erreur, annuler les modifications
            $pdo->rollBack();
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

        <div class="mb-3">
            <label class="form-label">Professeurs associés (optionnel)</label>
            <div class="card">
                <div class="card-body">
                    <?php if (empty($profs)): ?>
                        <p class="text-muted">Aucun professeur disponible.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($profs as $prof): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="prof_ids[]" 
                                               id="prof_<?= $prof['user_id'] ?>" value="<?= $prof['user_id'] ?>">
                                        <label class="form-check-label" for="prof_<?= $prof['user_id'] ?>">
                                            <?= htmlspecialchars($prof['nom'] . ' ' . $prof['prenom']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="home.php" class="btn btn-outline-secondary">⬅ Retour</a>
            <button type="submit" class="btn btn-primary">Ajouter le module</button>
        </div>
    </form>
</div>

<script>
// Optionnel : ajouter du JavaScript pour améliorer l'UX
document.addEventListener('DOMContentLoaded', function() {
    // Limiter la longueur du code module
    const codeInput = document.getElementById('code_module');
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            if (this.value.length > 20) {
                this.value = this.value.substring(0, 20);
            }
        });
    }
    
    // Limiter la longueur du nom du module
    const nomInput = document.getElementById('nom_module');
    if (nomInput) {
        nomInput.addEventListener('input', function() {
            if (this.value.length > 100) {
                this.value = this.value.substring(0, 100);
            }
        });
    }
});
</script>
</body>
</html>
