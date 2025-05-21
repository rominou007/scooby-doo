<?php
    session_start();
    require("db.php");
    if(!isset($_SESSION['user_id'])){
        header("location: home2.php");
        exit;
    } elseif($_SESSION['role'] != 2){
        header("location: home.php");
        exit;
    }
    
    // Récupérer le filtre de rôle (si présent)
    $filter_role = isset($_GET['role']) ? (int)$_GET['role'] : null;
    
    // Construire la requête SQL avec ou sans filtre
    if ($filter_role !== null) {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE role = :role ORDER BY nom, prenom");
        $stmt->execute(['role' => $filter_role]);
        $listUsers = $stmt->fetchAll();
    } else {
        $listUsers = $pdo->query("SELECT * FROM user ORDER BY nom, prenom")->fetchAll();
    }
    
    // Récupérer le nombre d'utilisateurs par rôle pour les statistiques
    $stats = [
        'total' => $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn(),
        'admin' => $pdo->query("SELECT COUNT(*) FROM user WHERE role = 2")->fetchColumn(),
        'prof' => $pdo->query("SELECT COUNT(*) FROM user WHERE role = 1")->fetchColumn(),
        'student' => $pdo->query("SELECT COUNT(*) FROM user WHERE role = 0")->fetchColumn()
    ];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <?php include("link.php"); ?>
    <style>
        .role-filter {
            transition: all 0.3s ease;
        }
        
        .role-filter:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .role-filter.active {
            transform: translateY(-2px);
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2);
            font-weight: bold;
        }
        
        .btn-clear-filter {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8rem;
            opacity: 0.7;
        }
        
        .btn-clear-filter:hover {
            opacity: 1;
        }
    </style>
</head>
<?php include("navbar.php"); ?>
<body>
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h1 class="text-center mb-4">Gestion des utilisateurs</h1>
                
                <!-- Compteurs et filtres -->
                <div class="d-flex justify-content-center gap-3 mb-4">
                    <a href="users.php" class="card role-filter text-center p-3 text-decoration-none text-dark <?= $filter_role === null ? 'active border-primary' : '' ?>" style="width: 150px;">
                        <div class="h3"><?= $stats['total'] ?></div>
                        <div>Tous</div>
                    </a>
                    
                    <a href="users.php?role=2" class="card role-filter text-center p-3 text-decoration-none text-dark <?= $filter_role === 2 ? 'active border-danger' : '' ?>" style="width: 150px;">
                        <div class="h3 text-danger"><?= $stats['admin'] ?></div>
                        <div>Administrateurs</div>
                    </a>
                    
                    <a href="users.php?role=1" class="card role-filter text-center p-3 text-decoration-none text-dark <?= $filter_role === 1 ? 'active border-success' : '' ?>" style="width: 150px;">
                        <div class="h3 text-success"><?= $stats['prof'] ?></div>
                        <div>Professeurs</div>
                    </a>
                    
                    <a href="users.php?role=0" class="card role-filter text-center p-3 text-decoration-none text-dark <?= $filter_role === 0 ? 'active border-primary' : '' ?>" style="width: 150px;">
                        <div class="h3 text-primary"><?= $stats['student'] ?></div>
                        <div>Étudiants</div>
                    </a>
                </div>
                
                <?php if ($filter_role !== null): ?>
                    <div class="alert alert-info position-relative mb-4">
                        Filtrage actif : 
                        <?php 
                            switch($filter_role) {
                                case 2: echo "Administrateurs uniquement"; break;
                                case 1: echo "Professeurs uniquement"; break;
                                case 0: echo "Étudiants uniquement"; break;
                            }
                        ?>
                        <a href="users.php" class="btn btn-sm btn-clear-filter">Effacer le filtre × </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="card-title">
                        <?= count($listUsers) ?> utilisateur(s) 
                        <?php if ($filter_role !== null): ?>
                            filtrés
                        <?php endif; ?>
                    </h5>
                    <a href="register.php" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-plus"></i> Ajouter un utilisateur
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Email</th>
                                <th scope="col">Prénom</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Rôle</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($listUsers as $user): ?>
                                <tr>
                                    <th scope="row"><?= $user["id_user"] ?></th>
                                    <td><?= htmlspecialchars($user["email"]) ?></td>
                                    <td><?= htmlspecialchars($user["prenom"]) ?></td>
                                    <td><?= htmlspecialchars($user["nom"]) ?></td>
                                    <td>
                                        <?php if ($user["role"] == 2): ?>
                                            <span class="badge text-bg-danger">Administrateur</span>
                                        <?php elseif ($user["role"] == 1): ?>
                                            <span class="badge text-bg-success">Professeur</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-primary">Étudiant</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- Bouton d'envoi de message -->
                                            <?php if ($user["id_user"] != $_SESSION['user_id']): ?>
                                                <a href="new_conversation.php?receveur_id=<?= $user["id_user"] ?>" class="btn btn-sm btn-info" title="Envoyer un message">
                                                    <i class="fa-solid fa-envelope"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <!-- Bouton d'édition -->
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop<?= $user["id_user"] ?>" title="Modifier">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            
                                            <!-- Bouton de suppression -->
                                            <a href="delete_user.php?id=<?= $user["id_user"] ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal d'édition (inchangé) -->
                                <div class="modal fade" id="staticBackdrop<?= $user["id_user"] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <!-- Contenu modal existant -->
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">Modifier l'utilisateur</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="edit_user.php" method="post">
                                                    <input type="hidden" name="id_user" value="<?= $user["id_user"] ?>">
                                                    <label for="prenom" class="form-label">Prénom</label>
                                                    <input required type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($user["prenom"]) ?>">
                                                    <label for="nom" class="form-label">Nom</label>
                                                    <input required type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($user["nom"]) ?>">
                                                    <label for="email" class="form-label">Email</label>
                                                    <input required type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user["email"]) ?>">

                                                    <label for="acces" class="form-label">Rôle</label>
                                                    <select name="acces" id="acces" class="form-control">
                                                        <option value="0" <?= $user["role"] == 0 ? "selected" : "" ?>>Étudiant</option>
                                                        <option value="1" <?= $user["role"] == 1 ? "selected" : "" ?>>Professeur</option>
                                                        <option value="2" <?= $user["role"] == 2 ? "selected" : "" ?>>Administrateur</option>
                                                    </select>
                                                
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                    <input type="submit" value="Modifier" class="btn btn-primary">
                                                </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (empty($listUsers)): ?>
                    <div class="alert alert-warning">Aucun utilisateur trouvé avec ces critères.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Confirmation avant suppression
        document.addEventListener('DOMContentLoaded', function() {
            const deleteLinks = document.querySelectorAll('a[href^="delete_user.php"]');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>