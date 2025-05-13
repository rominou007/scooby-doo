<?php
session_start();
require('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$current_role = $_SESSION['role'];
$target_user_id = ($current_role != 2) ? $current_user_id : null;

// Si admin : gestion des actions
if ($current_role == 2 && isset($_GET['view_user']) && is_numeric($_GET['view_user'])) {
    $target_user_id = intval($_GET['view_user']);
}

// Ajout d'un cours (admin seulement)
if ($current_role == 2 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $module_id = intval($_POST['module_id']);
    $user_id = intval($_POST['user_id']);
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    $stmt = $pdo->prepare("INSERT INTO plannings (user_id, module_id, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $module_id, $start, $end]);
}

// Suppression d’un cours (admin seulement)
if ($current_role == 2 && isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM plannings WHERE planning_id = ?");
    $stmt->execute([$_GET['delete']]);
    // Redirection pour éviter re-soumission
    header("Location: planning.php?view_user=" . $_GET['view_user']);
    exit();
}

// Récupération de la liste des utilisateurs (hors admins)
$professeurs = $etudiants = [];
if ($current_role == 2) {
    $stmt = $pdo->query("SELECT id, username, role FROM users WHERE role != 2 ORDER BY username");
    while ($row = $stmt->fetch()) {
        ($row['role'] == 1) ? $professeurs[] = $row : $etudiants[] = $row;
    }
}

// Récupération des modules
$modules = $pdo->query("SELECT * FROM modules ORDER BY module_name")->fetchAll();

// Récupération du planning
$planning = [];
if ($target_user_id !== null) {
    $stmt = $pdo->prepare("
        SELECT p.*, m.module_name 
        FROM plannings p 
        JOIN modules m ON p.module_id = m.module_id 
        WHERE p.user_id = :uid
    ");
    $stmt->execute(['uid' => $target_user_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($courses as $course) {
        $day = date('N', strtotime($course['start_time']));
        $hour = date('H:i', strtotime($course['start_time']));
        $planning[$day][$hour][] = $course;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planning</title>
    <?php include("link.php"); ?>
    <style>
        table td, table th { text-align: center; vertical-align: middle; }
        .horaire { width: 80px; background: #f8f9fa; font-weight: bold; }
    </style>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5">
    <h1 class="mb-4">Planning de la semaine</h1>

    <?php if ($current_role == 2): ?>
        <div class="mb-4">
            <h4>Voir l'emploi du temps de :</h4>
            <form method="get" class="row g-3">
                <div class="col-md-6">
                    <label>Professeurs :</label>
                    <select name="view_user" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($professeurs as $prof): ?>
                            <option value="<?= $prof['id'] ?>" <?= ($target_user_id === $prof['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prof['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Élèves :</label>
                    <select name="view_user" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($etudiants as $eleve): ?>
                            <option value="<?= $eleve['id'] ?>" <?= ($target_user_id === $eleve['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($eleve['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($target_user_id !== null): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="horaire">Heure</th>
                    <?php
                    $jours = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
                    foreach ($jours as $jour) {
                        echo "<th>$jour</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($h = 8; $h <= 20; $h++): ?>
                    <?php $heure = sprintf("%02d:00", $h); ?>
                    <tr>
                        <td class="horaire"><?= $heure ?></td>
                        <?php for ($d = 1; $d <= 6; $d++): ?>
                            <td>
                                <?php if (!empty($planning[$d][$heure])): ?>
                                    <?php foreach ($planning[$d][$heure] as $c): ?>
                                        <div class="bg-primary text-white rounded p-1 mb-1">
                                            <?= htmlspecialchars($c['module_name']) ?>
                                            <?php if ($current_role == 2): ?>
                                                <a href="?view_user=<?= $target_user_id ?>&delete=<?= $c['planning_id'] ?>" class="btn btn-sm btn-danger ms-2">✖</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <?php if ($current_role == 2): ?>
            <h4 class="mt-5">Ajouter un cours pour <?= $target_user_id ?></h4>
            <form method="post" class="row g-3">
                <input type="hidden" name="add_course" value="1">
                <input type="hidden" name="user_id" value="<?= $target_user_id ?>">

                <div class="col-md-4">
                    <label>Module :</label>
                    <select name="module_id" class="form-select" required>
                        <?php foreach ($modules as $m): ?>
                            <option value="<?= $m['module_id'] ?>"><?= htmlspecialchars($m['module_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Début :</label>
                    <input type="datetime-local" name="start_time" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Fin :</label>
                    <input type="datetime-local" name="end_time" class="form-control" required>
                </div>

                <div class="col-12">
                    <button class="btn btn-success" type="submit">Ajouter</button>
                </div>
            </form>
        <?php endif; ?>
    <?php elseif ($current_role == 2): ?>
        <div class="alert alert-info">Sélectionnez un utilisateur pour voir et modifier son emploi du temps.</div>
    <?php endif; ?>
</div>

</body>
</html>
