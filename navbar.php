<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;
$user_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] : 'Invité';

// Initialisation du compteur de messages non lus
$unread_messages = 0;

// Si l'utilisateur est connecté, compter les messages non lus
if ($user_id) {
    require_once('db.php');
    
    try {
        // Requête pour compter tous les messages non lus destinés à l'utilisateur connecté
        $stmt = $pdo->prepare("SELECT COUNT(*) as count 
                              FROM messages m 
                              JOIN conversations c ON m.conversation_id = c.conversation_id 
                              WHERE (c.user1_id = :user_id OR c.user2_id = :user_id) 
                              AND m.sender_id != :user_id 
                              AND m.lu = 0");
        $stmt->execute(['user_id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $unread_messages = $result['count'];
    } catch (PDOException $e) {
        // En cas d'erreur avec la base de données, ne pas afficher l'erreur mais simplement ne pas montrer de notification
        $unread_messages = 0;
    }
}
?>

<nav class="navbar navbar-sm navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="home.php">
            <img src="uploads/student_five_logo.png" alt="Logo LMS" class="navbar-logo" width="75" height="75">  Plateforme LMS
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-0">
                
                <?php if (isset($user_role) && $user_role === 0): ?>
                <!-- Étudiant -->
                <li class="nav-item"><a class="nav-link py-1" href="modules.php"><i class="fas fa-book"></i> Mes cours</a></li>
                <li class="nav-item"><a class="nav-link py-1" href="notes.php"><i class="fas fa-graduation-cap"></i> Notes</a></li>
                <li class="nav-item"><a class="nav-link py-1" href="planning.php"><i class="fas fa-calendar-alt"></i> Planning</a></li>

                <?php elseif (isset($user_role) && $user_role === 1): ?>
                <!-- Professeur -->
                <li class="nav-item"><a class="nav-link" href="courses.php"><i class="fas fa-chalkboard-teacher"></i> Mes cours</a></li>
                <li class="nav-item"><a class="nav-link" href="ajoutdevoires.php"><i class="fas fa-clipboard-list"></i> Devoirs & Tests</a></li>
                <li class="nav-item"><a class="nav-link py-1" href="notes.php"><i class="fas fa-star"></i> Notes</a></li>
                <li class="nav-item"><a class="nav-link py-1" href="classes.php"><i class="fas fa-users"></i> Classes</a></li>
                <li class="nav-item"><a class="nav-link py-1" href="planning.php"><i class="fas fa-calendar-alt"></i> Planning</a></li>

                <?php elseif (isset($user_role) && $user_role === 2): ?>
                <!-- Admin -->
                <li class="nav-item"><a class="nav-link py-1" href="users.php"><i class="fas fa-users-cog"></i> Utilisateurs</a></li>
                <li class="nav-item"><a class="nav-link py-1" href="classes.php"><i class="fas fa-user-graduate"></i> Classes</a></li>
                <li class="nav-item"><a class="nav-link py-1" href="modules.php"><i class="fas fa-book"></i> Modules</a></li>
                <li class="nav-item"><a class="nav-link py-1" href="planning.php"><i class="fas fa-calendar-alt"></i> Planning</a></li>
                <li class="nav-item"><a class="nav-link py-1" href="settings.php"><i class="fas fa-cogs"></i> Paramètres</a></li>

            </ul>

            <?php if (isset($_SESSION['user_id'])): ?>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item position-relative">
                    <a class="nav-link py-1" href="messagerie.php" id="messagesDropdown">
                        <i class="fas fa-envelope"></i>
                        <?php if ($unread_messages > 0): ?>
                            <span class="badge rounded-pill bg-danger badge-notification"><?= $unread_messages; ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle py-1" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($user_name); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-id-card"></i> Mon profil</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </li>
            </ul>
            <?php else: ?>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link py-1" href="home.php">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </a>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
