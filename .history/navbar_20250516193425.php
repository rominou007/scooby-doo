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

<!-- Barre de navigation principale  -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #1a237e;">
    <div class="container-fluid">
        <!-- Logo et nom du LMS -->
        <a class="navbar-brand d-flex align-items-center" href="<?php echo isset($_SESSION['user_id']) ? 'home.php' : '#'; ?>">
            <img src="uploads/student_five_logo.png" alt="Logo LMS" width="40" height="40" class="me-2">
            <span class="fw-bold">STUDENT FIVE</span>
            <span class="ms-3" style="font-size: 1.1rem; font-weight: 400;">/ Plateforme LMS</span>
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Bouton hamburger pour mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Contenu de la navbar qui sera collapsé sur mobile -->
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-0">
                    <?php if ($user_role === 0): ?>
                        <!-- Étudiant -->
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Tableau de bord</a></li>
                        <li class="nav-item"><a class="nav-link" href="modules.php">Mes cours</a></li>
                        <li class="nav-item"><a class="nav-link" href="notes.php">Notes</a></li>
                        <li class="nav-item"><a class="nav-link" href="planning.php">Planning</a></li>
                    <?php elseif ($user_role === 1): ?>
                        <!-- Professeur -->
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Tableau de bord</a></li>
                        <li class="nav-item"><a class="nav-link" href="courses.php">Mes cours</a></li>
                        <li class="nav-item"><a class="nav-link" href="ajoutdevoires.php">Devoirs & Tests</a></li>
                        <li class="nav-item"><a class="nav-link" href="notes.php">Notes</a></li>
                        <li class="nav-item"><a class="nav-link" href="classes.php">Classes</a></li>
                    <?php elseif ($user_role === 2): ?>
                        <!-- Admin -->
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Tableau de bord</a></li>
                        <li class="nav-item"><a class="nav-link" href="users.php">Utilisateurs</a></li>
                        <li class="nav-item"><a class="nav-link" href="classes.php">Classes</a></li>
                        <li class="nav-item"><a class="nav-link" href="modules.php">Modules</a></li>
                        <li class="nav-item"><a class="nav-link" href="settings.php">Paramètres</a></li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle py-1" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
            </div>
        <?php endif; ?>
    </div>
</nav>