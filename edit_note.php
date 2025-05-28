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
$stmt = $pdo->prepare("SELECT id_module, code_module, nom_module FROM modules WHERE id_module = ?");
$stmt->execute([$module_id]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    $_SESSION['error'] = "Module non trouvé.";
    header("Location: notes.php");
    exit();
}

// Vérifier si le professeur est associé au module (sauf pour les administrateurs)
if ($user_role == 1) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM profs_modules WHERE id_prof = ? AND id_module = ?");
    $stmt->execute([$user_id, $module_id]);
    if ($stmt->fetchColumn() == 0) {
        $_SESSION['error'] = "Vous n'êtes pas autorisé à modifier les notes de ce module.";
        header("Location: notes.php");
        exit();
    }
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Parcourir toutes les notes soumises (mise à jour des notes existantes)
        if (isset($_POST['notes'])) {
            foreach ($_POST['notes'] as $note_id => $note_data) {
                $note_value = isset($note_data['valeur']) ? floatval($note_data['valeur']) : null;
                $coefficient = isset($note_data['coefficient']) ? intval($note_data['coefficient']) : 1;
                
                // Valider les données
                if ($note_value === null || $note_value < 0 || $note_value > 20) {
                    throw new Exception("La note doit être comprise entre 0 et 20.");
                }
                
                if ($coefficient < 1 || $coefficient > 10) {
                    throw new Exception("Le coefficient doit être compris entre 1 et 10.");
                }
                
                // Mettre à jour la note
                $update_stmt = $pdo->prepare("UPDATE notes SET note = ?, coefficient = ? WHERE id_note = ?");
                $update_stmt->execute([$note_value, $coefficient, $note_id]);
            }
        }
        
        // Traitement des nouvelles notes
        if (isset($_POST['new_notes'])) {
            foreach ($_POST['new_notes'] as $student_id => $student_notes) {
                foreach ($student_notes as $note_data) {
                    if (
                        isset($note_data['nom_devoir']) && !empty($note_data['nom_devoir']) && 
                        isset($note_data['valeur']) && $note_data['valeur'] !== ''
                    ) {
                        $note_value = floatval($note_data['valeur']);
                        $coefficient = isset($note_data['coefficient']) ? intval($note_data['coefficient']) : 1;
                        $nom_devoir = trim($note_data['nom_devoir']);
                        
                        // Valider les données
                        if ($note_value < 0 || $note_value > 20) {
                            throw new Exception("La note doit être comprise entre 0 et 20.");
                        }
                        
                        if ($coefficient < 1 || $coefficient > 10) {
                            throw new Exception("Le coefficient doit être compris entre 1 et 10.");
                        }
                        
                        if (empty($nom_devoir)) {
                            throw new Exception("Le nom du devoir ne peut pas être vide.");
                        }
                        
                        // Insérer la nouvelle note
                        $insert_stmt = $pdo->prepare(
                            "INSERT INTO notes (id_etudiant, id_module, note, nom_devoir, coefficient) 
                             VALUES (?, ?, ?, ?, ?)"
                        );
                        $insert_stmt->execute([$student_id, $module_id, $note_value, $nom_devoir, $coefficient]);
                    }
                }
            }
        }
        
        // Si des notes doivent être supprimées
        if (isset($_POST['delete_notes']) && !empty($_POST['delete_notes'])) {
            $delete_ids = array_keys($_POST['delete_notes']);
            $placeholders = implode(',', array_fill(0, count($delete_ids), '?'));
            
            $delete_stmt = $pdo->prepare("DELETE FROM notes WHERE id_note IN ($placeholders)");
            $delete_stmt->execute($delete_ids);
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Notes mises à jour avec succès.";
        header("Location: notes.php?module_id=$module_id");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de la mise à jour des notes : " . $e->getMessage();
    }
}

// Récupérer toutes les classes
$stmt = $pdo->query("SELECT class_id, class_name FROM classes ORDER BY class_name");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Variable pour stocker la classe sélectionnée
$selected_class = isset($_GET['class_id']) ? intval($_GET['class_id']) : null;

