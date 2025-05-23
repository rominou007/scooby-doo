<?php
session_start();
require('db.php');

// Récupérer tous les modules
$stmt = $pdo->query("SELECT * FROM modules ORDER BY date_creation DESC");
$modules = $stmt->fetchAll();

// Récupérer les quiz existants
$quizs = $pdo->query("
    SELECT q.*, m.nom_module, u.nom AS prof_nom 
    FROM quiz q
    JOIN modules m ON q.id_module = m.id_module
    LEFT JOIN user u ON q.id_prof = u.id_user
    ORDER BY q.date_creation DESC
")->fetchAll();

// Récupère toutes les règles de visibilité
$visibilites = [];
$stmt = $pdo->query("SELECT * FROM quiz_visibilite");
while ($row = $stmt->fetch()) {
    $visibilites[$row['id_quiz']] = $row;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Modules</title>
    <?php include("link.php"); ?>
    <!-- Ajout de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5">
    <h1 class="mb-4 text-white">Liste des modules</h1>

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
                  <!-- Remplis dynamiquement avec tes classes -->
                  <option value="">Sélectionner une classe</option>
                </select>
              </div>
              <div class="mb-3 d-none" id="eleve-select-div">
                <label class="form-label">Élève</label>
                <select class="form-control" name="id_eleve" id="eleve-select">
                  <!-- Remplis dynamiquement avec tes élèves -->
                  <option value="">Sélectionner un élève</option>
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
        <div class="alert alert-info">Aucun module n’a encore été ajouté.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($modules as $module): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($module['nom_module']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($module['description']) ?></p>
                            <a href="cours.php?module_id=<?= $module['id_module'] ?>" class="btn btn-primary">Voir le module</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Section dédiée aux quiz -->
    <div class="mt-5">
        <h2 class="text-white mb-4">
            <i class="fas fa-question"></i> Quiz disponibles
        </h2>
        
        <?php if (empty($quizs)): ?>
            <div class="alert alert-info">Aucun quiz disponible.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($quizs as $quiz): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 bg-dark text-white">
                            <div class="card-body">
                                <h5><?= htmlspecialchars($quiz['titre']) ?></h5>
                                <p class="text-muted small">
                                    <i class="fas fa-book"></i> <?= htmlspecialchars($quiz['nom_module']) ?><br>
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($quiz['prof_nom'] ?? 'Système') ?><br>
                                    <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($quiz['date_creation'])) ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary">
                                        <?= count(json_decode($quiz['questions'], true)) ?> questions
                                    </span>
                                    <div>
                                        <a href="faire_quiz.php?id=<?= $quiz['id_quiz'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-play"></i> Commencer
                                        </a>
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
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Affichage de la visibilité -->
                                <?php
                                $vis = $visibilites[$quiz['id_quiz']] ?? null;
                                if ($vis):
                                ?>
                                    <div class="mb-2">
                                        <span class="badge bg-info text-dark">
                                            Visible à :
                                            <?php
                                                if ($vis['cible'] === 'tous') echo 'Tous les étudiants';
                                                elseif ($vis['cible'] === 'classe') echo 'Classe ID ' . $vis['id_cible'];
                                                elseif ($vis['cible'] === 'eleve') echo 'Élève ID ' . $vis['id_cible'];
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
</div>

<!-- Scripts JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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