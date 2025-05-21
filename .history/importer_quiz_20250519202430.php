<?php
session_start();
require('db.php');

// Vérification des permissions
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], [1, 2])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

// Validation
$id_module = filter_input(INPUT_POST, 'id_module', FILTER_VALIDATE_INT);
if (!$id_module || empty($_FILES['quiz_file']['name'])) {
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
            while (($row = fgetcsv($handle)) !== false) {
                // Adaptez selon votre format CSV
                $data[] = [
                    'text' => $row[0],
                    'type' => 'qcm',
                    'answers' => [
                        ['text' => $row[1], 'correct' => $row[2] === '1'],
                        // ... autres réponses
                    ]
                ];
            }
            fclose($handle);
            break;
            
        default:
            throw new Exception("Format non supporté");
    }

    // Enregistrement
    $stmt = $pdo->prepare("INSERT INTO quiz (id_module, id_prof, titre, questions) 
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $id_module,
        $_SESSION['user_id'],
        "Quiz importé - " . date('d/m/Y'),
        json_encode($data, JSON_UNESCAPED_UNICODE)
    ]);
    
    header("Location: modules.php?success=quiz_imported");
    exit;
} catch (Exception $e) {
    die("Erreur d'import : " . $e->getMessage());
}