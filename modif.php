<?php // Connexion à la base de données
$conn = new mysqli('132.148.183.180', 'a7zq6mn2qjza', 'Sna#2024@!!', 'Officier_de_garde');

// Fonction pour échapper les caractères spéciaux HTML
function clean($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
} 
// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $directeur = $conn->real_escape_string($_POST['directeur_de_garde']);
    $hopital = $conn->real_escape_string($_POST['hopital_concerne']);
    $date_heure = $conn->real_escape_string($_POST['date_heure_garde']);
    $personnel_modifie = $_POST['personnel']; // Tableau des personnels modifiés
    $incident_modifie = $_POST['incidents']; // Tableau des incidents modifiés
    $activite_medicale = $conn->real_escape_string($_POST['activite_medicale']);
    $ressources_utilisees = $conn->real_escape_string($_POST['ressources_utilisees']);
    $communications = $conn->real_escape_string($_POST['communications']);
    $observations = $conn->real_escape_string($_POST['observations']);

    // Mise à jour du rapport principal
    $conn->query("UPDATE reporting_garde SET directeur_de_garde = '$directeur', hopital_concerne = '$hopital', date_heure_garde = '$date_heure' WHERE id = $id");

    // Mise à jour du personnel
    foreach ($personnel_modifie as $pid => $data) {
        $nom = $conn->real_escape_string($data['nom']);
        $fonction = $conn->real_escape_string($data['fonction']);
        $remplacement = $conn->real_escape_string($data['remplacement']);
        $conn->query("UPDATE personnel SET nom_personnel = '$nom', fonction = '$fonction', remplacement_ou_absence = '$remplacement' WHERE id = $pid");
    }

    // Mise à jour des incidents
    foreach ($incident_modifie as $iid => $data) {
        $type = $conn->real_escape_string($data['type']);
        $heure = $conn->real_escape_string($data['heure']);
        $description = $conn->real_escape_string($data['description']);
        $conn->query("UPDATE incidents SET type_incident = '$type', heure_incident = '$heure', description = '$description' WHERE id = $iid");
    }

    // Mise à jour des autres sections
    $conn->query("UPDATE activite_medicale SET details = '$activite_medicale' WHERE reporting_id = $id");
    $conn->query("UPDATE ressources_utilisees SET details = '$ressources_utilisees' WHERE reporting_id = $id");
    $conn->query("UPDATE communications SET details_communications = '$communications' WHERE reporting_id = $id");
    $conn->query("UPDATE observations_recommandations SET details = '$observations' WHERE reporting_id = $id");

    header("Location: Modifier?id=$id&success=1");
    exit();
}
?>