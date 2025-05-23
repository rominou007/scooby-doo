<?php
session_start();
require('db.php');

// Récupération du module
if (isset($_GET['module_id']) && !empty($_GET['module_id'])) {
    $id_module = $_GET['module_id'];
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE id_module = :id_module");
    $stmt->execute(['id_module' => $id_module]);
    $module = $stmt->fetch();
    if (!$module) {
        die("Module introuvable");
    }
} else {
    die("ID du module non spécifié");
}

function isAuthorizedUploader($role) {
    return in_array($role, [1, 2], true); // Prof ou Admin
}

$error = "";

// Traitement du formulaire d'ajout de cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_cours']) && isset($_SESSION['user_id']) && isAuthorizedUploader($_SESSION['role'])) {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $id_prof = $_SESSION['user_id'];

    if ($titre && $contenu) {
        $stmt = $pdo->prepare("INSERT INTO cours (id_module, id_prof, titre, contenu, date_creation) VALUES (:id_module, :id_prof, :titre, :contenu, NOW())");
        $stmt->execute([
            'id_module' => $id_module,
            'id_prof' => $id_prof,
            'titre' => $titre,
            'contenu' => $contenu
        ]);

        if (!empty($_FILES['document']['name'])) {
            $fileName = basename($_FILES['document']['name']);
            $targetPath = "uploads/" . uniqid() . "_" . $fileName;
            if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
                $stmt = $pdo->prepare("INSERT INTO documents (id_etudiant, type_document, chemin_fichier, date_televersement) VALUES (:id_etudiant, :type_document, :chemin_fichier, NOW())");
                $stmt->execute([
                    'id_etudiant' => $id_prof,
                    'type_document' => 'cours_module_' . $id_module,
                    'chemin_fichier' => $targetPath
                ]);
            } else {
                $error = "Erreur lors du téléversement du document.";
            }
        }

        if (empty($error)) {
            header("Location: cours.php?module_id=" . urlencode($id_module) . "&success=1");
            exit();
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM cours WHERE id_module = :id_module ORDER BY date_creation DESC");
$stmt->execute(['id_module' => $id_module]);
$cours_list = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM documents WHERE type_document = :type_document");
$stmt->execute(['type_document' => 'cours_module_' . $id_module]);
$documents = $stmt->fetchAll();
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

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success">Cours ajouté avec succès.</div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && isAuthorizedUploader($_SESSION['role'])): ?>
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
    <?php endif; ?>

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
                <a href="<?= htmlspecialchars($doc['chemin_fichier']) ?>" target="_blank">
                    <?= htmlspecialchars(basename($doc['chemin_fichier'])) ?>
                </a>
                <small class="text-muted">(Ajouté le <?= $doc['date_televersement'] ?>)</small>
            </li>
        <?php endforeach; ?>
        <?php if (empty($documents)): ?>
            <li>Aucun document joint pour ce module.</li>
        <?php endif; ?>
    </ul>

    <h3 class="mt-5">Devoirs à venir pour ce module :</h3>
    <?php
    $stmt = $pdo->prepare("SELECT * FROM devoirs WHERE id_module = :id_module AND date_limite >= NOW() ORDER BY date_limite ASC");
    $stmt->execute(['id_module' => $id_module]);
    $devoirs = $stmt->fetchAll();
    ?>
    <ul>
        <?php foreach ($devoirs as $devoir): ?>
            <li>
                <strong>
                    <a href="devoir_zoom.php?devoir_id=<?= $devoir['id_devoir'] ?>" class="text-white text-decoration-underline">
                        <?= htmlspecialchars($devoir['titre']) ?>
                    </a>
                </strong>
                <br>
                <small class="text-muted">À rendre avant le <?= date('d/m/Y H:i', strtotime($devoir['date_limite'])) ?></small>
            </li>
        <?php endforeach; ?>
        <?php if (empty($devoirs)): ?>
            <li>Aucun devoir à venir pour ce module.</li>
        <?php endif; ?>
    </ul>

    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], [1, 2])): ?>
        <a href="ajouter_devoir.php?module_id=<?= $id_module ?>" class="btn btn-outline-success mt-3">
            <i class="fas fa-plus"></i> Ajouter un devoir pour ce module
        </a>
    <?php endif; ?>
</div>
</body>
</html>
