<?php
session_start();
require('db.php');

// Vérifie que l'utilisateur est prof ou admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], [1, 2])) {
    header("Location: login.php");
    exit();
}

$id_module = $_GET['module_id'] ?? null;
$id_prof = $_SESSION['user_id'];
$success = $error = "";

// Traitement du formulaire d'ajout de cours + document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_cours'])) {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');

    if ($titre && $contenu && $id_module && $id_prof) {
        // 1. Ajout du cours
        $stmt = $pdo->prepare("INSERT INTO cours (id_module, id_prof, titre, contenu, date_creation) VALUES (:id_module, :id_prof, :titre, :contenu, NOW())");
        $stmt->execute([
            'id_module' => $id_module,
            'id_prof' => $id_prof,
            'titre' => $titre,
            'contenu' => $contenu
        ]);
        $success = "Cours ajouté avec succès.";

        // 2. Ajout du document si un fichier est envoyé
        if (!empty($_FILES['document']['name'])) {
            $fileName = basename($_FILES['document']['name']);
            $targetPath = "uploads/" . uniqid() . "_" . $fileName;
            if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
                // Ajout dans la table documents
                $stmt = $pdo->prepare("INSERT INTO documents (id_etudiant, type_document, chemin_fichier, date_televersement) VALUES (:id_etudiant, :type_document, :chemin_fichier, NOW())");
                $stmt->execute([
                    'id_etudiant' => $id_prof, // ou $_SESSION['user_id']
                    'type_document' => 'cours_module_' . $id_module,
                    'chemin_fichier' => $targetPath
                ]);
                $success .= " Document joint ajouté.";
            } else {
                $error = "Erreur lors du téléversement du document.";
            }
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}

// Récupération des cours du module
$cours_list = [];
if ($id_module) {
    $stmt = $pdo->prepare("SELECT * FROM cours WHERE id_module = :id_module ORDER BY date_creation DESC");
    $stmt->execute(['id_module' => $id_module]);
    $cours_list = $stmt->fetchAll();
}

// Récupération des documents liés au module
$documents = [];
if ($id_module) {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE type_document = :type_document");
    $stmt->execute(['type_document' => 'cours_module_' . $id_module]);
    $documents = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($module['nom_module']) ?></title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5 text-white">
    <h1><?= htmlspecialchars($module['nom_module']) ?></h1>
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
            <li>
                <a href="<?= htmlspecialchars($doc['chemin_fichier']) ?>" target="_blank">Voir</a>
                (<?= $doc['date_televersement'] ?>)
            </li>
        <?php endforeach; ?>
        <?php if (empty($documents)): ?>
            <li>Aucun document pour ce module.</li>
        <?php endif; ?>
    </ul>

    <h2>Ajouter un cours</h2>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="mb-4 p-4 bg-dark rounded shadow">
        <div class="mb-3">
            <label for="titre" class="form-label">Titre du cours *</label>
            <input type="text" name="titre" id="titre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="contenu" class="form-label">Contenu *</label>
            <textarea name="contenu" id="contenu" class="form-control" rows="6" required></textarea>
        </div>
        <div class="mb-3">
            <label for="document" class="form-label">Joindre un document (optionnel)</label>
            <input type="file" name="document" id="document" class="form-control">
        </div>
        <button type="submit" name="ajouter_cours" class="btn btn-primary">Ajouter le cours</button>
    </form>

    <h3 class="mt-5">Cours du module :</h3>
    <ul>
        <?php foreach ($cours_list as $cours): ?>
            <li>
                <strong><?= htmlspecialchars($cours['titre']) ?></strong><br>
                <?= nl2br(htmlspecialchars($cours['contenu'])) ?><br>
                <small class="text-muted">Ajouté le <?= $cours['date_creation'] ?></small>
            </li>
        <?php endforeach; ?>
        <?php if (empty($cours_list)): ?>
            <li>Aucun cours pour ce module.</li>
        <?php endif; ?>
    </ul>

    <h3 class="mt-5">Documents joints au module :</h3>
    <ul>
        <?php foreach ($documents as $doc): ?>
            <li>
                <a href="<?= htmlspecialchars($doc['chemin_fichier']) ?>" target="_blank">Voir le document</a>
                <small class="text-muted">(Ajouté le <?= $doc['date_televersement'] ?>)</small>
            </li>
        <?php endforeach; ?>
        <?php if (empty($documents)): ?>
            <li>Aucun document joint pour ce module.</li>
        <?php endif; ?>
    </ul>
</div>

</body>
</html>
