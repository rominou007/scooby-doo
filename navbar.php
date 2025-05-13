<?php
// navbar.php - Fichier d'inclusion pour la barre de navigation
// Démarrage de la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification si l'utilisateur est connecté
// if (!isset($_SESSION['user_id'])) {
//     // Rediriger vers la page de connexion si non connecté
//     header('Location: '); // le registzre de la page de connexion (romain avec le fichier csv)
//     exit;
// }

// Récupération des informations utilisateur depuis la session
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null; // Maintenant c'est un INT: 0=étudiant, 1=professeur, 2=admin
$user_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] : 'Invité';

// Définir le nombre de messages non lus (à implémenter avec une requête réelle)
$unread_messages = 0; // Exemple: comptez les messages non lus
?>

<!-- Barre de navigation principale -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <!-- Logo et nom du LMS -->
        <a class="navbar-brand" href="index.php">
            <img src="assets/logo.png" alt="Logo LMS" class="navbar-logo">  Plateforme LMS
        </a>
        
        <!-- Bouton hamburger pour mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Contenu de la navbar qui sera collapsé sur mobile -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                
                <?php if (isset($user_role) && $user_role === 0): ?>
                <!-- Menu Étudiant (role = 0) -->
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="courses.php">
                        <i class="fas fa-book"></i> Mes cours
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="QUIZZ.php">
                        <i class="fas fa-tasks"></i> QUIZZES
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="grades.php">
                        <i class="fas fa-graduation-cap"></i> Notes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="planing.php">
                        <i class="fas fa-calendar-alt"></i> planing
                    </a>
                </li>
                
                <?php elseif (isset($user_role) && $user_role === 1): ?>
                <!-- Menu Professeur (role = 1) -->
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="courses.php">
                        <i class="fas fa-chalkboard-teacher"></i> Mes cours
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ajoutdevoires.php">
                        <i class="fas fa-clipboard-list"></i> Devoirs & Tests
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="grades.php">
                        <i class="fas fa-star"></i> ajouter && voir les notes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="classes.php">
                        <i class="fas fa-users"></i> Classes
                    </a>
                </li>
                
                <?php elseif (isset($user_role) && $user_role === 2): ?>
                <!-- Menu Admin (role = 2) -->
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users-cog"></i> Utilisateurs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="classes.php">
                        <i class="fas fa-user-graduate"></i> Classes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="modules.php">
                        <i class="fas fa-book"></i> Modules
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">
                        <i class="fas fa-cogs"></i> Paramètres
                    </a>
                </li>
                
                <?php elseif (isset($user_role) && $user_role === 3): ?>
                <!-- Menu Personnel (si vous avez un rôle staff = 3) -->
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="students.php">
                        <i class="fas fa-user-graduate"></i> Étudiants
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="documents.php">
                        <i class="fas fa-file-alt"></i> Documents
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <!-- Côté droit de la navbar - Pour tous les rôles -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <ul class="navbar-nav ms-auto">
                <!-- Messagerie -->
                <li class="nav-item dropdown position-relative">
                    <a class="nav-link" href="messages.php" id="messagesDropdown">
                        <i class="fas fa-envelope"></i>
                        <?php if ($unread_messages > 0): ?>
                            <span class="badge rounded-pill bg-danger badge-notification"><?php echo $unread_messages; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            
                <!-- Menu utilisateur -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user_name); ?>
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
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </a>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>