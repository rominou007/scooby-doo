<?php
session_start();
require('db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$utilisateur_selectionne = $user_id;

// Choix utilisateur si admin
if ($role === 0) {
    if (isset($_POST['utilisateur'])) {
        $utilisateur_selectionne = intval($_POST['utilisateur']);
    }
    $stmt = $pdo->query("SELECT user_id, first_name, last_name FROM users WHERE role IN (1, 2)");
    $utilisateurs = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE user_id = :uid");
    $stmt->execute(['uid' => $user_id]);
    $utilisateur_info = $stmt->fetch();
}

// Définir la date de référence
$current_date = $_GET['date'] ?? date('Y-m-d');
$date = new DateTime($current_date);

// Générer les 6 prochains jours sans les dimanches
$jours_dates = [];
$interval = new DateInterval('P1D');
$dt = clone $date;
while (count($jours_dates) < 6) {
    if ($dt->format('w') != 0) { // Exclure les dimanches
        $jours_dates[] = clone $dt;
    }
    $dt->add($interval);
}

// Dates pour navigation
$prev_date = (clone $date)->sub(new DateInterval('P7D'))->format('Y-m-d');
$next_date = (clone $date)->add(new DateInterval('P7D'))->format('Y-m-d');

// Heures affichées
$heures = [];
for ($h = 8; $h <= 20; $h++) {
    $heures[] = sprintf('%02d:00', $h);
}

// Récupération des cours
$start_date = $jours_dates[0]->format('Y-m-d');
$end_date = end($jours_dates)->format('Y-m-d');

$stmt = $pdo->prepare("SELECT c.*, m.module_name, u.first_name, u.last_name 
                       FROM courses c
                       JOIN modules m ON c.module_id = m.module_id
                       JOIN users u ON c.professor_id = u.user_id
                       WHERE (c.professor_id = :uid OR EXISTS (
                           SELECT 1 FROM cours_eleves ce WHERE ce.course_id = c.course_id AND ce.student_id = :uid))
                       AND c.date_cours BETWEEN :start AND :end
                       ORDER BY c.date_cours, c.heure_debut");
$stmt->execute([
    'uid' => $utilisateur_selectionne,
    'start' => $start_date,
    'end' => $end_date
]);
$cours = $stmt->fetchAll();

$planning = [];
foreach ($cours as $cours_info) {
    $date = $cours_info['date_cours'];
    $planning[$date][] = $cours_info;
}

// Tableau des jours en français
$jours_fr = [1 => 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planning</title>
    <?php include("link.php"); ?>
    <style>
        table, th, td { border: 1px solid #ccc; border-collapse: collapse; }
        th, td { padding: 0; text-align: left; vertical-align: top; position: relative; height: 60px; }
        .course-block {
            position: absolute;
            left: 0;
            right: 0;
            margin: 2px;
            padding: 4px;
            border-radius: 4px;
            background-color: #add8e6; /* Bleu clair */
            border: 2px solid #00008b; /* Bleu foncé */
            color: #000;
            font-size: 0.8em;
            overflow: hidden;
        }
        .delete-button {
            position: absolute;
            top: 2px;
            right: 4px;
            background: transparent;
            border: none;
            color: red;
            font-weight: bold;
            cursor: pointer;
            font-size: 1em;
        }
        .hour-label { width: 60px; text-align: right; padding-right: 5px; }
        .planning-container { display: flex; }
        .sidebar { margin-left: 20px; }
    </style>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5 d-flex">
    <div style="flex-grow:1;">
        <h1>Planning</h1>

        <!-- Sélection utilisateur -->
        <?php if ($role === 0): ?>
            <form method="post" class="mb-3">
                <label for="utilisateur">Afficher le planning de :</label>
                <select name="utilisateur" id="utilisateur" onchange="this.form.submit()">
                    <option value="">-- Choisir un utilisateur --</option>
                    <?php foreach ($utilisateurs as $user): ?>
                        <option value="<?= $user['user_id'] ?>" <?= ($utilisateur_selectionne == $user['user_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php else: ?>
            <p>Planning de <?= htmlspecialchars($utilisateur_info['first_name'] . ' ' . $utilisateur_info['last_name']) ?></p>
        <?php endif; ?>

        <!-- Navigation -->
        <div class="mb-3">
            <a href="?date=<?= $prev_date ?>" class="btn btn-secondary">← Semaine précédente</a>
            <a href="?date=<?= $next_date ?>" class="btn btn-secondary">Semaine suivante →</a>
        </div>

        <!-- Planning -->
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Heure / Jour</th>
                <?php foreach ($jours_dates as $dt): ?>
                    <th><?= $jours_fr[$dt->format('N')] . ' ' . $dt->format('d/m') ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($heures as $heure): ?>
                <tr>
                    <td class="hour-label"><?= $heure ?></td>
                    <?php foreach ($jours_dates as $dt): 
                        $date_str = $dt->format('Y-m-d');
                        $timestamp_heure = strtotime($heure);
                        $cours_jour = $planning[$date_str] ?? [];
                    ?>
                        <td>
                            <?php foreach ($cours_jour as $cours_item): 
                                $debut = strtotime($cours_item['heure_debut']);
                                $fin = strtotime($cours_item['heure_fin']);
                                if ($debut >= $timestamp_heure && $debut < $timestamp_heure + 3600): // cours dans cette heure
                                    $top_percent = ((($debut % 3600) / 3600) * 100);
                                    $height_percent = (($fin - $debut) / 3600) * 100;
                            ?>
                                <div class="course-block" style="top: <?= $top_percent ?>%; height: <?= $height_percent ?>%;">
                                    <?php if ($role === 0): ?>
                                        <form method="post" action="supprimer_cours.php" onsubmit="return confirm('Supprimer ce cours ?');" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $cours_item['course_id'] ?>">
                                            <button type="submit" class="delete-button" title="Supprimer le cours">×</button>
                                        </form>
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($cours_item['title']) ?></strong><br>
                                    <small><?= htmlspecialchars($cours_item['module_name']) ?></small><br>
                                    <small><?= htmlspecialchars($cours_item['first_name'] . ' ' . $cours_item['last_name']) ?></small>
                                </div>
                            <?php endif; endforeach; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="sidebar">
        <a href="ajouter_cours.php" class="btn btn-success mb-3">Ajouter un cours</a>
    </div>
</div>

</body>
</html>
