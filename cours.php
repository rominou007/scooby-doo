<?php
session_start();
require('db.php');

if (isset($_GET['module_id']) && !empty($_GET['module_id'])) {
    $module_id = $_GET['module_id'];

    $stmt = $pdo->prepare("SELECT * FROM modules WHERE id_module = :module_id");
    $stmt->execute(['module_id' => $module_id]);
    $module = $stmt->fetch();

    if (!$module) {
        die("Module introuvable");
    }
} else {
    die("ID du module non spécifié");
}

// Détection du rôle autorisé
function isAuthorizedUploader($role) {
    return in_array($role, [1, 2, 'admin', 'professor'], true);
}

// Upload de document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    if (isset($_SESSION['role']) && isAuthorizedUploader($_SESSION['role'])) {
        $fileName = basename($_FILES['document']['name']);
        $targetPath = "uploads/" . $fileName;

        if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
            $stmt = $pdo->prepare("INSERT INTO documents (id_etudiant, type_document, chemin_fichier) VALUES (:user_id, :type, :path)");
            $stmt->execute([
                'user_id' => $_SESSION['user_id'], // ID de l'utilisateur qui téléverse
                'type' => 'module_doc_' . $module_id,
                'path' => $targetPath
            ]);
            $uploadSuccess = "Document téléversé avec succès.";
        } else {
            $uploadError = "Erreur lors du téléversement.";
        }
    }
}

// Documents du module
$stmt = $pdo->prepare("SELECT * FROM documents WHERE document_type = :type");
$stmt->execute(['type' => 'module_doc_' . $module_id]);
$documents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($module['module_name']) ?></title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5">
    <h1><?= htmlspecialchars($module['module_name']) ?></h1>
    <p><?= htmlspecialchars($module['description']) ?></p>

    <?php if (!empty($uploadSuccess)): ?>
        <div class="alert alert-success"><?= $uploadSuccess ?></div>
    <?php elseif (!empty($uploadError)): ?>
        <div class="alert alert-danger"><?= $uploadError ?></div>
    <?php endif; ?>

    <?php 
    // Formulaire visible si utilisateur autorisé
    if (isset($_SESSION['role']) && isAuthorizedUploader($_SESSION['role'])): 
    ?>
        <form method="post" enctype="multipart/form-data" class="mb-4">
            <div class="mb-3">
                <label for="document" class="form-label">Ajouter un document :</label>
                <input type="file" name="document" id="document" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Téléverser</button>
        </form>
    <?php endif; ?>

    <h3>Documents disponibles :</h3>
    <ul>
        <?php foreach ($documents as $doc): ?>
            <li><a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank">Voir</a> (<?= $doc['uploaded_at'] ?>)</li>
        <?php endforeach; ?>
        <?php if (empty($documents)): ?>
            <li>Aucun document pour ce module.</li>
        <?php endif; ?>
    </ul>
</div>

</body>
</html>
