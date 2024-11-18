<?php
// Exemple pour insérer un utilisateur avec un mot de passe haché
$nom_utilisateur = 'admin';
$mot_de_passe = 'admin123'; // Mot de passe en texte brut
$mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);

// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'Officier_de_garde');

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

// Insérer l'utilisateur
$sql = "INSERT INTO utilisateur (nom_utilisateur, mot_de_passe) VALUES ('$nom_utilisateur', '$mot_de_passe_hache')";
if ($conn->query($sql) === TRUE) {
    echo "Nouvel utilisateur créé avec succès";
} else {
    echo "Erreur : " . $sql . "<br>" . $conn->error;
}
$conn->close();
?>