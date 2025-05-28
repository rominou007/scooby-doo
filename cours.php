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

// Fonction d'autorisation
function isAuthorizedUploader($role) {
    return in_array($role, [1, 2], true); // 1 = prof, 2 = admin
}

$error = "";

// Traitement du formulaire d'ajout de cours + document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_cours']) && isset($_SESSION['user_id']) && isAuthorizedUploader($_SESSION['role'])) {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $id_prof = $_SESSION['user_id'];

    if ($titre && $contenu) {
        // 1. Ajout du cours
        $stmt = $pdo->prepare("INSERT INTO cours (id_module, id_prof, titre, contenu, date_creation) VALUES (:id_module, :id_prof, :titre, :contenu, NOW())");
        $stmt->execute([
            'id_module' => $id_module,
            'id_prof' => $id_prof,
            'titre' => $titre,
            'contenu' => $contenu
        ]);

        // 2. Ajout du document si un fichier est envoyé
        if (!empty($_FILES['document']['name'])) {
            $fileName = basename($_FILES['document']['name']);
            $targetPath = "uploads/" . uniqid() . "_" . $fileName;
            if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
                // Ajout dans la table documents AVEC le titre du cours
                $stmt = $pdo->prepare("INSERT INTO documents (id_etudiant, type_document, chemin_fichier, titre, date_televersement) VALUES (:id_etudiant, :type_document, :chemin_fichier, :titre, NOW())");
                $stmt->execute([
                    'id_etudiant' => $id_prof,
                    'type_document' => 'cours_module_' . $id_module,
                    'chemin_fichier' => $targetPath,
                    'titre' => $titre // même titre que le cours
                ]);
            } else {
                $error = "Erreur lors du téléversement du document.";
            }
        }

        // Redirection PRG pour éviter la duplication
        if (empty($error)) {
            header("Location: cours.php?module_id=" . urlencode($id_module) . "&success=1");
            exit();
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

// Suppression d'un cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_cours_id']) && isAuthorizedUploader($_SESSION['role'])) {
    $id_cours = intval($_POST['supprimer_cours_id']);
    // Supprimer le cours
    $stmt = $pdo->prepare("DELETE FROM cours WHERE id_cours = :id_cours AND id_module = :id_module");
    $stmt->execute(['id_cours' => $id_cours, 'id_module' => $id_module]);
    // Redirection pour éviter la re-soumission
    header("Location: cours.php?module_id=" . urlencode($id_module) . "&success=2");
    exit();
}

// Suppression d'un document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_document_id']) && isAuthorizedUploader($_SESSION['role'])) {
    $id_doc = intval($_POST['supprimer_document_id']);
    // Récupérer le chemin du fichier pour le supprimer physiquement
    $stmt = $pdo->prepare("SELECT chemin_fichier FROM documents WHERE id_document = :id_document");
    $stmt->execute(['id_document' => $id_doc]);
    $doc = $stmt->fetch();
    if ($doc && file_exists($doc['chemin_fichier'])) {
        unlink($doc['chemin_fichier']);
    }
    // Supprimer l'entrée en base
    $stmt = $pdo->prepare("DELETE FROM documents WHERE id_document = :id_document");
    $stmt->execute(['id_document' => $id_doc]);
    header("Location: cours.php?module_id=" . urlencode($id_module) . "&success=3");
    exit();
}

// Récupération des cours du module
$stmt = $pdo->prepare("SELECT * FROM cours WHERE id_module = :id_module ORDER BY date_creation DESC");
$stmt->execute(['id_module' => $id_module]);
$cours_list = $stmt->fetchAll();

// Récupération des documents liés au module
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .doc-blanc { color: #fff !important; }
    </style>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5 text-white">
    <h1><?= htmlspecialchars($module['nom_module']) ?></h1>
    <p><?= htmlspecialchars($module['description']) ?></p>

    <!-- Après la description du module, ajoutez ce code -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Code module: <?= htmlspecialchars($module['code_module']) ?></h5>
        
        <?php
        // Récupérer les professeurs associés à ce module
        $stmt = $pdo->prepare("
            SELECT u.id_user, u.prenom, u.nom
            FROM profs_modules pm
            JOIN user u ON pm.id_prof = u.id_user
            WHERE pm.id_module = :id_module
            ORDER BY u.nom, u.prenom
        ");
        $stmt->execute(['id_module' => $id_module]);
        $professors = $stmt->fetchAll();
        
        // Si c'est un étudiant et qu'il y a au moins un professeur associé
        if (isset($_SESSION['role']) && $_SESSION['role'] == 0 && !empty($professors)):
        ?>
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle" type="button" id="contactProfDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-envelope me-1"></i> Contacter un professeur
                </button>
                <ul class="dropdown-menu" aria-labelledby="contactProfDropdown">
                    <?php foreach ($professors as $prof): ?>
                        <li>
                            <a class="dropdown-item" href="creer_conversation.php?receveur_id=<?= $prof['id_user'] ?>">
                                <?= htmlspecialchars($prof['prenom'] . ' ' . $prof['nom']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success">Cours ajouté avec succès.</div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['success']) && $_GET['success'] == 2): ?>
        <div class="alert alert-success">Cours supprimé avec succès.</div>
    <?php endif; ?>
    <?php if (!empty($_GET['success']) && $_GET['success'] == 3): ?>
        <div class="alert alert-success">Document supprimé avec succès.</div>
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
                <?php if (isset($_SESSION['role']) && isAuthorizedUploader($_SESSION['role'])): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce cours ?');">
                        <input type="hidden" name="supprimer_cours_id" value="<?= $cours['id_cours'] ?>">
                        <button type="submit" class="btn btn-link p-0" title="Supprimer">
                            <i class="fa fa-trash text-danger"></i>
                        </button>
                    </form>
                <?php endif; ?>
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
                <a href="<?= htmlspecialchars($doc['chemin_fichier']) ?>" target="_blank" class="doc-blanc">
                    <?= htmlspecialchars($doc['titre'] ?: basename($doc['chemin_fichier'])) ?>
                </a>
                <small class="text-muted">(Ajouté le <?= $doc['date_televersement'] ?>)</small>
                <?php if (isset($_SESSION['role']) && isAuthorizedUploader($_SESSION['role'])): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce document ?');">
                        <input type="hidden" name="supprimer_document_id" value="<?= $doc['id_document'] ?>">
                        <button type="submit" class="btn btn-link p-0" title="Supprimer">
                            <i class="fa fa-trash text-danger"></i>
                        </button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        <?php if (empty($documents)): ?>
            <li>Aucun document joint pour ce module.</li>
        <?php endif; ?>
    </ul>
</div>
</body>
</html>
