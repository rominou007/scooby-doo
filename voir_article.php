<?php
    require('db.php');
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
    
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: forum.php");
        exit();
    }
    
    $article_id = (int)$_GET['id'];
    
    // Récupérer les informations de l'article
    $stmt = $pdo->prepare("SELECT a.*, u.username, u.first_name, u.last_name 
                          FROM forum_articles a 
                          JOIN users u ON a.user_id = u.user_id 
                          WHERE a.article_id = :article_id");
    $stmt->execute(['article_id' => $article_id]);
    $article = $stmt->fetch();
    
    if (!$article) {
        header("Location: forum.php");
        exit();
    }
    
    // Récupérer les commentaires
    $stmt = $pdo->prepare("SELECT c.*, u.username, u.first_name, u.last_name 
                          FROM forum_commentaires c 
                          JOIN users u ON c.user_id = u.user_id 
                          WHERE c.article_id = :article_id 
                          ORDER BY c.date_creation");
    $stmt->execute(['article_id' => $article_id]);
    $commentaires = $stmt->fetchAll();
    
    // Traiter l'ajout d'un commentaire
    $erreur = '';
    $succes = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $contenu = trim($_POST['contenu'] ?? '');
        
        if (empty($contenu)) {
            $erreur = "Le contenu du commentaire ne peut pas être vide";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO forum_commentaires (article_id, user_id, contenu) VALUES (:article_id, :user_id, :contenu)");
                $stmt->execute([
                    'article_id' => $article_id,
                    'user_id' => $_SESSION['user_id'],
                    'contenu' => $contenu
                ]);
                
                // Rafraîchir la page pour voir le nouveau commentaire
                header("Location: voir_article.php?id=" . $article_id . "&succes=1");
                exit();
            } catch (PDOException $e) {
                $erreur = "Erreur lors de l'ajout du commentaire: " . $e->getMessage();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['titre']) ?> - Forum</title>
    <?php include("link.php"); ?>
</head>
<?php include("navbar.php"); ?>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row mb-3">
            <div class="col">
                <a href="forum.php" class="btn btn-sm btn-outline-secondary">&larr; Retour au forum</a>
            </div>
        </div>
        
        <?php if (isset($_GET['succes'])): ?>
            <div class="alert alert-success">Votre commentaire a été ajouté avec succès.</div>
        <?php endif; ?>
        
        <?php if ($erreur): ?>
            <div class="alert alert-danger"><?= $erreur ?></div>
        <?php endif; ?>
        
        <!-- Article principal -->
        <div class="card mb-4 shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0"><?= htmlspecialchars($article['titre']) ?></h2>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <strong>Par <?= htmlspecialchars($article['first_name'] . ' ' . $article['last_name']) ?></strong>
                    </div>
                    <div>
                        <small class="text-muted">
                            Publié le <?= date('d/m/Y à H:i', strtotime($article['date_creation'])) ?>
                        </small>
                    </div>
                </div>
                <div class="post-content">
                    <?= nl2br(htmlspecialchars($article['contenu'])) ?>
                </div>
            </div>
        </div>
        
        <!-- Commentaires -->
        <h3 class="mb-3"><?= count($commentaires) ?> Réponse(s)</h3>
        
        <?php if (count($commentaires) > 0): ?>
            <?php foreach ($commentaires as $commentaire): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <strong><?= htmlspecialchars($commentaire['first_name'] . ' ' . $commentaire['last_name']) ?></strong>
                            </div>
                            <div>
                                <small class="text-muted">
                                    <?= date('d/m/Y à H:i', strtotime($commentaire['date_creation'])) ?>
                                </small>
                            </div>
                        </div>
                        <?= nl2br(htmlspecialchars($commentaire['contenu'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Aucune réponse pour l'instant. Soyez le premier à répondre!</p>
        <?php endif; ?>
        
        <!-- Formulaire pour ajouter un commentaire -->
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h4 class="mb-0">Répondre à cette discussion</h4>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="contenu" class="form-label">Votre réponse</label>
                        <textarea class="form-control" id="contenu" name="contenu" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Publier</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>