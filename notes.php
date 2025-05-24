<?php
session_start();
require("db.php");

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 0; // 0=étudiant, 1=prof, 2=admin

// Vérifier si on veut voir les résultats d'un quiz
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : null;

// Vérifier si on veut filtrer par classe
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : null;

// Vérifier si un module spécifique est demandé par GET
$module_id = isset($_GET['module_id']) ? intval($_GET['module_id']) : null;

// Si on veut voir les résultats d'un quiz spécifique (professeur uniquement)
if ($quiz_id && ($user_role == 1 || $user_role == 2)) {
    // Récupérer les informations du quiz
    $stmt = $pdo->prepare("SELECT q.*, m.code_module, m.nom_module 
                         FROM quiz q 
                         JOIN modules m ON q.id_module = m.id_module 
                         WHERE q.id_quiz = ?");
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$quiz) {
        header("Location: notes.php");
        exit();
    }
    
    // Récupérer la liste des classes
    $stmt = $pdo->query("SELECT * FROM classes ORDER BY class_name");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les résultats des étudiants, avec filtre par classe si nécessaire
    $params = [$quiz_id];
    
    $sql = "SELECT u.id_user, u.prenom, u.nom, u.email, 
              c.class_name, c.class_id,
              qr.score, qr.date_passage
       FROM user u
       JOIN student_classes sc ON u.id_user = sc.student_id
       JOIN classes c ON sc.class_id = c.class_id
       LEFT JOIN quiz_resultats qr ON u.id_user = qr.id_etudiant AND qr.id_quiz = ?
       JOIN quiz q ON q.id_quiz = ?";
    
    $params[] = $quiz_id;
    
    if ($class_id) {
        $sql .= " WHERE c.class_id = ?";
        $params[] = $class_id;
    }
    
    $sql .= " ORDER BY c.class_name, u.nom, u.prenom";
    
    // Après avoir récupéré les résultats, calculez le nombre de questions séparément:
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculer le nombre de questions depuis la colonne JSON
    $questions = json_decode($quiz['questions'], true);
    $quiz['nb_questions'] = is_array($questions) ? count($questions) : 0;
    
    // Calculer des statistiques
    $stats = [
        'completion_rate' => 0,
        'average_score' => 0,
        'highest_score' => 0,
        'lowest_score' => null,
        'total_students' => count($resultats),
        'completed_count' => 0
    ];
    
    $total_score = 0;
    foreach ($resultats as $resultat) {
        if (isset($resultat['score'])) {
            $stats['completed_count']++;
            $total_score += $resultat['score'];
            
            if ($resultat['score'] > $stats['highest_score']) {
                $stats['highest_score'] = $resultat['score'];
            }
            
            if ($stats['lowest_score'] === null || $resultat['score'] < $stats['lowest_score']) {
                $stats['lowest_score'] = $resultat['score'];
            }
        }
    }
    
    if ($stats['total_students'] > 0) {
        $stats['completion_rate'] = round(($stats['completed_count'] / $stats['total_students']) * 100);
    }
    
    if ($stats['completed_count'] > 0) {
        $stats['average_score'] = round($total_score / $stats['completed_count'], 1);
    }
    
} elseif ($module_id) {
    // Le reste du code existant pour afficher les quiz d'un module
    $stmt = $pdo->prepare("SELECT id_module, code_module, nom_module FROM modules WHERE id_module = ?");
    $stmt->execute([$module_id]);
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$module) {
        // Si module non trouvé, rediriger vers la page des notes
        header("Location: notes.php");
        exit();
    }
    
    // Récupérer les quiz associés à ce module
    $stmt = $pdo->prepare("SELECT q.id_quiz, q.titre, q.date_creation, q.questions,
                          COALESCE(qr.score, 0) AS score_user,
                          CASE WHEN qr.id_etudiant IS NOT NULL THEN 1 ELSE 0 END AS quiz_complete
                          FROM quiz q
                          LEFT JOIN quiz_resultats qr ON q.id_quiz = qr.id_quiz AND qr.id_etudiant = ?
                          WHERE q.id_module = ?
                          ORDER BY q.date_creation DESC");
    $stmt->execute([$user_id, $module_id]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculer le nombre de questions pour chaque quiz en analysant le JSON
    foreach ($quizzes as &$quiz) {
        $questions = json_decode($quiz['questions'], true);
        $quiz['nb_questions'] = is_array($questions) ? count($questions) : 0;
    }
    unset($quiz); // Détruire la référence
} else {
    // Code existant pour la liste des modules et notes
    $stmt = $pdo->query("SELECT id_module, code_module, nom_module FROM modules ORDER BY nom_module ASC");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les notes de l'utilisateur si étudiant
    $notes = [];
    if ($user_role == 0) {
        // Récupérer les notes et calculer la moyenne pondérée
        $stmt = $pdo->prepare("
            SELECT id_module, 
                   SUM(note * coefficient) / SUM(coefficient) as moyenne,
                   GROUP_CONCAT(nom_devoir SEPARATOR '|') as devoirs,
                   GROUP_CONCAT(note SEPARATOR '|') as notes,
                   GROUP_CONCAT(coefficient SEPARATOR '|') as coefficients
            FROM notes 
            WHERE id_etudiant = :user_id 
            GROUP BY id_module");
        $stmt->execute(['user_id' => $user_id]);
        
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $notes[$row['id_module']] = [
                'moyenne' => round($row['moyenne'], 2),
                'devoirs' => explode('|', $row['devoirs']),
                'notes' => explode('|', $row['notes']),
                'coefficients' => explode('|', $row['coefficients'])
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <?php if (isset($quiz)): ?>
        <title>Résultats du quiz <?= htmlspecialchars($quiz['titre']) ?></title>
    <?php elseif (isset($module)): ?>
        <title>Quiz du module <?= htmlspecialchars($module['code_module']) ?></title>
    <?php else: ?>
        <title>Mes notes</title>
    <?php endif; ?>
    <?php include("link.php"); ?>
    <style>
        .completion-badge {
            font-size: 0.8rem;
            vertical-align: middle;
        }
        .progress {
            height: 8px;
        }
        .filter-bar {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .stats-card {
            transition: transform 0.2s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<?php include("navbar.php"); ?>
<body>
<div class="container mt-5">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</div>
<div class="container mt-5">
    <?php if (isset($quiz) && ($user_role == 1 || $user_role == 2)): ?>
        <!-- Vue des résultats pour un quiz spécifique (pour les professeurs) -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Résultats du quiz</h1>
                <h5 class="text-muted"><?= htmlspecialchars($quiz['titre']) ?></h5>
                <p class="small mb-0">Module: <?= htmlspecialchars($quiz['code_module'] . ' - ' . $quiz['nom_module']) ?></p>
            </div>
            <div class="text-end">
                <a href="notes.php?module_id=<?= $quiz['id_module'] ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour aux quiz
                </a>
                <a href="faire_quiz.php?id=<?= $quiz['id_quiz'] ?>" class="btn btn-primary ms-2">
                    <i class="fas fa-eye me-2"></i>Voir le quiz
                </a>
            </div>
        </div>
        
        <!-- Statistiques générales -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">Taux de complétion</h6>
                        <h2 class="mb-0"><?= $stats['completion_rate'] ?>%</h2>
                        <p class="card-text small"><?= $stats['completed_count'] ?>/<?= $stats['total_students'] ?> étudiants</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">Score moyen</h6>
                        <h2 class="mb-0"><?= $stats['average_score'] ?>/<?= $quiz['nb_questions'] ?></h2>
                        <p class="card-text small"><?= round(($stats['average_score'] / $quiz['nb_questions']) * 100) ?>% de réponses correctes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">Score le plus élevé</h6>
                        <h2 class="mb-0"><?= $stats['highest_score'] ?>/<?= $quiz['nb_questions'] ?></h2>
                        <p class="card-text small"><?= round(($stats['highest_score'] / $quiz['nb_questions']) * 100) ?>% de réponses correctes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-warning text-dark">
                    <div class="card-body">
                        <h6 class="card-title">Score le plus bas</h6>
                        <h2 class="mb-0"><?= $stats['lowest_score'] !== null ? $stats['lowest_score'] : 'N/A' ?>/<?= $quiz['nb_questions'] ?></h2>
                        <p class="card-text small"><?= $stats['lowest_score'] !== null ? round(($stats['lowest_score'] / $quiz['nb_questions']) * 100) . '% de réponses correctes' : 'Aucun quiz complété' ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filtres -->
        <div class="filter-bar mb-4">
            <form method="get" class="row align-items-end">
                <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
                <div class="col-md-4">
                    <label for="class_id" class="form-label">Filtrer par classe:</label>
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">Toutes les classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['class_id'] ?>" <?= $class_id == $class['class_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['class_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <?php if ($class_id): ?>
                        <a href="?quiz_id=<?= $quiz_id ?>" class="btn btn-outline-secondary">Réinitialiser</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Bouton pour affecter les notes au module -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Affecter ces résultats comme notes du module</h5>
                        <p class="card-text small text-muted mt-1">
                            <i class="fas fa-info-circle me-1"></i>
                            Cette action créera une nouvelle évaluation à partir des scores de ce quiz. 
                            Les scores seront convertis sur une échelle de 20 points.
                        </p>
                    </div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#affectationModal">
                        <i class="fas fa-check-circle me-2"></i>Affecter comme notes
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de confirmation pour l'affectation des notes -->
        <div class="modal fade" id="affectationModal" tabindex="-1" aria-labelledby="affectationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="affectationModalLabel">Confirmation d'affectation des notes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Attention :</strong> Vous êtes sur le point d'affecter les scores de ce quiz comme notes pour le module <strong><?= htmlspecialchars($quiz['code_module']) ?></strong>.</p>
                        
                        <form id="affectationForm" action="affecter_notes.php" method="post">
                            <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
                            <input type="hidden" name="module_id" value="<?= $quiz['id_module'] ?>">
                            <?php if ($class_id): ?>
                            <input type="hidden" name="class_id" value="<?= $class_id ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="nom_devoir" class="form-label">Titre de l'évaluation:</label>
                                <input type="text" class="form-control" id="nom_devoir" name="nom_devoir" 
                                       value="Quiz: <?= htmlspecialchars($quiz['titre']) ?>" required>
                                <div class="form-text">Ce titre apparaîtra dans le détail des notes des étudiants</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="coefficient" class="form-label">Coefficient de cette évaluation:</label>
                                <input type="number" class="form-control" id="coefficient" name="coefficient" 
                                       min="1" max="10" value="1" required>
                                <div class="form-text">Poids de cette évaluation dans la moyenne (1-10)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Pour les étudiants qui n'ont pas complété le quiz:</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="non_completes" id="option1" value="zero" checked>
                                    <label class="form-check-label" for="option1">
                                        Attribuer une note de 0
                                    </label>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="non_completes" id="option2" value="ignore">
                                    <label class="form-check-label" for="option2">
                                        Ignorer (ne pas créer de note pour ces étudiants)
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" form="affectationForm" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Confirmer l'affectation
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tableau des résultats -->
        <?php if (empty($resultats)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Aucun étudiant trouvé pour ce quiz.
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Classe</th>
                                    <th>Statut</th>
                                    <th>Score</th>
                                    <th>Date de passage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $current_class = null;
                                foreach ($resultats as $resultat): 
                                    $score_percent = isset($resultat['score']) ? round(($resultat['score'] / $quiz['nb_questions']) * 100) : 0;
                                ?>
                                    <?php if ($current_class != $resultat['class_id']): ?>
                                        <tr class="table-secondary">
                                            <td colspan="5"><strong>Classe: <?= htmlspecialchars($resultat['class_name']) ?></strong></td>
                                        </tr>
                                        <?php $current_class = $resultat['class_id']; ?>
                                    <?php endif; ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="ms-2">
                                                    <div class="fw-bold"><?= htmlspecialchars($resultat['prenom'] . ' ' . $resultat['nom']) ?></div>
                                                    <div class="small text-muted"><?= htmlspecialchars($resultat['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($resultat['class_name']) ?></td>
                                        <td>
                                            <?php if (isset($resultat['score'])): ?>
                                                <span class="badge bg-success completion-badge">Complété</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger completion-badge">Non complété</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($resultat['score'])): ?>
                                                <div class="d-flex align-items-center">
                                                    <strong class="me-2"><?= $resultat['score'] ?>/<?= $quiz['nb_questions'] ?></strong>
                                                    <div style="width: 100px">
                                                        <div class="progress">
                                                            <div class="progress-bar <?= $score_percent < 50 ? 'bg-danger' : ($score_percent < 75 ? 'bg-warning' : 'bg-success') ?>" 
                                                                role="progressbar" 
                                                                style="width: <?= $score_percent ?>%"
                                                                aria-valuenow="<?= $score_percent ?>" aria-valuemin="0" aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <div class="small text-muted text-end"><?= $score_percent ?>%</div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($resultat['date_passage'])): ?>
                                                <?= date('d/m/Y H:i', strtotime($resultat['date_passage'])) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    <?php elseif (isset($module)): ?>
        <!-- Code existant pour les quiz d'un module -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Quiz du module <?= htmlspecialchars($module['code_module'] . ' - ' . $module['nom_module']) ?></h1>
            <a href="notes.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Retour aux notes</a>
        </div>
        
        <?php if (empty($quizzes)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Aucun quiz n'est disponible pour ce module pour le moment.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($quiz['titre']) ?></h5>
                                <p class="text-muted">
                                    <small><i class="fas fa-question-circle me-1"></i><?= $quiz['nb_questions'] ?> questions</small>
                                    <br>
                                    <small><i class="far fa-calendar-alt me-1"></i>Créé le <?= date('d/m/Y', strtotime($quiz['date_creation'])) ?></small>
                                </p>
                                <?php if ($quiz['quiz_complete']): ?>
                                    <div class="alert alert-success p-2 mb-3">
                                        <i class="fas fa-check-circle me-1"></i>Quiz complété - Score: <?= $quiz['score_user'] ?>/<?= $quiz['nb_questions'] ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning p-2 mb-3">
                                        <i class="fas fa-exclamation-circle me-1"></i>Quiz non complété
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent d-flex">
                                <a href="faire_quiz.php?id=<?= $quiz['id_quiz'] ?>" class="btn btn-primary flex-grow-1">
                                    <?= $quiz['quiz_complete'] ? 'Refaire le quiz' : 'Commencer le quiz' ?>
                                </a>
                                <?php if ($user_role == 1 || $user_role == 2): ?>
                                    <a href="notes.php?quiz_id=<?= $quiz['id_quiz'] ?>" class="btn btn-outline-secondary ms-2" title="Voir les résultats des étudiants">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Code existant pour la liste des modules et notes -->
        <h1 class="mb-4 text-center">Mes notes</h1>
        <?php if (empty($modules)): ?>
            <div class="alert alert-info text-center">Aucune note pour l'instant.</div>
        <?php else: ?>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Note</th>
                        <?php if ($user_role == 0): ?>
                            <th>Quiz</th>
                        <?php endif; ?>
                        <?php if ($user_role == 1 || $user_role == 2): ?>
                            <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modules as $module): ?>
                        <tr>
                            <td><?= htmlspecialchars($module['code_module'] . ' - ' . $module['nom_module']) ?></td>
                            <td>
                                <?php if ($user_role == 0): ?>
                                    <?php if (isset($notes[$module['id_module']])): ?>
                                        <div>
                                            <strong><?= round($notes[$module['id_module']]['moyenne'], 1) ?>/20</strong>
                                            <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#notesModal<?= $module['id_module'] ?>">
                                                <i class="fas fa-info-circle"></i> Détail
                                            </button>
                                        </div>
                                        
                                        <!-- Modal avec détail des notes -->
                                        <div class="modal fade" id="notesModal<?= $module['id_module'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Détail des notes - <?= htmlspecialchars($module['code_module']) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Évaluation</th>
                                                                    <th>Note</th>
                                                                    <th>Coefficient</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php 
                                                                $module_notes = $notes[$module['id_module']];
                                                                $count = count($module_notes['devoirs']);
                                                                for ($i = 0; $i < $count; $i++):
                                                                ?>
                                                                <tr>
                                                                    <td><?= htmlspecialchars($module_notes['devoirs'][$i]) ?></td>
                                                                    <td><?= $module_notes['notes'][$i] ?>/20</td>
                                                                    <td><?= $module_notes['coefficients'][$i] ?></td>
                                                                </tr>
                                                                <?php endfor; ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="table-primary">
                                                                    <th>Moyenne</th>
                                                                    <th colspan="2"><?= $module_notes['moyenne'] ?>/20</th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>`
                            <?php if ($user_role == 0): ?>
                                <td>
                                    <a href="notes.php?module_id=<?= $module['id_module'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-tasks me-1"></i>Voir les quiz
                                    </a>
                                </td>
                            <?php endif; ?>
                            <?php if ($user_role == 1 || $user_role == 2): ?>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="notes.php?module_id=<?= $module['id_module'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-tasks me-1"></i>Quiz
                                        </a>
                                        <a href="attribuer_notes.php?module_id=<?= $module['id_module'] ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-plus me-1"></i>Notes
                                        </a>
                                        <a href="edit_note.php?module_id=<?= $module['id_module'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit me-1"></i>Modifier
                                        </a>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>