// Si une classe est sélectionnée, récupérer les étudiants de cette classe
$students = [];
if ($selected_class) {
    $stmt = $pdo->prepare("
        SELECT u.id_user, u.prenom, u.nom
        FROM user u
        JOIN student_classes sc ON u.id_user = sc.student_id
        WHERE sc.class_id = ? AND u.role = 0
        ORDER BY u.nom, u.prenom
    ");
    $stmt->execute([$selected_class]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les notes des étudiants pour ce module
    if (!empty($students)) {
        $student_ids = array_column($students, 'id_user');
        $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
        
        $notes_query = "
            SELECT n.id_note, n.id_etudiant, n.note, n.nom_devoir, n.coefficient
            FROM notes n
            WHERE n.id_module = ? AND n.id_etudiant IN ($placeholders)
            ORDER BY n.id_etudiant, n.nom_devoir
        ";
        
        $params = array_merge([$module_id], $student_ids);
        $stmt = $pdo->prepare($notes_query);
        $stmt->execute($params);
        
        $all_notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organiser les notes par étudiant
        $student_notes = [];
        foreach ($all_notes as $note) {
            $student_id = $note['id_etudiant'];
            if (!isset($student_notes[$student_id])) {
                $student_notes[$student_id] = [];
            }
            $student_notes[$student_id][] = $note;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier les notes - <?= htmlspecialchars($module['code_module']) ?></title>
    <?php include("link.php"); ?>
</head>
<body>
    <?php include("navbar.php"); ?>
    
    <div class="container mt-5">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Modifier les notes</h1>
            <a href="notes.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour aux notes
            </a>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($module['code_module'] . ' - ' . $module['nom_module']) ?></h5>
                
                <!-- Sélection de la classe -->
                <form method="get" class="mt-3">
                    <input type="hidden" name="module_id" value="<?= $module_id ?>">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label for="class_id" class="form-label">Sélectionner une classe:</label>
                            <select name="class_id" id="class_id" class="form-select" required>
                                <option value="">-- Choisir une classe --</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['class_id'] ?>" <?= $selected_class == $class['class_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($class['class_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">Afficher les étudiants</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($selected_class && !empty($students)): ?>
            <form method="post" action="" id="notesForm">
                <div class="row">
                    <?php foreach ($students as $student): ?>
                        <div class="col-lg-6">
                            <div class="card student-card shadow-sm">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?= htmlspecialchars($student['prenom'] . ' ' . $student['nom']) ?></h5>
                                    <button type="button" class="btn btn-sm btn-light" onclick="addNoteRow(<?= $student['id_user'] ?>)">
                                        <i class="fas fa-plus"></i> Ajouter une note
                                    </button>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($student_notes[$student['id_user']]) && !empty($student_notes[$student['id_user']])): ?>
                                        <div class="note-container" id="notes-<?= $student['id_user'] ?>">
                                            <?php foreach ($student_notes[$student['id_user']] as $note): ?>
                                                <div class="note-row d-flex align-items-center">
                                                    <div class="col-5">
                                                        <input type="text" class="form-control form-control-sm" name="notes[<?= $note['id_note'] ?>][nom_devoir]" value="<?= htmlspecialchars($note['nom_devoir']) ?>" readonly>
                                                    </div>
                                                    <div class="col-3">
                                                        <input type="number" class="form-control form-control-sm" name="notes[<?= $note['id_note'] ?>][valeur]" value="<?= $note['note'] ?>" min="0" max="20" step="0.01" required>
                                                    </div>
                                                    <div class="col-2">
                                                        <input type="number" class="form-control form-control-sm" name="notes[<?= $note['id_note'] ?>][coefficient]" value="<?= $note['coefficient'] ?>" min="1" max="10" required>
                                                    </div>
                                                    <div class="col-2 text-end">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="delete_notes[<?= $note['id_note'] ?>]" id="delete_<?= $note['id_note'] ?>">
                                                            <label class="form-check-label" for="delete_<?= $note['id_note'] ?>">
                                                                <i class="fas fa-trash text-danger"></i>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>Aucune note pour cet étudiant.
                                        </div>
                                        <div class="note-container" id="notes-<?= $student['id_user'] ?>">
                                            <!-- Les nouvelles notes seront ajoutées ici -->
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="d-flex justify-content-end mt-4 mb-5">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
            
            <!-- Template pour ajouter une nouvelle note (caché) -->
            <div id="new-note-template" style="display:none;">
                <div class="note-row d-flex align-items-center">
                    <div class="col-5">
                        <select class="form-control form-control-sm" name="new_notes[STUDENT_ID][TEMPLATE_INDEX][nom_devoir]" required>
                            <option value="">-- Type d'évaluation --</option>
                            <option value="Contrôle continu">Contrôle continu</option>
                            <option value="TP">Travaux pratiques</option>
                            <option value="Projet">Projet</option>
                            <option value="Examen final">Examen final</option>
                            <option value="Quiz">Quiz</option>
                            <option value="Présentation">Présentation</option>
                            <option value="Participation">Participation</option>
                        </select>
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control form-control-sm" name="new_notes[STUDENT_ID][TEMPLATE_INDEX][valeur]" value="10" min="0" max="20" step="0.01" required placeholder="Note">
                    </div>
                    <div class="col-2">
                        <input type="number" class="form-control form-control-sm" name="new_notes[STUDENT_ID][TEMPLATE_INDEX][coefficient]" value="1" min="1" max="10" required placeholder="Coef.">
                    </div>
                    <div class="col-2 text-end">
                        <button type="button" class="btn btn-sm btn-danger remove-note-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php elseif ($selected_class): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-circle me-2"></i>Aucun étudiant trouvé dans cette classe.
            </div>
        <?php endif; ?>
    </div>
    
    <script>
    // Compteurs pour les nouveaux éléments
    const noteCounters = {};
    
    // Fonction pour ajouter une nouvelle ligne de note
    function addNoteRow(studentId) {
        // Initialiser le compteur s'il n'existe pas
        if (!noteCounters[studentId]) {
            noteCounters[studentId] = 0;
        }
        
        // Obtenir le template et le modifier
        const template = document.getElementById('new-note-template').innerHTML;
        const newRow = template
            .replace(/STUDENT_ID/g, studentId)
            .replace(/TEMPLATE_INDEX/g, noteCounters[studentId]);
        
        // Ajouter au DOM
        const container = document.getElementById(`notes-${studentId}`);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newRow;
        const newElement = tempDiv.firstElementChild;
        container.appendChild(newElement);
        
        // Ajouter l'event listener pour le bouton de suppression
        newElement.querySelector('.remove-note-btn').addEventListener('click', function() {
            this.closest('.note-row').remove();
        });
        
        // Incrémenter le compteur
        noteCounters[studentId]++;
    }
    
    // Validation du formulaire avant soumission
    document.getElementById('notesForm').addEventListener('submit', function(event) {
        // Vérifier si des notes sont cochées pour suppression
        const deleteCheckboxes = document.querySelectorAll('input[name^="delete_notes"]');
        let hasCheckedBoxes = false;
        
        deleteCheckboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                hasCheckedBoxes = true;
            }
        });
        
        if (hasCheckedBoxes) {
            // Demander confirmation avant de supprimer les notes
            if (!confirm('Êtes-vous sûr de vouloir supprimer les notes sélectionnées?')) {
                event.preventDefault();
                return false;
            }
        }
    });
    </script>
</body>
</html>