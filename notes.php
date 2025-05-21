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

// Récupérer les modules
$modules = $pdo->query("SELECT * FROM modules")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les notes de l'utilisateur si étudiant
$notes = [];
if ($user_role == 0) {
    $stmt = $pdo->prepare("SELECT n.id_module, n.note FROM notes n WHERE n.user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $notes[$row['id_module']] = $row['note'];
    }
}

$_SESSION['user_id'] = $user['id_user'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes notes</title>
    <?php include("link.php"); ?>
</head>
<?php include("navbar.php"); ?>
<body>
<div class="container mt-5">
    <h1 class="mb-4 text-center">Mes notes</h1>
    <?php if (empty($modules)): ?>
        <div class="alert alert-info text-center">Aucune note pour l'instant.</div>
    <?php else: ?>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Note</th>
                    <?php if ($user_role == 1 || $user_role == 2): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $module): ?>
                    <tr>
                        <td><?= htmlspecialchars($module['module_name']) ?></td>
                        <td>
                            <?php
                            if ($user_role == 0) {
                                // Étudiant : affiche sa note ou "-"
                                echo isset($notes[$module['id_module']]) ? htmlspecialchars($notes[$module['id_module']]) : "-";
                            } else {
                                // Prof/Admin : affiche "-" (ou rien, à adapter)
                                echo "-";
                            }
                            ?>
                        </td>
                        <?php if ($user_role == 1 || $user_role == 2): ?>
                            <td>
                                <a href="edit_note.php?module_id=<?= $module['module_id'] ?>" class="btn btn-sm btn-warning">
                                    Modifier la note
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>