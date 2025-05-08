<?php
session_start();
require("db.php");

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION["id"]) || $_SESSION["access"] != 0) {
    // Rediriger vers la page de connexion ou une page d'erreur
    header("Location: index.php");
    exit();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["classe"]) && isset($_FILES["csv_file"])) {
    $classe = trim($_POST["classe"]);
    $form_type = $_POST["form_type"];
    
    // Vérifier le fichier CSV
    if ($_FILES["csv_file"]["error"] == 0) {
        $csvMimeTypes = ['text/csv', 'application/csv', 'text/plain', 'application/vnd.ms-excel'];
        $fileType = $_FILES["csv_file"]["type"];
        $fileExt = pathinfo($_FILES["csv_file"]["name"], PATHINFO_EXTENSION);
        
        if (in_array($fileType, $csvMimeTypes) || strtolower($fileExt) === 'csv') {
            $csvFile = $_FILES["csv_file"]["tmp_name"];
            
            // Traitement du fichier CSV - à implémenter selon vos besoins
            // ...
            
            // Retour à la page d'origine avec un message de succès
            header("Location: register.php?type=eleve&status=success&message=Import+réussi");
            exit();
        } else {
            header("Location: register.php?type=eleve&status=error&message=Format+de+fichier+invalide.+Veuillez+télécharger+un+fichier+CSV.");
            exit();
        }
    } else {
        // Erreur de téléchargement
        header("Location: register.php?type=eleve&status=error&message=Erreur+lors+du+téléchargement+du+fichier.");
        exit();
    }
} else {
    // Redirection si le formulaire n'a pas été correctement soumis
    header("Location: register.php?type=eleve");
    exit();
}
?>