<?php
session_start();
require('db.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Récupération des modules selon le rôle de l'utilisateur
if ($user_role == 2) { // Admin
    // L'admin voit tous les modules
    $stmt = $pdo->query("SELECT m.*, c.class_name 
                         FROM modules m 
                         LEFT JOIN classes c ON m.class_id = c.class_id 
                         ORDER BY m.date_creation DESC");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($user_role == 1) { // Professeur
    // Le professeur voit les modules auxquels il est associé via profs_modules
    $stmt = $pdo->prepare("SELECT m.*, c.class_name 
                          FROM modules m 
                          LEFT JOIN classes c ON m.class_id = c.class_id 
                          JOIN profs_modules pm ON m.id_module = pm.id_module 
                          WHERE pm.id_prof = ? 
                          ORDER BY m.date_creation DESC");
    $stmt->execute([$user_id]);
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else { // Étudiant
    // L'étudiant voit les modules liés à sa classe
    $stmt = $pdo->prepare("SELECT m.*, c.class_name 
                          FROM modules m 
                          JOIN classes c ON m.class_id = c.class_id 
                          JOIN student_classes sc ON c.class_id = sc.class_id 
                          WHERE sc.student_id = ? 
                          ORDER BY m.date_creation DESC");
    $stmt->execute([$user_id]);
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer les quiz selon le rôle de l'utilisateur
if ($user_role == 2) { // Admin
    // L'admin voit tous les quiz
    $quizs = $pdo->query("
        SELECT q.*, m.nom_module, u.nom AS prof_nom, u.prenom AS prof_prenom
        FROM quiz q
        JOIN modules m ON q.id_module = m.id_module
        LEFT JOIN user u ON q.id_prof = u.id_user
        ORDER BY q.date_creation DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} elseif ($user_role == 1) { // Professeur
    // Le professeur voit les quiz de ses modules ou qu'il a créés
    $stmt = $pdo->prepare("
        SELECT DISTINCT q.*, m.nom_module, u.nom AS prof_nom, u.prenom AS prof_prenom
        FROM quiz q
        JOIN modules m ON q.id_module = m.id_module
        LEFT JOIN user u ON q.id_prof = u.id_user
        LEFT JOIN profs_modules pm ON m.id_module = pm.id_module
        WHERE q.id_prof = ? OR pm.id_prof = ?
        ORDER BY q.date_creation DESC
    ");
    $stmt->execute([$user_id, $user_id]);
    $quizs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else { // Étudiant
    // L'étudiant voit les quiz des modules de sa classe qui sont visibles pour lui
    $stmt = $pdo->prepare("
        SELECT q.*, m.nom_module, u.nom AS prof_nom, u.prenom AS prof_prenom
        FROM quiz q
        JOIN modules m ON q.id_module = m.id_module
        LEFT JOIN user u ON q.id_prof = u.id_user
        JOIN student_classes sc ON sc.student_id = ?
        LEFT JOIN quiz_visibilite qv ON q.id_quiz = qv.id_quiz
        WHERE 
            (m.class_id = sc.class_id)
            AND (
                (qv.cible = 'tous')
                OR (qv.cible = 'classe' AND qv.id_cible = sc.class_id)
                OR (qv.cible = 'eleve' AND qv.id_cible = ?)
            )
        ORDER BY q.date_creation DESC
    ");
    $stmt->execute([$user_id, $user_id]);
    $quizs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupère toutes les règles de visibilité
$visibilites = [];
$stmt = $pdo->query("SELECT * FROM quiz_visibilite");
while ($row = $stmt->fetch()) {
    $visibilites[$row['id_quiz']] = $row;
}

// Récupère toutes les classes
$classes = [];
foreach ($pdo->query("SELECT class_id, class_name FROM classes") as $row) {
    $classes[$row['class_id']] = $row['class_name'];
}

// Récupère tous les élèves
$eleves = [];
foreach ($pdo->query("SELECT id_user, prenom, nom FROM user WHERE role = 0") as $row) {
    $eleves[$row['id_user']] = $row['prenom'] . ' ' . $row['nom'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Modules</title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5">
    <h1 class="mb-4 text-white">
        <?php if ($user_role == 0): ?>
            Mes modules
        <?php elseif ($user_role == 1): ?>
            Mes modules d'enseignement
        <?php else: ?>
            Gestion des modules
        <?php endif; ?>
    </h1>

    <!-- Boutons d'action -->
    <div class="d-flex gap-2 mb-4">
        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], [1, 2])): ?>
            <a href="ajouter_module.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Ajouter un module
            </a>
            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#quizModal">
                <i class="fas fa-question-circle"></i> Créer un quiz
            </button>
        <?php endif; ?>
    </div>

    <!-- Modal Quiz -->
    <div class="modal fade" id="quizModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
          <div class="modal-header">
            <h5 class="modal-title">Créer un quiz</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <form action="importer_quiz.php" method="post" enctype="multipart/form-data">
            <div class="modal-body">
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
              <div class="mb-3">
                <label for="quiz_file" class="form-label">Fichier quiz (JSON ou CSV)</label>
                <input type="file" class="form-control" name="quiz_file" id="quiz_file" accept=".json,.csv" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Importer</button>
              <a href="creer_quiz_manuel.php" class="btn btn-warning">Créer manuellement</a>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de visibilité -->
    <div class="modal fade" id="visibilityModal" tabindex="-1" aria-labelledby="visibilityModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="post" action="gerer_visibilite_quiz.php">
          <div class="modal-content bg-dark text-white">
            <div class="modal-header">
              <h5 class="modal-title" id="visibilityModalLabel">Gérer la visibilité du quiz</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="id_quiz" id="modal_quiz_id">
              <div class="mb-3">
                <label class="form-label">Quiz</label>
                <input type="text" class="form-control" id="modal_quiz_title" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Rendre visible à</label>
                <select class="form-control" name="cible" id="cible-select" required>
                  <option value="tous">Tous les étudiants</option>
                  <option value="classe">Une classe</option>
                  <option value="eleve">Un élève</option>
                </select>
              </div>
              <div class="mb-3 d-none" id="classe-select-div">
                <label class="form-label">Classe</label>
                <select class="form-control" name="id_classe" id="classe-select">
                  <option value="">Sélectionner une classe</option>
                  <?php foreach ($classes as $id_classe => $nom_classe): ?>
                    <option value="<?= $id_classe ?>"><?= htmlspecialchars($nom_classe) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3 d-none" id="eleve-select-div">
                <label class="form-label">Élève</label>
                <select class="form-control" name="id_eleve" id="eleve-select">
                  <option value="">Sélectionner un élève</option>
                  <?php foreach ($eleves as $id_eleve => $nom_eleve): ?>
                    <option value="<?= $id_eleve ?>"><?= htmlspecialchars($nom_eleve) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Date et heure de début</label>
                <input type="datetime-local" class="form-control" name="date_debut" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Date et heure de fin (optionnel)</label>
                <input type="datetime-local" class="form-control" name="date_fin">
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">Enregistrer</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Liste des modules -->
    <?php if (empty($modules)): ?>
        <div class="alert alert-info">
            <?php if ($user_role == 0): ?>
                Vous n'êtes inscrit à aucun module pour le moment.
            <?php elseif ($user_role == 1): ?>
                Vous n'êtes assigné à aucun module. Veuillez contacter l'administration.
            <?php else: ?>
                Aucun module n'a encore été ajouté.
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($modules as $module): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($module['code_module']) ?></span>
                            <?php if ($module['class_name']): ?>
                                <span class="badge bg-info"><?= htmlspecialchars($module['class_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($module['nom_module']) ?></h5>
                            <?php if (!empty($module['description'])): ?>
                                <p class="card-text"><?= nl2br(htmlspecialchars(substr($module['description'], 0, 100))) ?>...</p>
                            <?php else: ?>
                                <p class="card-text text-muted"><i>Aucune description disponible</i></p>
                            <?php endif; ?>
                            
                            <?php
                            // Récupérer les professeurs associés à ce module
                            $prof_stmt = $pdo->prepare("
                                SELECT u.prenom, u.nom 
                                FROM profs_modules pm
                                JOIN user u ON pm.id_prof = u.id_user
                                WHERE pm.id_module = ?
                            ");
                            $prof_stmt->execute([$module['id_module']]);
                            $profs = $prof_stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (!empty($profs)): ?>
                                <div class="text-muted small mb-2">
                                    <i class="fas fa-chalkboard-teacher me-1"></i> Enseignants:
                                    <?php 
                                    $prof_names = array_map(function($p) {
                                        return $p['prenom'] . ' ' . $p['nom'];
                                    }, $profs);
                                    echo htmlspecialchars(implode(', ', $prof_names));
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-light d-flex justify-content-between">
                            <a href="cours.php?module_id=<?= $module['id_module'] ?>" class="btn btn-primary">
                                <i class="fas fa-book-open me-1"></i> Accéder
                            </a>
                            
                            <?php if ($user_role == 1 || $user_role == 2): ?>
                                <div>
                                    <a href="edit_module.php?id=<?= $module['id_module'] ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user_role == 2): // Seul l'admin peut supprimer ?>
                                        <button type="button" class="btn btn-outline-danger ms-1" 
                                                onclick="if(confirm('Êtes-vous sûr de vouloir supprimer ce module?')) 
                                                window.location.href='supprimer_module.php?id=<?= $module['id_module'] ?>'">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Section dédiée aux quiz -->
    <div class="mt-5">
        <h2 class="text-white mb-4">
            <i class="fas fa-question"></i> 
            <?php if ($user_role == 0): ?>
                Quiz disponibles
            <?php else: ?>
                Gestion des quiz
            <?php endif; ?>
        </h2>
        
        <?php if (empty($quizs)): ?>
            <div class="alert alert-info">Aucun quiz disponible.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($quizs as $quiz): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 bg-dark text-white">
                            <div class="card-body d-flex flex-column">
                                <h5><?= htmlspecialchars($quiz['titre']) ?></h5>
                                <p class="text-muted small">
                                    <i class="fas fa-book"></i> <?= htmlspecialchars($quiz['nom_module']) ?><br>
                                    <i class="fas fa-user"></i> <?= htmlspecialchars(($quiz['prof_prenom'] ?? '') . ' ' . ($quiz['prof_nom'] ?? 'Système')) ?><br>
                                    <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($quiz['date_creation'])) ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                                    <span class="badge bg-primary">
                                        <?= count(json_decode($quiz['questions'], true)) ?> questions
                                    </span>
                                    <div>
                                        <?php
                                        $vis = $visibilites[$quiz['id_quiz']] ?? null;
                                        $role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
                                        $canStart = true;
                                        if ($vis && strtotime($vis['date_debut']) > time() && in_array($role, [0, 1])) {
                                            $canStart = false;
                                        }
                                        ?>
                                        <?php if ($canStart): ?>
                                            <a href="faire_quiz.php?id=<?= $quiz['id_quiz'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-play"></i> Commencer
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="fas fa-lock"></i> Pas encore disponible
                                            </button>
                                        <?php endif; ?>
                                        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], [1, 2])): ?>
                                            <button 
                                                class="btn btn-sm btn-outline-light ms-2" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#visibilityModal" 
                                                data-quiz-id="<?= $quiz['id_quiz'] ?>"
                                                data-quiz-title="<?= htmlspecialchars($quiz['titre']) ?>"
                                                title="Gérer la visibilité"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form method="post" action="supprimer_quiz.php" style="display:inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer ce quiz ? Cette action est irréversible.');">
                                                <input type="hidden" name="id_quiz" value="<?= $quiz['id_quiz'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger ms-2" title="Supprimer le quiz">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php
                                            $vis = $visibilites[$quiz['id_quiz']] ?? null;
                                            $canEdit = true;
                                            if ($vis && strtotime($vis['date_debut']) <= time()) {
                                                $canEdit = false;
                                            }
                                            ?>
                                            <?php if ($canEdit): ?>
                                                <a href="modifier_quiz.php?id=<?= $quiz['id_quiz'] ?>" class="btn btn-sm btn-warning ms-2" title="Modifier le quiz">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php
                                $vis = $visibilites[$quiz['id_quiz']] ?? null;
                                if ($vis):
                                ?>
                                    <div class="mt-4 pt-2 border-top border-info">
                                        <span class="badge bg-info text-dark p-2" style="font-size:1em;">
                                            Visible à :
                                            <?php
                                            if ($vis['cible'] === 'tous') {
                                                echo 'Tous les étudiants';
                                            } elseif ($vis['cible'] === 'classe') {
                                                echo 'Classe : ' . ($classes[$vis['id_cible']] ?? 'Inconnue');
                                            } elseif ($vis['cible'] === 'eleve') {
                                                echo 'Élève : ' . ($eleves[$vis['id_cible']] ?? 'Inconnu');
                                            }
                                            ?>
                                            <br>
                                            Du <?= date('d/m/Y H:i', strtotime($vis['date_debut'])) ?>
                                            <?php if ($vis['date_fin']): ?>
                                                au <?= date('d/m/Y H:i', strtotime($vis['date_fin'])) ?>
                                                <?php
                                                    $duree = strtotime($vis['date_fin']) - strtotime($vis['date_debut']);
                                                    $heures = floor($duree / 3600);
                                                    $minutes = floor(($duree % 3600) / 60);
                                                    echo '<br>Durée : ' . ($heures ? $heures . 'h ' : '') . $minutes . 'min';
                                                ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Message de succès pour la suppression d'un quiz -->
    <?php if (isset($_GET['success']) && $_GET['success'] === 'quiz_deleted'): ?>
        <div class="alert alert-success">Quiz supprimé avec succès.</div>
    <?php endif; ?>
    <?php if (isset($_GET['success']) && $_GET['success'] === 'quiz_modified'): ?>
        <div class="alert alert-success">Quiz modifié avec succès.</div>
    <?php endif; ?>
</div>

<!-- Scripts JS -->
<script>
var visibilityModal = document.getElementById('visibilityModal');
if (visibilityModal) {
    visibilityModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var quizId = button.getAttribute('data-quiz-id');
        var quizTitle = button.getAttribute('data-quiz-title');
        document.getElementById('modal_quiz_id').value = quizId;
        document.getElementById('modal_quiz_title').value = quizTitle;
    });

    // Affiche ou masque les sélecteurs classe/élève selon le choix
    document.getElementById('cible-select').addEventListener('change', function() {
        document.getElementById('classe-select-div').classList.toggle('d-none', this.value !== 'classe');
        document.getElementById('eleve-select-div').classList.toggle('d-none', this.value !== 'eleve');
    });
}
</script>
</body>
</html>