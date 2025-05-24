<?php
session_start();
require('db.php');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Affichage des erreurs (à retirer en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Récupère l'ID du quiz
$id_quiz = isset($_POST['id_quiz']) ? intval($_POST['id_quiz']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
if (!$id_quiz) die("Quiz introuvable.");

// Récupère la visibilité
$stmt = $pdo->prepare("SELECT * FROM quiz_visibilite WHERE id_quiz = ?");
$stmt->execute([$id_quiz]);
$vis = $stmt->fetch();

// Blocage accès trop tôt pour étudiants
if ($vis && strtotime($vis['date_debut']) > time() && isset($_SESSION['role']) && $_SESSION['role'] == 0) {
    echo "<div class='alert alert-warning'>Ce quiz n'est pas encore disponible.</div>";
    exit;
}

// Charge le quiz
$stmt = $pdo->prepare("SELECT * FROM quiz WHERE id_quiz = ?");
$stmt->execute([$id_quiz]);
$quiz = $stmt->fetch();
if (!$quiz) die("Quiz non trouvé.");

$questions = json_decode($quiz['questions'], true);

// Calcule la durée du quiz en secondes
$duree = 0;
if ($vis && isset($vis['date_debut']) && isset($vis['date_fin'])) {
    $duree = strtotime($vis['date_fin']) - strtotime($vis['date_debut']);
    if ($duree < 0) $duree = 0;
}

// Blocage si déjà fait (hors POST)
if (
    isset($_SESSION['id_user']) &&
    isset($_SESSION['role']) &&
    $_SESSION['role'] == 0
) {
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM quiz_resultats WHERE id_quiz = ? AND id_etudiant = ?");
    $stmtCheck->execute([$id_quiz, $_SESSION['id_user']]);
    $count = $stmtCheck->fetchColumn();
    // Debug
    // echo "<!-- DEBUG: id_quiz=$id_quiz, id_user=".$_SESSION['id_user'].", count=$count -->";
    if ($count > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo "<div class='alert alert-danger'>Vous ne pouvez pas refaire ce quiz, vous l'avez déjà fait.</div>";
        exit;
    }
}

// Correction du quiz si formulaire soumis
$score = null;
$total = count($questions);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    foreach ($questions as $idx => $q) {
        $userAnswers = isset($_POST["q$idx"]) ? (array)$_POST["q$idx"] : [];
        $correctAnswers = [];
        foreach ($q['answers'] as $aIdx => $a) {
            if ($a['correct']) $correctAnswers[] = (string)$aIdx;
        }
        sort($userAnswers);
        sort($correctAnswers);
        if ($userAnswers == $correctAnswers && count($userAnswers) > 0) {
            $score++;
        }
    }
    if ($_SESSION['role'] == 0) {
        // Si étudiant, on enregistre le score
        $stmtUpdate = $pdo->prepare("INSERT INTO quiz_resultats (id_quiz, id_etudiant, score, temps_utilise) VALUES (?, ?, ?, 0) 
                               ON DUPLICATE KEY UPDATE score = ?");
        $stmtUpdate->execute([$id_quiz, $_SESSION['user_id'], $score, $score]);
        // On peut aussi mettre à jour le temps écoulé si nécessaire
        if (isset($_POST['temps_ecoule'])) {
            $temps_ecoule = intval($_POST['temps_ecoule']);
            $stmtUpdateTime = $pdo->prepare("UPDATE quiz_resultats SET temps_utilise = ? WHERE id_quiz = ? AND id_etudiant = ?");
            $stmtUpdateTime->execute([$temps_ecoule, $id_quiz, $_SESSION['user_id']]);
        }
    }x  
}

// Insertion du résultat si pas déjà fait
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_user']) && isset($_POST['temps_ecoule'])) {
    // Debug
    echo "<pre>";
    echo "id_quiz: $id_quiz\n";
    echo "id_user: " . $_SESSION['id_user'] . "\n";
    echo "score: $score\n";
    echo "temps_ecoule: " . $_POST['temps_ecoule'] . "\n";
    echo "</pre>";

    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM quiz_resultats WHERE id_quiz = ? AND id_etudiant = ?");
    $stmtCheck->execute([$id_quiz, $_SESSION['id_user']]);
    if ($stmtCheck->fetchColumn() == 0) {
        $stmtInsert = $pdo->prepare("INSERT INTO quiz_resultats (id_quiz, id_etudiant, score, temps_utilise) VALUES (?, ?, ?, ?)");
        if ($stmtInsert->execute([$id_quiz, $_SESSION['id_user'], $score, intval($_POST['temps_ecoule'])])) {
            echo "<div style='color:green'>Insertion OK</div>";
        } else {
            echo "<div style='color:red'>Erreur insertion</div>";
        }
    } else {
        echo "<div style='color:orange'>Déjà inséré</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($quiz['titre']) ?></title>
    <?php include("link.php"); ?>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container mt-5 text-white">
    <h1><?= htmlspecialchars($quiz['titre']) ?></h1>

    <?php if ($duree > 0 && isset($_SESSION['role']) && $_SESSION['role'] == 0 && $score === null): ?>
    <div class="alert alert-warning mb-4" style="position:sticky;top:0;z-index:1000;">
        Temps restant : <span id="timer"></span>
    </div>
    <?php endif; ?>

    <?php if ($score !== null): ?>
        <div class="alert alert-info">
            Votre score : <strong><?= $score ?> / <?= $total ?></strong><br>
            Temps utilisé :
            <?php
            if (isset($_POST['temps_ecoule'])) {
                $min = floor($_POST['temps_ecoule'] / 60);
                $sec = $_POST['temps_ecoule'] % 60;
                echo $min . "m " . str_pad($sec, 2, "0", STR_PAD_LEFT) . "s";
            }
            ?>
        </div>
    <?php endif; ?>

    <form id="quizForm" method="post">
        <input type="hidden" name="id_quiz" value="<?= $quiz['id_quiz'] ?>">
        <input type="hidden" name="temps_ecoule" id="temps_ecoule" value="0">
        <?php foreach ($questions as $idx => $q): ?>
            <div class="mb-4">
                <h5><?= ($idx+1) . '. ' . htmlspecialchars($q['text']) ?></h5>
                <?php foreach ($q['answers'] as $aIdx => $a): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="q<?= $idx ?>[]" value="<?= $aIdx ?>" id="q<?= $idx ?>a<?= $aIdx ?>"
                            <?= (isset($_POST["q$idx"]) && in_array($aIdx, (array)$_POST["q$idx"])) ? 'checked' : '' ?>
                            <?= ($score !== null) ? 'disabled' : '' ?>
                        >
                        <label class="form-check-label" for="q<?= $idx ?>a<?= $aIdx ?>">
                            <?= htmlspecialchars($a['text']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <?php if ($score === null): ?>
            <button type="submit" class="btn btn-primary">Valider</button>
        <?php endif; ?>
    </form>

    <?php if ($duree > 0 && isset($_SESSION['role']) && $_SESSION['role'] == 0 && $score === null): ?>
    <script>
    let testFini = <?= ($score !== null) ? 'true' : 'false' ?>;
    let tempsRestant = <?= $duree ?>;
    let tempsPasse = 0;
    let timer = setInterval(function() {
        if (testFini) {
            clearInterval(timer);
            return;
        }
        if (tempsRestant <= 0) {
            clearInterval(timer);
            alert("Temps écoulé ! Le test est terminé.");
            document.getElementById('quizForm').submit();
        } else {
            let min = Math.floor(tempsRestant / 60);
            let sec = tempsRestant % 60;
            document.getElementById('timer').textContent = min + "m " + (sec < 10 ? "0" : "") + sec + "s";
            tempsRestant--;
            tempsPasse++;
            document.getElementById('temps_ecoule').value = tempsPasse;
        }
    }, 1000);

    // Détection de sortie d'onglet/fenêtre ou de changement de visibilité
    let triche = false;
    function arreterPourTriche() {
        if (!triche && !testFini) {
            triche = true;
            alert("Vous avez quitté la page. Le test est arrêté pour suspicion de triche.");
            setTimeout(function() {
                document.getElementById('quizForm').submit();
            }, 200);
        }
    }
    window.onblur = arreterPourTriche;
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            arreterPourTriche();
        }
    }); 
    </script>
    <?php endif; ?>
</div>
</body>
</html>
<?php
echo "<!-- SESSION: "; print_r($_SESSION); echo " -->";
?>