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
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="uploads/logo_nwar.jpg" alt="Logo" width="40" height="40" class="d-inline-block align-text-top">
            Scooby-Doo
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="home.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="modules.php">Modules</a></li>
                    <!-- Ajoute ici d'autres liens selon le rôle -->
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</nav>