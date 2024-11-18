<?php
// fonction.php

// Fonction de nettoyage pour empêcher les attaques XSS
function clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Fonction pour récupérer un rapport par son ID
function getRapportById($id, $mysqli) {
    $stmt = $mysqli->prepare("SELECT * FROM reporting_garde WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Fonction pour récupérer le personnel présent pour un rapport
function getPersonnelByRapportId($id, $mysqli) {
    $stmt = $mysqli->prepare("SELECT * FROM personnel WHERE rapport_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result();
}

// Fonction pour récupérer les incidents pour un rapport
function getIncidentsByRapportId($id, $mysqli) {
    $stmt = $mysqli->prepare("SELECT * FROM incidents WHERE rapport_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result();
}

// Vous pouvez ajouter d'autres fonctions pour chaque section de votre page si nécessaire.
?>