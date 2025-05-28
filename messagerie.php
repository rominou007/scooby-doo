<?php
    session_start();
    require('db.php');
    
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Récupérer l'ID de la conversation à partir de l'URL
    $conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;
    
    // Si on est sur la page d'une conversation spécifique
    if ($conversation_id > 0) {
        // Vérifier que l'utilisateur connecté est bien membre de cette conversation
        $stmt = $pdo->prepare("SELECT c.*, 
                               CASE 
                                   WHEN c.user1_id = :user_id THEN c.user2_id
                                   ELSE c.user1_id
                               END AS other_user_id
                              FROM conversations c
                              WHERE c.conversation_id = :conversation_id
                              AND (c.user1_id = :user_id OR c.user2_id = :user_id)");
        $stmt->execute([
            'conversation_id' => $conversation_id,
            'user_id' => $user_id
        ]);
        $conversation = $stmt->fetch();
        
        if (!$conversation) {
            // L'utilisateur n'a pas accès à cette conversation ou elle n'existe pas
            header("Location: messagerie.php");
            exit();
        }
        
        $other_user_id = $conversation['other_user_id'];
        
        // Récupérer les informations de l'autre utilisateur
        $stmt = $pdo->prepare("SELECT prenom, nom FROM user WHERE id_user = :user_id");
        $stmt->execute(['user_id' => $other_user_id]);
        $other_user = $stmt->fetch();
        
        // Marquer tous les messages non lus comme lus
        $stmt = $pdo->prepare("UPDATE messages SET lu = 1 
                              WHERE conversation_id = :conversation_id 
                              AND sender_id = :other_user_id 
                              AND lu = 0");
        $stmt->execute([
            'conversation_id' => $conversation_id,
            'other_user_id' => $other_user_id
        ]);
        
        // Récupérer les messages de la conversation
        $stmt = $pdo->prepare("SELECT m.*, u.prenom, u.nom FROM messages m
                              JOIN user u ON m.sender_id = u.id_user
                              WHERE m.conversation_id = :conversation_id
                              ORDER BY m.date_envoi ASC");
        $stmt->execute(['conversation_id' => $conversation_id]);
        $messages = $stmt->fetchAll();
        
        // Traitement de l'envoi d'un nouveau message
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && !empty($_POST['message'])) {
            $message_content = trim($_POST['message']);
            
            // Insérer le message dans la base de données
            $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, content) 
                                  VALUES (:conversation_id, :sender_id, :content)");
            $stmt->execute([
                'conversation_id' => $conversation_id,
                'sender_id' => $user_id,
                'content' => $message_content
            ]);
            
            // Mettre à jour la date du dernier message
            $stmt = $pdo->prepare("UPDATE conversations SET last_message_date = NOW() 
                                  WHERE conversation_id = :conversation_id");
            $stmt->execute(['conversation_id' => $conversation_id]);
            
            // Redirection pour éviter les envois multiples par rafraîchissement
            header("Location: messagerie.php?conversation_id=" . $conversation_id);
            exit();
        }
    } else {
        // On est sur la page de liste des conversations
        
        // Récupérer les conversations de l'utilisateur connecté
        $stmt = $pdo->prepare("SELECT c.*, 
                                CASE 
                                    WHEN c.user1_id = :user_id THEN c.user2_id
                                    ELSE c.user1_id
                                END AS other_user_id
                              FROM conversations c
                              WHERE c.user1_id = :user_id OR c.user2_id = :user_id
                              ORDER BY c.last_message_date DESC");
        $stmt->execute(['user_id' => $user_id]);
        $conversations = $stmt->fetchAll();
        
        // Récupérer les informations des utilisateurs
        $user_info = [];
        $last_messages = [];
        $unread_counts = [];
        
        foreach ($conversations as $convo) {
            $other_id = $convo['other_user_id'];
            $convo_id = $convo['conversation_id'];
            
            // Info utilisateur
            if (!isset($user_info[$other_id])) {
                $stmt = $pdo->prepare("SELECT u.prenom, u.nom, u.role FROM user u WHERE u.id_user = :user_id");
                $stmt->execute(['user_id' => $other_id]);
                $user_info[$other_id] = $stmt->fetch();
            }
            
            // Dernier message
            $stmt = $pdo->prepare("SELECT m.content, m.date_envoi, m.sender_id 
                                  FROM messages m 
                                  WHERE m.conversation_id = :conversation_id 
                                  ORDER BY m.date_envoi DESC LIMIT 1");
            $stmt->execute(['conversation_id' => $convo_id]);
            $last_messages[$convo_id] = $stmt->fetch();
            
            // Comptage messages non lus
            $stmt = $pdo->prepare("SELECT COUNT(*) AS count 
                                  FROM messages 
                                  WHERE conversation_id = :conversation_id 
                                  AND sender_id = :other_id 
                                  AND lu = 0");
            $stmt->execute([
                'conversation_id' => $convo_id,
                'other_id' => $other_id
            ]);
            $result = $stmt->fetch();
            $unread_counts[$convo_id] = $result['count'];
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($other_user) ? "Conversation avec " . htmlspecialchars($other_user['prenom'] . ' ' . $other_user['nom']) : "Messagerie"; ?></title>
    <?php include("link.php"); ?>
    <style>
        .message-container {
            height: 400px;
            overflow-y: auto;
        }
        .message {
            max-width: 75%;
            padding: 10px 15px;
            border-radius: 15px;
            margin-bottom: 10px;
        }
        .message-sent {
            background-color: #dcf8c6;
            margin-left: auto;
        }
        .message-received {
            background-color: #f1f1f1;
        }
        .message-time {
            font-size: 0.7rem;
            color: #777;
            margin-top: 5px;
            text-align: right;
        }
        .conversation-item:hover {
            background-color: #f8f9fa;
        }
        .unread-indicator {
            width: 10px;
            height: 10px;
            background-color: #007bff;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
    </style>
</head>
<?php include("navbar.php"); ?>
<body class="bg-light">
    <div class="container mt-4">
        <?php if (isset($conversation_id) && $conversation_id > 0): ?>
            <!-- Vue de conversation spécifique -->
            <div class="row mb-3">
                <div class="col">
                    <a href="messagerie.php" class="btn btn-outline-black">&larr; Retour aux conversations</a>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        Conversation avec <?= htmlspecialchars($other_user['prenom'] . ' ' . $other_user['nom']) ?>
                        <?php
                            // Récupérer le rôle de l'autre utilisateur
                            $stmt = $pdo->prepare("SELECT role FROM user WHERE id_user = :user_id");
                            $stmt->execute(['user_id' => $other_user_id]);
                            $other_user_role = $stmt->fetchColumn();
                            
                            // Affichage du badge selon le rôle
                            $role_text = "";
                            $role_class = "";
                            switch($other_user_role) {
                                case 0:
                                    $role_text = "Étudiant";
                                    $role_class = "bg-info";
                                    break;
                                case 1:
                                    $role_text = "Professeur";
                                    $role_class = "bg-success";
                                    break;
                                case 2:
                                    $role_text = "Admin";
                                    $role_class = "bg-danger";
                                    break;
                                default:
                                    $role_text = "Utilisateur";
                                    $role_class = "bg-secondary";
                            }
                        ?>
                        <span class="badge <?= $role_class ?> ms-2"><?= $role_text ?></span>
                    </h5>
                </div>
                
                <div class="card-body">
                    <div class="message-container" id="messageContainer">
                        <?php if (empty($messages)): ?>
                            <div class="text-center text-muted my-5">
                                <p>Aucun message. Commencez la conversation!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?= ($message['sender_id'] == $user_id) ? 'message-sent' : 'message-received' ?>">
                                    <?= nl2br(htmlspecialchars($message['content'])) ?>
                                    <div class="message-time">
                                        <?= date('d/m/Y H:i', strtotime($message['date_envoi'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <form method="post" class="mt-3">
                        <div class="form-group">
                            <textarea class="form-control" name="message" rows="3" placeholder="Saisissez votre message..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Envoyer</button>
                    </form>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Vue de liste des conversations -->
            <div class="row mb-4">
                <div class="col">
                    <h1>Messagerie</h1>
                    <p class="text-muted">Retrouvez ici l'ensemble de vos conversations</p>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (count($conversations) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($conversations as $convo): ?>
                                <?php 
                                    $other_id = $convo['other_user_id'];
                                    $convo_id = $convo['conversation_id'];
                                    if (!isset($user_info[$other_id]) || !$user_info[$other_id]) continue;
                                    
                                    // Vérifier si last_messages[$convo_id] existe et n'est pas false
                                    $has_last_message = isset($last_messages[$convo_id]) && $last_messages[$convo_id] !== false;
                                ?>
                                <a href="messagerie.php?conversation_id=<?= $convo_id ?>" 
                                   class="list-group-item list-group-item-action conversation-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">
                                            <?php if (isset($unread_counts[$convo_id]) && $unread_counts[$convo_id] > 0): ?>
                                                <span class="unread-indicator"></span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($user_info[$other_id]['prenom'] . ' ' . $user_info[$other_id]['nom']) ?>
                                            <?php
                                                // Affichage du badge selon le rôle
                                                $role_text = "";
                                                $role_class = "";
                                                switch($user_info[$other_id]['role']) {
                                                    case 0:
                                                        $role_text = "Étudiant";
                                                        $role_class = "bg-info";
                                                        break;
                                                    case 1:
                                                        $role_text = "Professeur";
                                                        $role_class = "bg-success";
                                                        break;
                                                    case 2:
                                                        $role_text = "Admin";
                                                        $role_class = "bg-danger";
                                                        break;
                                                    default:
                                                        $role_text = "Utilisateur";
                                                        $role_class = "bg-secondary";
                                                }
                                            ?>
                                            <span class="badge <?= $role_class ?> ms-2"><?= $role_text ?></span>
                                        </h5>
                                        <small>
                                            <?php if ($has_last_message): ?>
                                                <?= date('d/m H:i', strtotime($last_messages[$convo_id]['date_envoi'])) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    
                                    <?php if ($has_last_message): ?>
                                        <p class="mb-1 text-truncate">
                                            <?php if ($last_messages[$convo_id]['sender_id'] == $user_id): ?>
                                                <small class="text-muted">Vous: </small>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($last_messages[$convo_id]['content']) ?>
                                        </p>
                                        
                                        <?php if (isset($unread_counts[$convo_id]) && $unread_counts[$convo_id] > 0): ?>
                                            <span class="badge bg-primary rounded-pill float-end">
                                                <?= $unread_counts[$convo_id] ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="mb-1 text-muted"><em>Pas encore de message</em></p>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p class="lead">Vous n'avez pas encore de conversations.</p>
                            <p>Pour démarrer une conversation, contactez un autre utilisateur.</p>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 2): // Admin ?>
                                <a href="users.php" class="btn btn-primary">Voir les utilisateurs</a>
                            <?php endif; ?>                               
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <a href="users.php?action=message" class="btn btn-success">
                        <i class="fas fa-plus"></i> Nouvelle conversation
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Faire défiler automatiquement vers le bas de la conversation
        document.addEventListener('DOMContentLoaded', function() {
            const messageContainer = document.getElementById('messageContainer');
            if (messageContainer) {
                messageContainer.scrollTop = messageContainer.scrollHeight;
            }
        });
    </script>
</body>
</html>