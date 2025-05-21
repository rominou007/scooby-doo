<?php
// filepath: c:\xampp\htdocs\scooby-doo\delete_classe.php
session_start();
require("db.php");

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 2) {
    header("Location: classes.php");
    exit();
}

// Vérifier l'id de la classe
if (!empty($_GET['class_id']) && is_numeric($_GET['class_id'])) {
    $class_id = (int)$_GET['class_id'];
    // Supprimer les liaisons éventuelles (optionnel si ON DELETE CASCADE)
    $pdo->prepare("DELETE FROM student_classes WHERE class_id = :class_id")->execute(['class_id' => $class_id]);
    // Supprimer la classe
    $pdo->prepare("DELETE FROM classes WHERE class_id = :class_id")->execute(['class_id' => $class_id]);
}

// Rediriger vers la liste des classes
header("Location: classes.php");
exit();