<?php
// database.php

// Paramètres de connexion
$host = '132.148.183.180';
$dbname = 'Officier_de_garde';
$username = 'a7zq6mn2qjza';
$password = 'Sna#2024@!!';

// Création de la connexion
$mysqli = new mysqli($host, $username, $password, $dbname);

// Vérifier la connexion
if ($mysqli->connect_error) {
    die("Connexion échouée : " . $mysqli->connect_error);
}

// Configuration du charset pour éviter les problèmes d'encodage
$mysqli->set_charset("utf8");
?>