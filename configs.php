<?php
// Démarrer la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration de la base de données
$host = '132.148.183.180';
$db_name = 'Officier_de_garde';
$username = 'a7zq6mn2qjza';
$password = 'Sna#2024@!!';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Fonctions utilitaires
function requireLogin() {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: Login");
        exit;
    }
}

function hasRole($role) {
    return isset($_SESSION["role"]) && $_SESSION["role"] === $role;
}

function isAdmin() {
    return hasRole('admin');
}

function formatRole($role) {
    switch($role) {
        case 'admin':
            return 'Administrateur';
        case 'Officier':
            return 'Officier';
        default:
            return $role;
    }
}

function getRoleColor($role) {
    switch($role) {
        case 'admin':
            return '#dc3545';
        case 'Officier':
            return '#28a745';
        default:
            return '#6c757d';
    }
}
?>
?>