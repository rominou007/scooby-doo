<?php
session_start();
require("db.php");

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != 'admin') {
    // Rediriger vers la page de connexion ou une page d'erreur
    header("Location: index.php");
    exit();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["class"]) && isset($_FILES["csv_file"])) {
    $class = trim($_POST["class"]);
    
    // Vérifier le fichier CSV
    if ($_FILES["csv_file"]["error"] == 0) {
        $csvMimeTypes = ['text/csv', 'application/csv', 'text/plain', 'application/vnd.ms-excel'];
        $fileType = $_FILES["csv_file"]["type"];
        $fileExt = pathinfo($_FILES["csv_file"]["name"], PATHINFO_EXTENSION);
        
        if (in_array($fileType, $csvMimeTypes) || strtolower($fileExt) === 'csv') {
            $csvFile = $_FILES["csv_file"]["tmp_name"];
            $handle = fopen($csvFile, "r");
            
            // Compter les élèves ajoutés et les erreurs
            $successCount = 0;
            $errorCount = 0;
            $errorMessages = [];
            
            // Lire le fichier CSV ligne par ligne
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Vérifier que nous avons les colonnes nécessaires
                if (count($data) >= 7) {
                    $username = trim($data[0]);
                    $first_name = trim($data[1]);
                    $last_name = trim($data[2]);
                    $email = trim($data[3]);
                    $phone_number = trim($data[4]);
                    $address = trim($data[5]);
                    $password = trim($data[6]);
                    
                    // Valider les données
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errorCount++;
                        $errorMessages[] = "Email invalide pour {$first_name} {$last_name}: {$email}";
                        continue;
                    }
                    
                    if (strlen($password) < 8) {
                        $errorCount++;
                        $errorMessages[] = "Mot de passe trop court pour {$first_name} {$last_name} (minimum 8 caractères)";
                        continue;
                    }
                    
                    try {
                        // Vérifier si l'utilisateur existe déjà
                        $checkSql = "SELECT * FROM users WHERE email = :email OR username = :username";
                        $checkStmt = $pdo->prepare($checkSql);
                        $checkStmt->execute(['email' => $email, 'username' => $username]);
                        $user = $checkStmt->fetch();
                        
                        if ($user) {
                            $errorCount++;
                            $errorMessages[] = "L'utilisateur {$first_name} {$last_name} ({$email}) existe déjà";
                            continue;
                        }
                        
                        // Hacher le mot de passe
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insérer l'élève avec les informations supplémentaires sur la classe
                        $insertSql = "INSERT INTO users (username, password_hash, email, first_name, last_name, role, phone_number, address) 
                                      VALUES (:username, :password_hash, :email, :first_name, :last_name, :role, :phone_number, :address)";
                        $insertStmt = $pdo->prepare($insertSql);
                        $insertStmt->execute([
                            'username' => $username,
                            'password_hash' => $password_hash,
                            'email' => $email,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'role' => 'student',
                            'phone_number' => $phone_number,
                            'address' => $address . "\nClasse: {$class}" // Ajouter la classe dans l'adresse
                        ]);
                        $successCount++;
                    } catch (PDOException $e) {
                        $errorCount++;
                        $errorMessages[] = "Erreur pour {$first_name} {$last_name}: " . $e->getMessage();
                    }
                } else {
                    $errorCount++;
                    $errorMessages[] = "Ligne CSV invalide (colonnes insuffisantes): " . implode(",", $data);
                }
            }
            fclose($handle);
            
            // Créer un message de statut
            $status = $errorCount > 0 ? 'error' : 'success';
            $message = "Import terminé: {$successCount} élèves ajoutés";
            if ($errorCount > 0) {
                $message .= ", {$errorCount} erreurs.";
            }
            
            // Rediriger avec un message
            header("Location: register.php?type=student&status={$status}&message=" . urlencode($message));
            exit();
        } else {
            // Format de fichier non valide
            header("Location: register.php?type=student&status=error&message=" . urlencode("Format de fichier invalide. Veuillez télécharger un fichier CSV."));
            exit();
        }
    } else {
        // Erreur de téléchargement
        header("Location: register.php?type=student&status=error&message=" . urlencode("Erreur lors du téléchargement du fichier. Code: " . $_FILES["csv_file"]["error"]));
        exit();
    }
} else {
    // Redirection si le formulaire n'a pas été correctement soumis
    header("Location: register.php?type=student");
    exit();
}
?>