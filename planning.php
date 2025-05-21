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

// Si c'est un admin (role = 2), il peut choisir un autre utilisateur
if ((int)$role === 2) {
    if (isset($_POST['utilisateur']) && is_numeric($_POST['utilisateur'])) {
        $utilisateur_selectionne = intval($_POST['utilisateur']);
    }

    // On récupère tous les utilisateurs (profs et étudiants)
    $stmt = $pdo->query("SELECT id_user, prenom, nom FROM user WHERE role IN (0,1)");
    $utilisateurs = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT prenom, nom FROM user WHERE id_user = :uid");
    $stmt->execute(['uid' => $user_id]);
    $utilisateur_info = $stmt->fetch();
}

// Date de référence
$current_date = $_GET['date'] ?? date('Y-m-d');
$date = new DateTime($current_date);

// Générer les 6 prochains jours sans dimanche
$jours_dates = [];
$interval = new DateInterval('P1D');
$dt = clone $date;
while (count($jours_dates) < 6) {
    if ($dt->format('w') != 0) {
        $jours_dates[] = clone $dt;
    }
    $dt->add($interval);
}

$prev_date = (clone $date)->sub(new DateInterval('P7D'))->format('Y-m-d');
$next_date = (clone $date)->add(new DateInterval('P7D'))->format('Y-m-d');

$heures = [];
for ($h = 8; $h <= 20; $h++) {
    $heures[] = sprintf('%02d:00', $h);
}

// Requête des cours
$stmt = $pdo->prepare("
    SELECT c.*, m.nom_module, u.prenom, u.nom
    FROM cours c
    JOIN modules m ON c.id_module = m.id_module
    JOIN user u ON c.id_prof = u.id_user
    WHERE 
        (c.id_prof = :uid
        OR m.class_id IN (
            SELECT class_id FROM student_classes WHERE student_id = :uid
        ))
        AND c.date_cours BETWEEN :start AND :end
    ORDER BY c.date_cours, c.heure_debut
");
$stmt->execute([
    'uid' => $utilisateur_selectionne,
    'start' => $jours_dates[0]->format('Y-m-d'),
    'end' => end($jours_dates)->format('Y-m-d')
]);
$cours = $stmt->fetchAll();

$planning = [];
foreach ($cours as $cours_info) {
    $date_cours = $cours_info['date_cours'];
    $planning[$date_cours][] = $cours_info;
}

$jours_fr = [1 => 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planning</title>
    <?php include("link.php"); ?>
    <style>
        /* tes styles ici */
    </style>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5 d-flex">
    <div style="flex-grow:1;">
        <h1>Planning</h1>

        <?php if ((int)$role === 2): ?>
            <form method="post" class="mb-3">
                <label for="utilisateur">Afficher le planning de :</label>
                <select name="utilisateur" id="utilisateur" onchange="this.form.submit()">
                    <option value="">-- Choisir un utilisateur --</option>
                    <?php foreach ($utilisateurs as $user): ?>
                        <option value="<?= $user['id_user'] ?>" <?= ($utilisateur_selectionne == $user['id_user']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php else: ?>
            <p>Planning de <?= htmlspecialchars($utilisateur_info['prenom'] . ' ' . $utilisateur_info['nom']) ?></p>
        <?php endif; ?>

        <div class="mb-3">
            <a href="?date=<?= $prev_date ?>" class="btn btn-secondary">← Semaine précédente</a>
            <a href="?date=<?= $next_date ?>" class="btn btn-secondary">Semaine suivante →</a>
        </div>

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
                                if ($debut >= $timestamp_heure && $debut < $timestamp_heure + 3600):
                                    $top_percent = ((($debut % 3600) / 3600) * 100);
                                    $height_percent = (($fin - $debut) / 3600) * 100;
                                    ?>
                                    <div class="course-block" style="top: <?= $top_percent ?>%; height: <?= $height_percent ?>%;">
                                        <?php if ((int)$role === 2): ?>
                                            <form method="post" action="supprimer_cours.php" onsubmit="return confirm('Supprimer ce cours ?');" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $cours_item['id_cours'] ?>">
                                                <button type="submit" class="delete-button" title="Supprimer le cours">×</button>
                                            </form>
                                        <?php endif; ?>
                                        <strong><?= htmlspecialchars($cours_item['titre']) ?></strong><br>
                                        <small><?= htmlspecialchars($cours_item['nom_module']) ?></small><br>
                                        <small><?= htmlspecialchars($cours_item['prenom'] . ' ' . $cours_item['nom']) ?></small>
                                    </div>
                                <?php endif;
                            endforeach; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="sidebar">
        <?php if ((int)$role === 2): ?>
            <a href="ajouter_cours.php" class="btn btn-success mb-3">Ajouter un cours</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
