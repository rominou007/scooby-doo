<?php
session_start();
require('db.php');

// Récupérer les modules pour la liste déroulante
$modules = $pdo->query("SELECT id_module, nom_module FROM modules ORDER BY nom_module")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un quiz manuellement</title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5 text-white">
    <h1>Créer un quiz manuellement</h1>
    <form action="creer_quiz_manuel.php" method="post">
        <div class="mb-3">
            <label for="titre" class="form-label">Titre du quiz</label>
            <input type="text" class="form-control" name="titre" id="titre" required>
        </div>
        <div class="mb-3">
            <label for="module_id" class="form-label">Module</label>
            <select class="form-control" name="module_id" id="module_id" required>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= $module['id_module'] ?>"><?= htmlspecialchars($module['nom_module']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <hr>
        <h4>Première question</h4>
        <div class="mb-3">
            <label for="question1" class="form-label">Question</label>
            <input type="text" class="form-control" name="questions[0][text]" id="question1" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Réponses</label>
            <?php for ($i = 0; $i < 3; $i++): ?>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="questions[0][answers][<?= $i ?>][text]" placeholder="Réponse <?= $i+1 ?>" required>
                    <span class="input-group-text">
                        <input type="checkbox" name="questions[0][answers][<?= $i ?>][correct]" value="1">
                        Bonne réponse
                    </span>
                </div>
            <?php endfor; ?>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer le quiz</button>
    </form>
</div>
</body>
</html>