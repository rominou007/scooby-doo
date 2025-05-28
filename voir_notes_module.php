<?php
session_start();
require("db.php");

// Vérifier que l'utilisateur est connecté et est professeur ou administrateur
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 1 && $_SESSION['role'] != 2)) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Récupérer l'ID du module
$module_id = isset($_GET['module_id']) ? intval($_GET['module_id']) : 0;

// Vérifier si le module existe
$stmt = $pdo->prepare("SELECT m.id_module, m.code_module, m.nom_module, m.class_id, c.class_name 
                      FROM modules m 
                      LEFT JOIN classes c ON m.class_id = c.class_id
                      WHERE m.id_module = ?");
$stmt->execute([$module_id]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    $_SESSION['error'] = "Module non trouvé.";
    header("Location: notes.php");
    exit();
}

// Si c'est un professeur, vérifier qu'il est bien associé à ce module
if ($user_role == 1) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM profs_modules WHERE id_prof = ? AND id_module = ?");
    $stmt->execute([$user_id, $module_id]);
    if ($stmt->fetchColumn() == 0) {
        $_SESSION['error'] = "Vous n'êtes pas autorisé à consulter les notes de ce module.";
        header("Location: notes.php");
        exit();
    }
}

// Récupération des étudiants et de leurs notes pour ce module
// Si le module est associé à une classe, on récupère les étudiants de cette classe
if ($module['class_id']) {
    $stmt = $pdo->prepare("
        SELECT 
            u.id_user, u.prenom, u.nom, u.email, 
            c.class_name,
            (SELECT SUM(n.note * n.coefficient) / SUM(n.coefficient) 
             FROM notes n 
             WHERE n.id_etudiant = u.id_user 
             AND n.id_module = ?) AS moyenne,
            (SELECT GROUP_CONCAT(n.nom_devoir SEPARATOR '|')
             FROM notes n 
             WHERE n.id_etudiant = u.id_user 
             AND n.id_module = ?) AS devoirs,
            (SELECT GROUP_CONCAT(n.note SEPARATOR '|')
             FROM notes n 
             WHERE n.id_etudiant = u.id_user 
             AND n.id_module = ?) AS notes,
            (SELECT GROUP_CONCAT(n.coefficient SEPARATOR '|')
             FROM notes n 
             WHERE n.id_etudiant = u.id_user 
             AND n.id_module = ?) AS coefficients
        FROM user u
        JOIN student_classes sc ON u.id_user = sc.student_id
        JOIN classes c ON sc.class_id = c.class_id
        WHERE u.role = 0 AND sc.class_id = ?
        ORDER BY u.nom, u.prenom
    ");
    $stmt->execute([$module_id, $module_id, $module_id, $module_id, $module['class_id']]);
} else {
    // Si le module n'est pas associé à une classe, on récupère tous les étudiants qui ont des notes pour ce module
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            u.id_user, u.prenom, u.nom, u.email, 
            c.class_name,
            (SELECT SUM(n.note * n.coefficient) / SUM(n.coefficient) 
             FROM notes n 
             WHERE n.id_etudiant = u.id_user 
             AND n.id_module = ?) AS moyenne,
            (SELECT GROUP_CONCAT(n.nom_devoir SEPARATOR '|')
             FROM notes n 
             WHERE n.id_etudiant = u.id_user 
             AND n.id_module = ?) AS devoirs,
            (SELECT GROUP_CONCAT(n.note SEPARATOR '|')
             FROM notes n 
             WHERE n.id_etudiant = u.id_user 
             AND n.id_module = ?) AS notes,
            (SELECT GROUP_CONCAT(n.coefficient SEPARATOR '|')
             FROM notes n 
             WHERE n.id_etudiant = u.id_user 
             AND n.id_module = ?) AS coefficients
        FROM user u
        JOIN notes n ON u.id_user = n.id_etudiant
        LEFT JOIN student_classes sc ON u.id_user = sc.student_id
        LEFT JOIN classes c ON sc.class_id = c.class_id
        WHERE u.role = 0 AND n.id_module = ?
        ORDER BY u.nom, u.prenom
    ");
    $stmt->execute([$module_id, $module_id, $module_id, $module_id, $module_id]);
}

$etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer des statistiques pour le module
$stats = [
    'moyenne_generale' => 0,
    'note_max' => 0,
    'note_min' => 20,
    'nb_etudiants_avec_notes' => 0,
    'repartition' => [
        'insuffisant' => 0, // < 10
        'passable' => 0,    // 10-12
        'assez_bien' => 0,  // 12-14
        'bien' => 0,        // 14-16
        'tres_bien' => 0    // > 16
    ]
];

$total_moyennes = 0;

foreach ($etudiants as $etudiant) {
    if (isset($etudiant['moyenne']) && $etudiant['moyenne'] !== null) {
        $moyenne = floatval($etudiant['moyenne']);
        $stats['nb_etudiants_avec_notes']++;
        $total_moyennes += $moyenne;
        
        // Note max et min
        if ($moyenne > $stats['note_max']) $stats['note_max'] = $moyenne;
        if ($moyenne < $stats['note_min']) $stats['note_min'] = $moyenne;
        
        // Répartition
        if ($moyenne < 10) {
            $stats['repartition']['insuffisant']++;
        } elseif ($moyenne < 12) {
            $stats['repartition']['passable']++;
        } elseif ($moyenne < 14) {
            $stats['repartition']['assez_bien']++;
        } elseif ($moyenne < 16) {
            $stats['repartition']['bien']++;
        } else {
            $stats['repartition']['tres_bien']++;
        }
    }
}

// Calculer la moyenne générale
if ($stats['nb_etudiants_avec_notes'] > 0) {
    $stats['moyenne_generale'] = round($total_moyennes / $stats['nb_etudiants_avec_notes'], 2);
}

// Si aucun étudiant n'a de note, initialiser note_min à 0
if ($stats['note_min'] == 20 && $stats['nb_etudiants_avec_notes'] == 0) {
    $stats['note_min'] = 0;
}

// Récupérer la liste des évaluations pour ce module
$stmt = $pdo->prepare("
    SELECT DISTINCT nom_devoir 
    FROM notes 
    WHERE id_module = ? 
    ORDER BY nom_devoir
");
$stmt->execute([$module_id]);
$evaluations = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notes des étudiants - <?= htmlspecialchars($module['code_module']) ?></title>
    <?php include("link.php"); ?>
</head>
<body>
    <?php include("navbar.php"); ?>
    
    <div class="container mt-5">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Notes des étudiants</h1>
                <h5 class="text-muted"><?= htmlspecialchars($module['code_module'] . ' - ' . $module['nom_module']) ?></h5>
                <?php if ($module['class_id']): ?>
                    <p class="small">Classe: <?= htmlspecialchars($module['class_name']) ?></p>
                <?php endif; ?>
            </div>
            <div>
                <a href="notes.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour aux notes
                </a>
                <a href="edit_note.php?module_id=<?= $module['id_module'] ?>" class="btn btn-warning ms-2">
                    <i class="fas fa-edit me-2"></i>Modifier les notes
                </a>
                <a href="attribuer_notes.php?module_id=<?= $module['id_module'] ?>" class="btn btn-success ms-2">
                    <i class="fas fa-plus me-2"></i>Ajouter des notes
                </a>
            </div>
        </div>
        
        <!-- Statistiques du module -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">Moyenne générale</h6>
                        <h2 class="mb-0"><?= number_format($stats['moyenne_generale'], 2) ?>/20</h2>
                        <p class="card-text small">Sur <?= $stats['nb_etudiants_avec_notes'] ?> étudiants</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">Meilleure moyenne</h6>
                        <h2 class="mb-0"><?= number_format($stats['note_max'], 2) ?>/20</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-danger text-white">
                    <div class="card-body">
                        <h6 class="card-title">Note minimale</h6>
                        <h2 class="mb-0"><?= number_format($stats['note_min'], 2) ?>/20</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">Répartition</h6>
                        <div class="small">
                            <div>< 10: <?= $stats['repartition']['insuffisant'] ?> étudiant(s)</div>
                            <div>10-12: <?= $stats['repartition']['passable'] ?> étudiant(s)</div>
                            <div>12-14: <?= $stats['repartition']['assez_bien'] ?> étudiant(s)</div>
                            <div>14-16: <?= $stats['repartition']['bien'] ?> étudiant(s)</div>
                            <div>> 16: <?= $stats['repartition']['tres_bien'] ?> étudiant(s)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Liste des évaluations du module -->
        <?php if (!empty($evaluations)): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Évaluations du module</h5>
                    <div class="d-flex flex-wrap">
                        <?php foreach ($evaluations as $evaluation): ?>
                            <span class="badge bg-secondary me-2 mb-2 p-2"><?= htmlspecialchars($evaluation) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Tableau des notes des étudiants -->
        <?php if (empty($etudiants)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Aucun étudiant trouvé pour ce module.
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-notes">
                            <thead>
                                <tr class="table-light">
                                    <th>Étudiant</th>
                                    <th>Classe</th>
                                    <th>Moyenne</th>
                                    <th>Détail des notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($etudiants as $etudiant): ?>
                                    <tr>
                                        <td width="25%">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?></div>
                                                    <div class="small text-muted"><?= htmlspecialchars($etudiant['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td width="15%">
                                            <?= $etudiant['class_name'] ? htmlspecialchars($etudiant['class_name']) : '<span class="text-muted">Non assigné</span>' ?>
                                        </td>
                                        <td width="15%">
                                            <?php if (isset($etudiant['moyenne']) && $etudiant['moyenne'] !== null): ?>
                                                <span class="fw-bold <?= $etudiant['moyenne'] < 10 ? 'text-danger' : 'text-success' ?>">
                                                    <?= number_format($etudiant['moyenne'], 2) ?>/20
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Pas de note</span>
                                            <?php endif; ?>
                                        </td>
                                        <td width="45%">
                                            <?php if (!empty($etudiant['devoirs'])): 
                                                $devoirs = explode('|', $etudiant['devoirs']);
                                                $notes = explode('|', $etudiant['notes']);
                                                $coefficients = explode('|', $etudiant['coefficients']);
                                                ?>
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Évaluation</th>
                                                            <th>Note</th>
                                                            <th>Coef.</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php for ($i = 0; $i < count($devoirs); $i++): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($devoirs[$i]) ?></td>
                                                                <td class="<?= $notes[$i] < 10 ? 'text-danger' : 'text-success' ?>">
                                                                    <?= $notes[$i] ?>/20
                                                                </td>
                                                                <td><?= $coefficients[$i] ?></td>
                                                            </tr>
                                                        <?php endfor; ?>
                                                    </tbody>
                                                </table>
                                            <?php else: ?>
                                                <span class="text-muted">Aucune note enregistrée</span>
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
    </div>
    
    <script>
    // Ajouter ici le javascript nécessaire
    document.addEventListener('DOMContentLoaded', function() {
        // Éventuellement ajouter des interactions JS ici
    });
    </script>
</body>
</html>