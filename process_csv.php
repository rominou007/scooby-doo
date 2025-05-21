<?php
session_start();
require("db.php");

// Vérifier si l'utilisateur est connecté et est un admin
// if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != 2) { // 2 pour admin
//     // Rediriger vers la page de connexion ou une page d'erreur
//     header("Location: login.php");
//     exit();
// }

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["class"]) && isset($_FILES["csv_file"])) {
    $class_name = trim($_POST["class"]);
    $enrollment_year = $_POST["enrollment_year"];
    $class_description = isset($_POST["class_description"]) ? trim($_POST["class_description"]) : '';
    
    try {
        // 1. Créer la classe dans la table 'classes'
        $classSql = "INSERT INTO classes (class_name, description, annee_scolaire) 
                     VALUES (:class_name, :description, :enrollment_year)";
        $classStmt = $pdo->prepare($classSql);
        $classStmt->execute([
            'class_name' => $class_name,
            'description' => $class_description,
            'enrollment_year' => $enrollment_year
        ]);
        
        // Récupérer l'ID de la classe insérée
        $class_id = $pdo->lastInsertId();
        
        // Continuer avec le traitement du fichier CSV
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
                    if (count($data) >= 6) {
                        $first_name = trim($data[0]);
                        $last_name = trim($data[1]);
                        $email = trim($data[2]);
                        $phone_number = trim($data[3]);
                        $address = trim($data[4]);
                        $password = trim($data[5]);
                        
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
                            $checkSql = "SELECT * FROM user WHERE email = :email";
                            $checkStmt = $pdo->prepare($checkSql);
                            $checkStmt->execute(['email' => $email]);
                            $user = $checkStmt->fetch();
                            
                            if ($user) {
                                $errorCount++;
                                $errorMessages[] = "L'utilisateur {$first_name} {$last_name} ({$email}) existe déjà";
                                continue;
                            }
                            
                            // Démarrer une transaction pour assurer la cohérence des données
                            $pdo->beginTransaction();
                            
                            // Hacher le mot de passe
                            $password_hash = password_hash($password, PASSWORD_DEFAULT);
                            
                            // Insérer l'élève dans la table users
                            $insertSql = "INSERT INTO user (mdp, email, prenom, nom, role, telephone, adresse) 
                                          VALUES (:password_hash, :email, :first_name, :last_name, :role, :phone_number, :address)";
                            $insertStmt = $pdo->prepare($insertSql);
                            $insertStmt->execute([
                                'password_hash' => $password_hash,
                                'email' => $email,
                                'first_name' => $first_name,
                                'last_name' => $last_name,
                                'role' => 0, // 0 pour étudiant selon le fichier SQL
                                'phone_number' => $phone_number,
                                'address' => $address
                            ]);
                            
                            // Récupérer l'ID de l'étudiant inséré
                            $student_id = $pdo->lastInsertId();
                            
                            // Associer l'étudiant à la classe dans la table student_classes
                            $assignSql = "INSERT INTO student_classes (student_id, class_id) VALUES (:student_id, :class_id)";
                            $assignStmt = $pdo->prepare($assignSql);
                            $assignStmt->execute([
                                'student_id' => $student_id,
                                'class_id' => $class_id
                            ]);
                            
                            // Valider la transaction
                            $pdo->commit();
                            $successCount++;
                        } catch (PDOException $e) {
                            // Annuler la transaction en cas d'erreur
                            $pdo->rollBack();
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
                $message = "Import terminé: {$successCount} élèves ajoutés à la classe {$class_name}";
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
    } catch (PDOException $e) {
        // Erreur lors de la création de la classe
        header("Location: register.php?type=student&status=error&message=" . urlencode("Erreur lors de la création de la classe: " . $e->getMessage()));
        exit();
    }
} else {
    // Redirection si le formulaire n'a pas été correctement soumis
    header("Location: register.php?type=student");
    exit();
}
?>