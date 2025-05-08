<?php
    session_start();
    require("db.php");

    //  Vérification si l'utilisateur est connecté et est un admin
     if (!isset($_SESSION["id"]) || $_SESSION["access"] != 0) {
         // Rediriger vers la page de connexion ou une page d'erreur
         header("Location: index.php");
    exit(); }

    // Déterminer le type de formulaire à afficher (par défaut: élève)
    $form_type = isset($_GET['type']) ? $_GET['type'] : 'eleve';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $name = trim($_POST["name"]);
    $email = trim($_POST["mail"]);
    $phone = trim($_POST["phone"]);
    $sexe = $_POST["sexe"];
    $password = $_POST["password"];
    $confirm_password = $_POST["password_conf"];
    
    // Récupérer le niveau d'accès selon le type de formulaire
    $access_level = 2; // Par défaut: élève
    
    if (isset($_POST["form_type"])) {
        switch ($_POST["form_type"]) {
            case 'admin':
                $access_level = 0; // Administrateur
                break;
            case 'prof':
                $access_level = 1; // Professeur
                break;
            default:
                $access_level = 2; // Élève
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
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    //check id user already exists
    $sql = "SELECT * FROM users WHERE mail = :mail";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['mail' => $email]);
    $user = $stmt->fetch();

    if($user){
        die("Cet utilisateur existe déjà !");
    }

    try {
        // Insérer les données dans la base de données
        $sql = "INSERT INTO users (name, username, mail, phone, mdp, sexe, access) VALUES (:name, :username, :mail, :phone, :mdp, :sexe, :access)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'mail' => $email,
            'name' => $name,
            'username' => $username,
            'phone' => $phone,
            'mdp' => $hashed_password,
            'sexe' => $sexe,
            "access" => $access_level
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
                        <div id="admin-tab" class="form-option <?php echo ($form_type == 'admin') ? 'active bg-danger text-white' : 'bg-light'; ?>" onclick="switchForm('admin')">
                            Administrateur
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div id="prof-tab" class="form-option <?php echo ($form_type == 'prof') ? 'active bg-success text-white' : 'bg-light'; ?>" onclick="switchForm('prof')">
                            Professeur
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div id="eleve-tab" class="form-option <?php echo ($form_type == 'eleve') ? 'active bg-primary text-white' : 'bg-light'; ?>" onclick="switchForm('eleve')">
                            Élève
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ajouter ceci avant le formulaire dans register.php -->
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
                    
                    <div class="mb-3">
                        <label for="admin_username" class="form-label">Nom d'utilisateur</label>
                        <input required type="text" class="form-control" id="admin_username" name="username">
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_name" class="form-label">Nom complet</label>
                        <input required type="text" class="form-control" id="admin_name" name="name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_password" class="form-label">Mot de passe</label>
                        <input required type="password" class="form-control" id="admin_password" name="password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_password_conf" class="form-label">Confirmer le mot de passe</label>
                        <input required type="password" class="form-control" id="admin_password_conf" name="password_conf">
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_mail" class="form-label">Email</label>
                        <input required type="email" class="form-control" id="admin_mail" name="mail">
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_phone" class="form-label">Téléphone</label>
                        <input required type="text" class="form-control" id="admin_phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_sexe" class="form-label">Sexe</label>
                        <select name="sexe" id="admin_sexe" class="form-control">
                            <option value="homme">Homme</option>
                            <option value="femme">Femme</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-danger w-100">Créer l'administrateur</button>
                </form>
            </div>
            
            <!-- Formulaire pour Professeur -->
            <div id="prof-form" class="form-container <?php echo ($form_type == 'prof') ? 'active' : ''; ?>">
                <form action="" method="post" class="p-3 rounded">
                    <h3 class="text-center text-success mb-3">Créer un professeur</h3>
                    <input type="hidden" name="form_type" value="prof">
                    
                    <div class="mb-3">
                        <label for="prof_username" class="form-label">Nom d'utilisateur</label>
                        <input required type="text" class="form-control" id="prof_username" name="username">
                    </div>
                    
                    <div class="mb-3">
                        <label for="prof_name" class="form-label">Nom complet</label>
                        <input required type="text" class="form-control" id="prof_name" name="name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="prof_password" class="form-label">Mot de passe</label>
                        <input required type="password" class="form-control" id="prof_password" name="password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="prof_password_conf" class="form-label">Confirmer le mot de passe</label>
                        <input required type="password" class="form-control" id="prof_password_conf" name="password_conf">
                    </div>
                    
                    <div class="mb-3">
                        <label for="prof_mail" class="form-label">Email</label>
                        <input required type="email" class="form-control" id="prof_mail" name="mail">
                    </div>
                    
                    <div class="mb-3">
                        <label for="prof_phone" class="form-label">Téléphone</label>
                        <input required type="text" class="form-control" id="prof_phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="prof_matiere" class="form-label">Matière enseignée</label>
                        <input type="text" class="form-control" id="prof_matiere" name="matiere">
                    </div>
                    
                    <div class="mb-3">
                        <label for="prof_sexe" class="form-label">Sexe</label>
                        <select name="sexe" id="prof_sexe" class="form-control">
                            <option value="homme">Homme</option>
                            <option value="femme">Femme</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">Créer le professeur</button>
                </form>
            </div>
            
            <!-- Formulaire pour Élève -->
            <div id="eleve-form" class="form-container <?php echo ($form_type == 'eleve') ? 'active' : ''; ?>">
                <form action="process_csv.php" method="post" class="p-3 rounded" enctype="multipart/form-data">
                    <h3 class="text-center text-primary mb-3">Importer une liste d'élèves</h3>
                    <input type="hidden" name="form_type" value="eleve">
                    
                    <div class="mb-3">
                        <label for="eleve_classe" class="form-label">Nom de la classe</label>
                        <input required type="text" class="form-control" id="eleve_classe" name="classe" placeholder="Ex: Terminale S2">
                    </div>
                    
                    <div class="mb-3">
                        <label for="csv_import" class="form-label">Fichier CSV des élèves</label>
                        <input required type="file" class="form-control" id="csv_import" name="csv_file" accept=".csv">
                        <div class="form-text text-muted mt-2">
                            <p>Format attendu du CSV :</p>
                            <ul>
                                <li><strong>Colonnes</strong>: nom_utilisateur,nom_complet,email,telephone,sexe,mot_de_passe</li>
                                <li><strong>Exemple</strong>: jdupont,Jean Dupont,jean.dupont@email.com,0123456789,homme,motdepasse123</li>
                            </ul>
                            <p>La classe sera automatiquement ajoutée à tous les élèves du fichier.</p>
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
            } else if (formType === 'prof') {
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