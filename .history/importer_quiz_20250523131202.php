<?php
session_start();
require('db.php');
var_dump($_POST);

// Vérification des permissions
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], [1, 2])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

// Validation
$id_module = $_POST['module_id'] ?? null;
if (!isset($id_module)|| !isset($_FILES['quiz_file'])) {
    die("Données manquantes");
}
$file = $_FILES['quiz_file'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

try {
    // Traitement selon le format
    switch ($extension) {
        case 'json':
            $data = json_decode(file_get_contents($file['tmp_name']), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Fichier JSON invalide");
            }
            break;
            
        case 'csv':
            $data = [];
            $handle = fopen($file['tmp_name'], 'r');
            $first = true;
            while (($row = fgetcsv($handle)) !== false) {
                // Sauter l'entête si présente
                if ($first) { $first = false; continue; }
                // Vérifier qu'il y a assez de colonnes pour au moins une réponse
                if (count($row) < 3) continue;
                $answers = [];
                // Boucle pour chaque réponse (ici, jusqu'à 3 réponses)
                for ($i = 1; $i < count($row) - 1; $i += 2) {
                    if (!isset($row[$i]) || !isset($row[$i+1])) continue;
                    $answers[] = [
                        'text' => $row[$i],
                        'correct' => $row[$i+1] === '1'
                    ];
                }
                $data[] = [
                    'text' => $row[0],
                    'type' => 'qcm',
                    'answers' => $answers
                ];
            }
            fclose($handle);
            break;
            
        default:
            throw new Exception("Format non supporté");
    }

    // Enregistrement
    $stmt = $pdo->prepare("INSERT INTO quiz (id_module, id_prof, titre, questions, date_creation) 
                          VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([
        $id_module,
        $_SESSION['user_id'],
        $_POST['titre'], // Utilise le titre saisi dans le formulaire
        json_encode($data, JSON_UNESCAPED_UNICODE)
    ]);
    
    header("Location: modules.php?success=quiz_imported");
    exit;
} catch (Exception $e) {
    die("Erreur d'import : " . $e->getMessage());
}