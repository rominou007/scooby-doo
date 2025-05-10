<?php
    session_start();
    require("db.php");

    // //  Vérification si l'utilisateur est connecté et est un admin
    //  if (!isset($_SESSION["id"]) || $_SESSION["access"] != 0) {
    //      // Rediriger vers la page de connexion ou une page d'erreur
    //      header("Location: index.php");
    // exit(); }

    // Déterminer le type de formulaire à afficher (par défaut: élève)
    $form_type = isset($_GET['type']) ? $_GET['type'] : 'student';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $form_type != 'student') {
    $username = trim($_POST["username"]);
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $phone_number = trim($_POST["phone_number"]);
    $address = trim($_POST["address"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["password_conf"];
    
    // Récupérer le rôle selon le type de formulaire
    $role = 'student'; // Par défaut
    
    if (isset($_POST["form_type"])) {
        switch ($_POST["form_type"]) {
            case 'admin':
                $role = 'admin';
                break;
            case 'professor':
                $role = 'professor';
                break;
            default:
                $role = 'student';
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
    $sql = "SELECT * FROM users WHERE email = :email OR username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email, 'username' => $username]);
    $user = $stmt->fetch();

    if($user){
        die("Cet utilisateur existe déjà !");
    }

    try {
        // Insérer les données dans la base de données
        $sql = "INSERT INTO users (username, password_hash, email, first_name, last_name, role, phone_number, address) 
                VALUES (:username, :password_hash, :email, :first_name, :last_name, :role, :phone_number, :address)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'username' => $username,
            'password_hash' => $password_hash,
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => $role,
            'phone_number' => $phone_number,
            'address' => $address
        ]);
        header("location: index.php");
    } catch (PDOException $e) {
        die("Erreur lors de l'enregistrement : " . $e->getMessage());
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
    <style>
        .form-option {
            cursor: pointer;
            padding: 10px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }
        .form-option.active {
            border-bottom: 3px solid #007bff;
        }
        .form-container {
            display: none;
        }
        .form-container.active {
            display: block;
        }
    </style>
</head>

<?php include("navbar.php"); ?>
<body class="bg-secondary">

    <div class="container mt-4">
        <div class="row bg-light p-3 rounded shadow">
            <h1 class="text-center text-danger mb-4">Gestion des utilisateurs</h1>
            
            <div class="col-md-12 mb-4">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div id="admin-tab" class="form-option <?php echo ($form_type == 'admin') ? 'active bg-danger text-black' : 'bg-light'; ?>" onclick="switchForm('admin')">
                            Administrateur
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div id="professor-tab" class="form-option <?php echo ($form_type == 'professor') ? 'active bg-success text-black' : 'bg-light'; ?>" onclick="switchForm('professor')">
                            Professeur
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div id="student-tab" class="form-option <?php echo ($form_type == 'student') ? 'active bg-primary text-black' : 'bg-light'; ?>" onclick="switchForm('student')">
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
                            <label for="admin_username" class="form-label">Nom d'utilisateur</label>
                            <input required type="text" class="form-control" id="admin_username" name="username">
                        </div>
                        
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
                            <label for="prof_username" class="form-label">Nom d'utilisateur</label>
                            <input required type="text" class="form-control" id="prof_username" name="username">
                        </div>
                        
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
                <form action="process_csv.php" method="post" class="p-3 rounded" enctype="multipart/form-data">
                    <h3 class="text-center text-primary mb-3">Importer une liste d'élèves</h3>
                    <input type="hidden" name="form_type" value="student">
                    
                    <div class="mb-3">
                        <label for="student_class" class="form-label">Nom de la classe</label>
                        <input required type="text" class="form-control" id="student_class" name="class" placeholder="Ex: Terminale S2">
                    </div>
                    
                    <div class="mb-3">
                        <label for="csv_import" class="form-label">Fichier CSV des élèves</label>
                        <input required type="file" class="form-control" id="csv_import" name="csv_file" accept=".csv">
                        <div class="form-text text-muted mt-2">
                            <p>Format attendu du CSV :</p>
                            <ul>
                                <li><strong>Colonnes</strong>: username,first_name,last_name,email,phone_number,address,password</li>
                                <li><strong>Exemple</strong>: jdupont,Jean,Dupont,jean.dupont@email.com,0123456789,"123 Rue Example, 75000 Paris",motdepasse123</li>
                            </ul>
                            <p>La classe sera automatiquement ajoutée comme information supplémentaire.</p>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Importer les élèves</button>
                </form>
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
                tab.classList.add('bg-light');
                tab.classList.remove('text-white');
            });
            
            // Afficher le formulaire sélectionné
            document.getElementById(formType + '-form').classList.add('active');
            
            // Mettre à jour l'onglet actif
            const activeTab = document.getElementById(formType + '-tab');
            activeTab.classList.add('active');
            
            // Appliquer le style approprié à l'onglet actif
            if (formType === 'admin') {
                activeTab.classList.add('bg-danger', 'text-white');
            } else if (formType === 'professor') {
                activeTab.classList.add('bg-success', 'text-white');
            } else {
                activeTab.classList.add('bg-primary', 'text-white');
            }
            
            // Mettre à jour l'URL pour conserver le type de formulaire lors des rafraîchissements
            window.history.replaceState({}, '', 'register.php?type=' + formType);
        }
    </script>
</body>
</html>