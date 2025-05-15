<?php
    require('db.php');

    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $erreur = '';
    $succes = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $titre = trim($_POST['titre'] ?? '');
        $contenu = trim($_POST['contenu'] ?? '');
        
        if (empty($titre) || empty($contenu)) {
            $erreur = "Tous les champs sont obligatoires";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO forum_articles (user_id, titre, contenu) VALUES (:user_id, :titre, :contenu)");
                $stmt->execute([
                    'user_id' => $_SESSION['user_id'],
                    'titre' => $titre,
                    'contenu' => $contenu
                ]);
                
                $article_id = $pdo->lastInsertId();
                header("Location: voir_article.php?id=" . $article_id);
                exit();
            } catch (PDOException $e) {
                $erreur = "Erreur lors de la création de la discussion: " . $e->getMessage();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une discussion</title>
    <?php include("link.php"); ?>
</head>
<?php include("navbar.php"); ?>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h1>Créer une nouvelle discussion</h1>
                <a href="forum.php" class="btn btn-sm btn-outline-secondary">&larr; Retour au forum</a>
            </div>
        </div>
        
        <?php if ($erreur): ?>
            <div class="alert alert-danger"><?= $erreur ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="titre" class="form-label">Titre de la discussion</label>
                                <input type="text" class="form-control" id="titre" name="titre" required>
                            </div>
                            <div class="mb-3">
                                <label for="contenu" class="form-label">Contenu</label>
                                <textarea class="form-control" id="contenu" name="contenu" rows="10" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Publier la discussion</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Conseils pour la rédaction</h5>
                        <ul>
                            <li>Utilisez un titre clair et descriptif</li>
                            <li>Soyez précis dans votre demande ou information</li>
                            <li>Vérifiez si un sujet similaire n'existe pas déjà</li>
                            <li>Respectez les autres membres dans vos échanges</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>