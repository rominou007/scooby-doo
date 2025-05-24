<?php
session_start();
require("db.php");

// Vérification si l'utilisateur est connecté et est un admin ou prof
if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["role"], [1, 2])) {
    // Rediriger vers la page de connexion
    header("Location: login.php");
    exit();
}


    // Déterminer le type de formulaire à afficher (par défaut: élève)
    $form_type = isset($_GET['type']) ? $_GET['type'] : 'student';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Traitement pour un élève unique
    if (isset($_POST["form_type"]) && $_POST["form_type"] === "student" && !isset($_FILES["csv_file"])) {
        $first_name = trim($_POST["first_name"]);
        $last_name = trim($_POST["last_name"]);
        $email = trim($_POST["email"]);
        $phone_number = trim($_POST["phone_number"]);
        $address = trim($_POST["address"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["password_conf"];
        $class_id = $_POST["class_id"];
        
        if (strlen($password) < 8) {
            die("Le mot de passe doit contenir au moins 8 caractères");
        }

        // Vérifier si les mots de passe correspondent
        if ($password !== $confirm_password) {
            die("Les mots de passe ne correspondent pas !");
        }

        // Hacher le mot de passe
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Vérifier si l'utilisateur existe déjà
        $sql = "SELECT * FROM user WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            die("Cet utilisateur existe déjà !");
        }
        
        try {
            // Démarrer une transaction
            $pdo->beginTransaction();
            
            // Créer une nouvelle classe si nécessaire
            if ($class_id === 'new' && isset($_POST['new_class_name'])) {
                $new_class_name = trim($_POST['new_class_name']);
                $new_class_year = $_POST['new_class_year'];
                $new_class_desc = trim($_POST['new_class_desc'] ?? '');
                
                $createClassSql = "INSERT INTO classes (class_name, annee_scolaire, description) 
                                  VALUES (:class_name, :annee_scolaire, :description)";
                $createClassStmt = $pdo->prepare($createClassSql);
                $createClassStmt->execute([
                    'class_name' => $new_class_name,
                    'annee_scolaire' => $new_class_year,
                    'description' => $new_class_desc
                ]);
                
                $class_id = $pdo->lastInsertId();
            }
            
            // Insérer l'élève avec le rôle 0 (étudiant)
            $sql = "INSERT INTO user (mdp, email, prenom, nom, role, telephone, adresse) 
                    VALUES (:password_hash, :email, :first_name, :last_name, :role, :phone_number, :address)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'password_hash' => $password_hash,
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'role' => 0, // INT 0 pour étudiant
                'phone_number' => $phone_number,
                'address' => $address
            ]);
            
            $student_id = $pdo->lastInsertId();
            
            // Si une classe a été sélectionnée, associer l'élève à cette classe
            if ($class_id && $class_id !== 'new') {
                $assignSql = "INSERT INTO student_classes (student_id, class_id) VALUES (:student_id, :class_id)";
                $assignStmt = $pdo->prepare($assignSql);
                $assignStmt->execute([
                    'student_id' => $student_id,
                    'class_id' => $class_id
                ]);
            }
            
            // Valider la transaction
            $pdo->commit();
            
            // Redirection
            header("location: register.php?type=student&status=success&message=".urlencode("L'élève a été créé avec succès !"));
            exit();
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $pdo->rollBack();
            die("Erreur lors de l'enregistrement : " . $e->getMessage());
        }
    }
    
    // Traitement pour les autres types d'utilisateurs (code existant)
    elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $form_type != 'student') {
        $first_name = trim($_POST["first_name"]);
        $last_name = trim($_POST["last_name"]);
        $email = trim($_POST["email"]);
        $phone_number = trim($_POST["phone_number"]);
        $address = trim($_POST["address"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["password_conf"];
        
        // Récupérer le rôle selon le type de formulaire
        $role = 0; // Par défaut: étudiant (0)
        
        if (isset($_POST["form_type"])) {
            switch ($_POST["form_type"]) {
                case 'admin':
                    $role = 2; // INT 2 pour admin
                    break;
                case 'professor':
                    $role = 1; // INT 1 pour professeur
                    break;
                default:
                    $role = 0; // INT 0 pour étudiant
            }
        }

        if(strlen($password) < 8){
            die("Le mot de passe doit contenir au moins 8 caractères");
        }

        // Vérifier si les mots de passe correspondent
        if ($password !== $confirm_password) {
            die("Les mots de passe ne correspondent pas !");
        }

        // Hacher le mot de passe pour plus de sécurité
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Vérifier si l'utilisateur existe déjà
        $sql = "SELECT * FROM user WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if($user){
            die("Cet utilisateur existe déjà !");
        }

        try {
            // Insérer les données dans la base de données avec le rôle INT
            $sql = "INSERT INTO user (mdp, email, prenom, nom, role, telephone, adresse) 
                    VALUES (:password_hash, :email, :first_name, :last_name, :role, :phone_number, :address)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'password_hash' => $password_hash,
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'role' => $role,
                'phone_number' => $phone_number,
                'address' => $address
            ]);
            header("location: login.php");
        } catch (PDOException $e) {
            die("Erreur lors de l'enregistrement : " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <?php include("link.php"); ?>
</head>

<?php include("navbar.php"); ?>
<body class="bg-secondary">

    <div class="container mt-4">
        <div class="row bg-light p-3 rounded shadow">
            <h1 class="text-center text-danger mb-4">Gestion des utilisateurs</h1>
            
            <div class="col-md-12 mb-4">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div id="admin-tab" class="form-option <?php echo ($form_type == 'admin') ? 'active bg-danger text-dark' : 'bg-light'; ?>" onclick="switchForm('admin')">
                            Administrateur
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div id="professor-tab" class="form-option <?php echo ($form_type == 'professor') ? 'active bg-success text-dark' : 'bg-light'; ?>" onclick="switchForm('professor')">
                            Professeur
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div id="student-tab" class="form-option <?php echo ($form_type == 'student') ? 'active bg-primary text-dark' : 'bg-light'; ?>" onclick="switchForm('student')">
                            Élève
                        </div>
                    </div>
                </div>
            </div>

            <!-- Affichage des messages de status -->
            <?php if (isset($_GET['status'])): ?>
                <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?> mb-4">
                    <?php echo htmlspecialchars($_GET['message'] ?? ''); ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire pour Administrateur -->
            <div id="admin-form" class="form-container <?php echo ($form_type == 'admin') ? 'active' : ''; ?>">
                <form action="" method="post" class="p-3 rounded">
                    <h3 class="text-center text-danger mb-3">Créer un administrateur</h3>
                    <input type="hidden" name="form_type" value="admin">
                    
                    <div class="row"> 
                        <div class="col-md-6 mb-3">
                            <label for="admin_email" class="form-label">Email</label>
                            <input required type="email" class="form-control" id="admin_email" name="email">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="admin_first_name" class="form-label">Prénom</label>
                            <input required type="text" class="form-control" id="admin_first_name" name="first_name">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="admin_last_name" class="form-label">Nom</label>
                            <input required type="text" class="form-control" id="admin_last_name" name="last_name">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="admin_password" class="form-label">Mot de passe</label>
                            <input required type="password" class="form-control" id="admin_password" name="password">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="admin_password_conf" class="form-label">Confirmer le mot de passe</label>
                            <input required type="password" class="form-control" id="admin_password_conf" name="password_conf">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_phone_number" class="form-label">Téléphone</label>
                        <input type="text" class="form-control" id="admin_phone_number" name="phone_number">
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_address" class="form-label">Adresse</label>
                        <textarea class="form-control" id="admin_address" name="address" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-danger w-100">Créer l'administrateur</button>
                </form>
            </div>
            
            <!-- Formulaire pour Professeur -->
            <div id="professor-form" class="form-container <?php echo ($form_type == 'professor') ? 'active' : ''; ?>">
                <form action="" method="post" class="p-3 rounded">
                    <h3 class="text-center text-success mb-3">Créer un professeur</h3>
                    <input type="hidden" name="form_type" value="professor">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="prof_email" class="form-label">Email</label>
                            <input required type="email" class="form-control" id="prof_email" name="email">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="prof_first_name" class="form-label">Prénom</label>
                            <input required type="text" class="form-control" id="prof_first_name" name="first_name">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="prof_last_name" class="form-label">Nom</label>
                            <input required type="text" class="form-control" id="prof_last_name" name="last_name">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="prof_password" class="form-label">Mot de passe</label>
                            <input required type="password" class="form-control" id="prof_password" name="password">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="prof_password_conf" class="form-label">Confirmer le mot de passe</label>
                            <input required type="password" class="form-control" id="prof_password_conf" name="password_conf">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="prof_phone_number" class="form-label">Téléphone</label>
                        <input type="text" class="form-control" id="prof_phone_number" name="phone_number">
                    </div>
                    
                    <div class="mb-3">
                        <label for="prof_address" class="form-label">Adresse</label>
                        <textarea class="form-control" id="prof_address" name="address" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">Créer le professeur</button>
                </form>
            </div>
            
            <!-- Formulaire pour Élève (import CSV) -->
            <div id="student-form" class="form-container <?php echo ($form_type == 'student') ? 'active' : ''; ?>">
                <div class="mb-4">
                    <ul class="nav nav-tabs" id="studentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="importcsv-tab" data-bs-toggle="tab" data-bs-target="#importcsv" type="button" role="tab" aria-controls="importcsv" aria-selected="true">Importer une classe</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="singlestudent-tab" data-bs-toggle="tab" data-bs-target="#singlestudent" type="button" role="tab" aria-controls="singlestudent" aria-selected="false">Ajouter un élève</button>
                        </li>
                    </ul>
                </div>
                
                <div class="tab-content" id="studentTabsContent">
                    <!-- Import CSV -->
                    <div class="tab-pane fade show active" id="importcsv" role="tabpanel" aria-labelledby="importcsv-tab">
                        <form action="process_csv.php" method="post" class="p-3 rounded" enctype="multipart/form-data">
                            <h3 class="text-center text-primary mb-3">Importer une liste d'élèves</h3>
                            <input type="hidden" name="form_type" value="student">
                            
                            <div class="mb-3">
                                <label for="student_class" class="form-label">Nom de la classe</label>
                                <input required type="text" class="form-control" id="student_class" name="class" placeholder="Ex: Terminale S2">
                            </div>

                            <div class="mb-3">
                                <label for="enrollment_year" class="form-label">Année scolaire</label>
                                <input required type="text" class="form-control" id="enrollment_year" name="enrollment_year" placeholder="Ex: 24/25 ou 2024/2025" ">
                            </div>

                            <div class="mb-3">
                                <label for="class_description" class="form-label">Description de la classe</label>
                                <input type="text" class="form-control" id="class_description" name="class_description">
                            </div>
                            
                            <div class="mb-3">
                                <label for="csv_import" class="form-label">Fichier CSV des élèves</label>
                                <input required type="file" class="form-control" id="csv_import" name="csv_file" accept=".csv">
                                <div class="form-text text-muted mt-2">
                                    <p>Format attendu du CSV :</p>
                                    <ul>
                                        <li><strong>Colonnes</strong>: first_name,last_name,email,phone_number,address,password</li>
                                        <li><strong>Exemple</strong>: Jean,Dupont,jean.dupont@email.com,0123456789,"123 Rue Example, 75000 Paris",motdepasse123</li>
                                    </ul>
                                    <p>La classe sera automatiquement ajoutée comme information supplémentaire.</p>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Importer les élèves</button>
                        </form>
                    </div>
                    
                    <!-- Ajout d'un élève unique -->
                    <div class="tab-pane fade" id="singlestudent" role="tabpanel" aria-labelledby="singlestudent-tab">
                        <form action="" method="post" class="p-3 rounded">
                            <h3 class="text-center text-primary mb-3">Créer un élève</h3>
                            <input type="hidden" name="form_type" value="student">
                            
                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label for="student_email" class="form-label">Email</label>
                                    <input required type="email" class="form-control" id="student_email" name="email">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="student_first_name" class="form-label">Prénom</label>
                                    <input required type="text" class="form-control" id="student_first_name" name="first_name">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="student_last_name" class="form-label">Nom</label>
                                    <input required type="text" class="form-control" id="student_last_name" name="last_name">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="student_password" class="form-label">Mot de passe</label>
                                    <input required type="password" class="form-control" id="student_password" name="password">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="student_password_conf" class="form-label">Confirmer le mot de passe</label>
                                    <input required type="password" class="form-control" id="student_password_conf" name="password_conf">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="student_phone_number" class="form-label">Téléphone</label>
                                    <input type="text" class="form-control" id="student_phone_number" name="phone_number">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="single_student_class" class="form-label">Classe</label>
                                    <select class="form-select" id="single_student_class" name="class_id" required>
                                        <option value="">Sélectionner une classe</option>
                                        <?php
                                        // Récupérer la liste des classes existantes
                                        try {
                                            $classesSql = "SELECT class_id, class_name, annee_scolaire FROM classes ORDER BY class_name";
                                            $classesStmt = $pdo->query($classesSql);
                                            while ($class = $classesStmt->fetch()) {
                                                echo '<option value="' . $class['class_id'] . '">' . htmlspecialchars($class['class_name']) . ' (' . $class['annee_scolaire'] . ')</option>';
                                            }
                                        } catch (PDOException $e) {
                                            // Gérer l'erreur en silence, l'utilisateur pourra toujours créer un élève sans classe
                                        }
                                        ?>
                                        <option value="new">+ Créer une nouvelle classe</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Champs pour nouvelle classe (initialement cachés) -->
                            <div id="new_class_fields" style="display:none;">
                                <div class="row mt-3">
                                    <div class="col-md-4 mb-3">
                                        <label for="new_class_name" class="form-label">Nom de la nouvelle classe</label>
                                        <input type="text" class="form-control" id="new_class_name" name="new_class_name">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="new_class_year" class="form-label">Année scolaire</label>
                                        <input type="text" class="form-control" id="new_class_year" name="new_class_year" placeholder="Ex: 24/25 ou 2024/2025">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="new_class_desc" class="form-label">Description</label>
                                        <input type="text" class="form-control" id="new_class_desc" name="new_class_desc">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="student_address" class="form-label">Adresse</label>
                                <textarea class="form-control" id="student_address" name="address" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Créer l'élève</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchForm(formType) {
            // Masquer tous les formulaires
            document.querySelectorAll('.form-container').forEach(form => {
                form.classList.remove('active');
            });
            
            // Réinitialiser tous les onglets
            document.querySelectorAll('.form-option').forEach(tab => {
                tab.classList.remove('active');
                tab.classList.remove('bg-danger', 'bg-success', 'bg-primary');
                tab.classList.remove('bg-light');
                tab.classList.remove('text-dark');
            });
            
            // Afficher le formulaire sélectionné
            document.getElementById(formType + '-form').classList.add('active');
            
            // Mettre à jour l'onglet actif
            const activeTab = document.getElementById(formType + '-tab');
            activeTab.classList.add('active');
            
            // Appliquer le style approprié à l'onglet actif
            if (formType === 'admin') {
                activeTab.classList.add('bg-danger', 'text-dark');
            } else if (formType === 'professor') {
                activeTab.classList.add('bg-success', 'text-dark');
            } else {
                activeTab.classList.add('bg-primary', 'text-dark');
            }
            
            // Mettre à jour l'URL pour conserver le type de formulaire lors des rafraîchissements
            window.history.replaceState({}, '', 'register.php?type=' + formType);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Gérer l'affichage des champs pour nouvelle classe
            const classSelect = document.getElementById('single_student_class');
            const newClassFields = document.getElementById('new_class_fields');
            
            if (classSelect && newClassFields) {
                classSelect.addEventListener('change', function() {
                    if (this.value === 'new') {
                        newClassFields.style.display = 'block';
                        document.getElementById('new_class_name').setAttribute('required', 'required');
                        document.getElementById('new_class_year').setAttribute('required', 'required');
                    } else {
                        newClassFields.style.display = 'none';
                        document.getElementById('new_class_name').removeAttribute('required');
                        document.getElementById('new_class_year').removeAttribute('required');
                    }
                });
            }
        });
    </script>
</body>
</html>