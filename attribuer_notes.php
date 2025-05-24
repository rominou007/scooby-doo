<?php
session_start();
require('db.php');

// Vérifier que l'utilisateur est connecté et est un professeur/admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 1 && $_SESSION['role'] != 2)) {
    header("Location: login.php");
    exit();
}

// Initialiser les variables
$success_message = "";
$error_message = "";
$classes = [];
$modules = [];
$students = [];
$notes = [];
$selected_class = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$selected_module = isset($_GET['module_id']) ? intval($_GET['module_id']) : 0;

// Récupérer toutes les classes
$stmt = $pdo->query("SELECT class_id, class_name FROM classes ORDER BY class_name");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les modules
$stmt = $pdo->query("SELECT id_module, code_module, nom_module FROM modules ORDER BY code_module");
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si une classe et un module sont sélectionnés
if ($selected_class && $selected_module) {
    // Récupérer les étudiants de cette classe
    $stmt = $pdo->prepare("
        SELECT u.id_user, u.prenom, u.nom, u.email
        FROM user u
        JOIN student_classes sc ON u.id_user = sc.student_id
        WHERE sc.class_id = ? AND u.role = 0
        ORDER BY u.nom, u.prenom
    ");
    $stmt->execute([$selected_class]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Traitement du formulaire de soumission des notes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_notes'])) {
    $module_id = intval($_POST['module_id']);
    $class_id = intval($_POST['class_id']);
    $nom_devoir = trim($_POST['nom_devoir']);
    $coefficient = intval($_POST['coefficient']);
    
    // Validations
    if (empty($nom_devoir)) {
        $nom_devoir = "Évaluation du " . date('d/m/Y');
    }
    
    if ($coefficient <= 0) {
        $coefficient = 1;
    }
    
    if ($module_id && $class_id) {
        $updated_count = 0;
        $inserted_count = 0;
        
        // Parcourir les notes soumises
        foreach ($_POST as $key => $value) {
            // Si c'est une note d'étudiant (format note_STUDENT_ID)
            if (strpos($key, 'note_') === 0 && is_numeric($value)) {
                $student_id = intval(substr($key, 5)); // Extraire l'ID étudiant
                $note_value = floatval($value);
                
                // Valider la note (entre 0 et 20)
                if ($note_value < 0) $note_value = 0;
                if ($note_value > 20) $note_value = 20;
                
                // Insérer une nouvelle note
                $stmt = $pdo->prepare("INSERT INTO notes (id_etudiant, id_module, note, nom_devoir, coefficient) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$student_id, $module_id, $note_value, $nom_devoir, $coefficient]);
                $inserted_count++;
            }
        }
        
        if ($inserted_count > 0) {
            $success_message = "$inserted_count notes ajoutées avec succès pour le devoir \"$nom_devoir\" (coefficient $coefficient).";
        } else {
            $error_message = "Aucune note n'a été ajoutée.";
        }
    }
}

// Récupérer le nom de la classe et du module sélectionnés
$class_name = "";
$module_name = "";

if ($selected_class) {
    $stmt = $pdo->prepare("SELECT class_name FROM classes WHERE class_id = ?");
    $stmt->execute([$selected_class]);
    $class_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $class_name = $class_result ? $class_result['class_name'] : "";
}

if ($selected_module) {
    $stmt = $pdo->prepare("SELECT code_module, nom_module FROM modules WHERE id_module = ?");
    $stmt->execute([$selected_module]);
    $module_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $module_name = $module_result ? $module_result['code_module'] . ' - ' . $module_result['nom_module'] : "";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attribution de notes</title>
    <?php include("link.php"); ?>
    <style>
        .sticky-header {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 100;
            padding: 15px 0;
        }
        .note-input {
            max-width: 80px;
        }
        .student-row:nth-child(odd) {
            background-color: rgba(0, 0, 0, 0.03);
        }
        .student-row {
            transition: all 0.2s ease;
        }
        .student-row:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }
        .notes-container {
            max-height: 70vh;
            overflow-y: auto;
        }
        .btn-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 99;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .invalid-note {
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include("navbar.php"); ?>
    
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h1><i class="fas fa-graduation-cap me-2"></i>Attribution de notes</h1>
                <p class="text-muted">Attribuez des notes directement aux étudiants d'une classe</p>
            </div>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Sélection de la classe et du module</h5>
            </div>
            <div class="card-body">
                <form method="get" action="attribuer_notes.php" class="row g-3">
                    <div class="col-md-5">
                        <label for="class_id" class="form-label">Classe</label>
                        <select name="class_id" id="class_id" class="form-select" required>
                            <option value="">-- Sélectionner une classe --</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['class_id'] ?>" <?= $selected_class == $class['class_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($class['class_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="module_id" class="form-label">Module</label>
                        <select name="module_id" id="module_id" class="form-select" required>
                            <option value="">-- Sélectionner un module --</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?= $module['id_module'] ?>" <?= $selected_module == $module['id_module'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($module['code_module'] . ' - ' . $module['nom_module']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Afficher
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($selected_class && $selected_module): ?>
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-pen me-2"></i>
                        Notes: <?= htmlspecialchars($module_name) ?>
                    </h5>
                    <small>Classe: <?= htmlspecialchars($class_name) ?></small>
                </div>
                
                <form method="post" id="notesForm">
                    <input type="hidden" name="module_id" value="<?= $selected_module ?>">
                    <input type="hidden" name="class_id" value="<?= $selected_class ?>">
                    
                    <div class="sticky-header">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label for="nom_devoir" class="form-label">Titre du devoir / évaluation</label>
                                        <input type="text" class="form-control" id="nom_devoir" name="nom_devoir" 
                                               value="Évaluation du <?= date('d/m/Y') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label for="coefficient" class="form-label">Coefficient</label>
                                        <input type="number" class="form-control" id="coefficient" name="coefficient" 
                                               min="1" max="10" value="1" required>
                                        <div class="form-text">Poids de cette évaluation dans la moyenne</div>
                                    </div>
                                </div>
                                <div class="col-md-5 text-end">
                                    <div class="alert alert-info mb-0 d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-info-circle me-2"></i>
                                            <span id="counter"><?= count($students) ?></span> étudiants dans cette classe
                                        </div>
                                        <button type="submit" name="submit_notes" class="btn btn-success">
                                            <i class="fas fa-save me-2"></i>Enregistrer les notes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="notes-container">
                        <?php if (empty($students)): ?>
                            <div class="alert alert-warning m-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>Aucun étudiant trouvé dans cette classe.
                            </div>
                        <?php else: ?>
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th style="width: 40%">Étudiant</th>
                                        <th style="width: 35%">Email</th>
                                        <th style="width: 20%">Note (/20)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $index => $student): ?>
                                        <tr class="student-row">
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($student['prenom'] . ' ' . $student['nom']) ?></div>
                                            </td>
                                            <td>
                                                <div class="small text-muted"><?= htmlspecialchars($student['email']) ?></div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input 
                                                        type="number" 
                                                        name="note_<?= $student['id_user'] ?>" 
                                                        class="form-control note-input" 
                                                        min="0" 
                                                        max="20" 
                                                        step="0.5"
                                                        placeholder="--"
                                                        onchange="validateNote(this)"
                                                    >
                                                    <span class="input-group-text">/20</span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Bouton flottant pour soumettre le formulaire -->
            <button type="submit" form="notesForm" name="submit_notes" class="btn btn-success btn-lg btn-float rounded-circle shadow">
                <i class="fas fa-save"></i>
            </button>
        <?php endif; ?>
    </div>
    
    <script>
    // Validation des notes (entre 0 et 20)
    function validateNote(input) {
        var value = parseFloat(input.value);
        if (isNaN(value) || value < 0) {
            input.value = '';
            input.classList.add('invalid-note');
        } else if (value > 20) {
            input.value = 20;
            input.classList.add('invalid-note');
            setTimeout(function() {
                input.classList.remove('invalid-note');
            }, 500);
        } else {
            input.classList.remove('invalid-note');
        }
    }
    
    // Vérifier toutes les notes avant soumission
    document.getElementById('notesForm')?.addEventListener('submit', function(e) {
        var inputs = document.querySelectorAll('.note-input');
        var valid = true;
        var hasValues = false;
        
        inputs.forEach(function(input) {
            var value = input.value.trim();
            if (value !== '') {
                hasValues = true;
                var numberValue = parseFloat(value);
                if (isNaN(numberValue) || numberValue < 0 || numberValue > 20) {
                    input.classList.add('invalid-note');
                    valid = false;
                }
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Certaines notes sont invalides. Veuillez vérifier que toutes les notes sont comprises entre 0 et 20.');
        } else if (!hasValues) {
            e.preventDefault();
            alert('Veuillez entrer au moins une note avant de soumettre le formulaire.');
        }
    });
    </script>
</body>
</html>