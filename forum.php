<?php
    require('db.php');
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    // Récupérer tous les articles
$stmt = $pdo->query("SELECT a.*, a.user_id as id_user, u.nom, u.prenom, 
                        (SELECT COUNT(*) FROM forum_commentaires WHERE article_id = a.article_id) AS nb_commentaires 
                         FROM forum_articles a 
                         JOIN user u ON a.user_id = u.id_user 
                         ORDER BY a.date_creation DESC");
    $articles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - Discussions</title>
    <?php include("link.php"); ?>
</head>
<?php include("navbar.php"); ?>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col d-flex justify-content-between align-items-center">
                <h1>Forum de discussion</h1>
                <a href="creer_article.php" class="btn btn-primary">Nouvelle discussion</a>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Discussions récentes</h5>
                        
                        <?php if (count($articles) > 0): ?>
                            <div class="list-group">
                                <?php foreach ($articles as $article): ?>
                                    <div class="list-group-item">
                                        <a href="voir_article.php?id=<?= $article['article_id'] ?>" class="text-decoration-none">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1"><?= htmlspecialchars($article['titre']) ?></h5>
                                                <small><?= $article['nb_commentaires'] ?> réponses</small>
                                            </div>
                                            <p class="mb-1"><?= substr(htmlspecialchars($article['contenu']), 0, 150) ?>...</p>
                                            <small>Par <?= htmlspecialchars($article['prenom'] . ' ' . $article['nom']) ?> - <?= date('d/m/Y H:i', strtotime($article['date_creation'])) ?></small>
                                        </a>
                                        
                                        <?php if ($article['id_user'] != $_SESSION['user_id']): ?>
                                            <div class="mt-2">
                                                <a href="creer_conversation.php?receveur_id=<?= $article['id_user'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-envelope me-1"></i> Contacter l'auteur
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted mt-4">Aucune discussion n'a encore été créée</p>
                            <div class="text-center">
                                <a href="creer_article.php" class="btn btn-outline-primary">Créer la première discussion</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>