<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Récupération de l'ID du rapport
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Connexion à la base de données
$conn = new mysqli('132.148.183.180', 'a7zq6mn2qjza', 'Sna#2024@!!', 'Officier_de_garde');

if ($conn->connect_error) {
    die("Échec de connexion : " . $conn->connect_error);
}
//echo "Connexion réussie !";

// Fonction pour échapper les caractères spéciaux HTML
/*function clean($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}*/
function clean($str) {
    if ($str === null) {
        return '';
    }
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Informations générales
        $directeur = $conn->real_escape_string($_POST['directeur_de_garde'] ?? '');
        $hopital = $conn->real_escape_string($_POST['hopital_concerne'] ?? '');
        $date_heure = $conn->real_escape_string($_POST['date_heure_garde'] ?? '');
        
        // Personnel modifié
        $personnel_modifie = $_POST['personnel'] ?? [];
        
        // Incidents modifiés
        $incident_modifie = $_POST['incidents'] ?? [];
        
        // Activité médicale
        $nb_patients_admis = intval($_POST['nb_patients_admis'] ?? 0);
        $nb_patients_sortis = intval($_POST['nb_patients_sortis'] ?? 0);
        $interventions_importantes = $conn->real_escape_string($_POST['interventions_importantes'] ?? '');
        
        // Actions et améliorations
        $actions_prises = $conn->real_escape_string($_POST['actions_prises'] ?? '');
        $ameliorations = $conn->real_escape_string($_POST['ameliorations'] ?? '');
        
        // Ressources
        $medicaments_equipements = $conn->real_escape_string($_POST['medicaments_equipements'] ?? '');
        $besoin_ressources_sup = $conn->real_escape_string($_POST['besoin_ressources_sup'] ?? '');
        
        // Communications
        $details_communications = $conn->real_escape_string($_POST['details_communications'] ?? '');
        
        // Observations et recommandations
        $points_a_ameliorer = $conn->real_escape_string($_POST['points_a_ameliorer'] ?? '');
        $suggestions_futures = $conn->real_escape_string($_POST['suggestions_futures'] ?? '');

        // Mise à jour du rapport principal
        $sql = "UPDATE reporting_garde SET 
                directeur_de_garde = ?, 
                hopital_concerne = ?, 
                date_heure_garde = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $directeur, $hopital, $date_heure, $id);
        $stmt->execute();

        // Mise à jour du personnel
        
        if (is_array($personnel_modifie)) {
            foreach ($personnel_modifie as $pid => $data) {
                $nom = $conn->real_escape_string($data['nom'] ?? '');
                $fonction = $conn->real_escape_string($data['fonction'] ?? '');
                $shift = $conn->real_escape_string($data['shift'] ?? '');
                $remplacement = $conn->real_escape_string($data['remplacement'] ?? '');
                
                $sql = "UPDATE personnel SET 
                        nom_personnel = ?, 
                        fonction = ?, 
                        shift = ?,
                        remplacement_ou_absence = ? 
                        WHERE id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $nom, $fonction, $shift, $remplacement, $pid);
                $stmt->execute();
            }
        }


        if (isset($_POST['incidents']) && is_array($_POST['incidents'])) {
            foreach ($_POST['incidents'] as $iid => $data) {
                $type_incident = $conn->real_escape_string($data['type_incident'] ?? '');
                $description = $conn->real_escape_string($data['description'] ?? '');
                $heure = $data['heure'] ?? '';
                $lieu = $conn->real_escape_string($data['lieu'] ?? '');
                $personnel_implique = $conn->real_escape_string($data['personnel_implique'] ?? '');

                $sql = "UPDATE incidents SET 
                        type_incident = ?, 
                        description = ?, 
                        heure_incident = ?, 
                        lieu_incident = ?, 
                        personnel_implique = ? 
                        WHERE id = ? AND reporting_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssii", 
                    $type_incident, 
                    $description, 
                    $heure, 
                    $lieu, 
                    $personnel_implique, 
                    $iid, 
                    $id
                );
                $stmt->execute();
            }
        }

        // Mise à jour de l'activité médicale
        $sql = "UPDATE activite_medicale SET 
                nb_patients_admis = ?, 
                nb_patients_sortis = ?, 
                interventions_importantes = ? 
                WHERE reporting_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisi", $nb_patients_admis, $nb_patients_sortis, $interventions_importantes, $id);
        $stmt->execute();

        // Mise à jour des actions prises
        $sql = "UPDATE actions_prises SET actions_prises = ? WHERE reporting_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $actions_prises, $id);
        $stmt->execute();

        // Mise à jour des améliorations
        $sql = "UPDATE ameliorations SET ameliorations = ? WHERE reporting_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $ameliorations, $id);
        $stmt->execute();

        // Mise à jour des ressources
        $sql = "UPDATE ressources_utilisees SET 
                medicaments_equipements = ?, 
                besoin_ressources_sup = ? 
                WHERE reporting_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $medicaments_equipements, $besoin_ressources_sup, $id);
        $stmt->execute();

        // Mise à jour des communications
        $sql = "UPDATE communications SET details_communications = ? WHERE reporting_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $details_communications, $id);
        $stmt->execute();

        // Mise à jour des observations
        $sql = "UPDATE observations_recommandations SET 
                points_a_ameliorer = ?, 
                suggestions_futures = ? 
                WHERE reporting_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $points_a_ameliorer, $suggestions_futures, $id);
        $stmt->execute();

        $_SESSION['success'] = "Les modifications ont été enregistrées avec succès.";
        header("Location: Dashboard");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Une erreur est survenue lors de l'enregistrement des modifications.";
    }
}

$rapport = $conn->query("SELECT * FROM reporting_garde WHERE id = $id")->fetch_assoc();

$personnel = $conn->query("SELECT id, nom_personnel, fonction, shift, remplacement_ou_absence 
                          FROM personnel 
                          WHERE reporting_id = $id");

$incidents = $conn->query("SELECT i.*, io.services_affectes, io.consequences_sur_patients 
                          FROM incidents i 
                          LEFT JOIN impacts_operations io ON i.id = io.incident_id 
                          WHERE i.reporting_id = $id");

$activite_medicale = $conn->query("SELECT * FROM activite_medicale WHERE reporting_id = $id")->fetch_assoc();
$ressources = $conn->query("SELECT * FROM ressources_utilisees WHERE reporting_id = $id")->fetch_assoc();
$communications = $conn->query("SELECT * FROM communications WHERE reporting_id = $id")->fetch_assoc();
$observations = $conn->query("SELECT * FROM observations_recommandations WHERE reporting_id = $id")->fetch_assoc();
$actions = $conn->query("SELECT * FROM actions_prises WHERE reporting_id = $id")->fetch_assoc();
$ameliorations = $conn->query("SELECT * FROM ameliorations WHERE reporting_id = $id")->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modification du rapport</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Modifier le rapport de garde</h1>

        <form action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $id; ?>" method="POST" class="space-y-8">
            <!-- Informations générales -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Informations générales</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-600">Directeur de garde</label>
                        <input type="text" name="directeur_de_garde" value="<?php echo clean($rapport['directeur_de_garde']); ?>" class="w-full border rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-gray-600">Hôpital concerné</label>
                        <input type="text" name="hopital_concerne" value="<?php echo clean($rapport['hopital_concerne']); ?>" class="w-full border rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-gray-600">Date et heure</label>
                        <input type="datetime-local" name="date_heure_garde" value="<?php echo date('Y-m-d\TH:i', strtotime($rapport['date_heure_garde'])); ?>" class="w-full border rounded-lg px-4 py-2">
                    </div>
                </div>
            </div>

            <!-- Personnel -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Personnel présent</h2>
                <?php while ($p = $personnel->fetch_assoc()): ?>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <input type="hidden" name="personnel[<?php echo $p['id']; ?>][id]" value="<?php echo $p['id']; ?>">
                    <div>
                        <label class="block text-gray-600">Nom</label>
                        <input type="text" name="personnel[<?php echo $p['id']; ?>][nom]" value="<?php echo clean($p['nom_personnel']); ?>" class="w-full border rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-gray-600">Fonction</label>
                        <input type="text" name="personnel[<?php echo $p['id']; ?>][fonction]" value="<?php echo clean($p['fonction']); ?>" class="w-full border rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-gray-600">Shift</label>
                        <input type="text" name="personnel[<?php echo $p['id']; ?>][shift]" value="<?php echo clean($p['shift']); ?>" class="w-full border rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-gray-600">Remplacement/Absence</label>
                        <input type="text" name="personnel[<?php echo $p['id']; ?>][remplacement]" value="<?php echo clean($p['remplacement_ou_absence']); ?>" class="w-full border rounded-lg px-4 py-2">
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Incidents -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Incidents</h2>
                <?php while ($incident = $incidents->fetch_assoc()): ?>
                <div class="border-b border-gray-200 pb-4 mb-4 last:border-0">
                    <input type="hidden" name="incidents[<?php echo $incident['id']; ?>][id]" value="<?php echo $incident['id']; ?>">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-600">Type d'incident</label>
                            <input type="text" name="incidents[<?php echo $incident['id']; ?>][type_incident]" value="<?php echo clean($incident['type_incident']); ?>" class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-gray-600">Heure</label>
                            <input type="time" 
                                   name="incidents[<?php echo $incident['id']; ?>][heure]" 
                                   value="<?php echo $incident['heure_incident']; ?>" 
                                   class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-gray-600">Lieu</label>
                            <input type="text" 
                                   name="incidents[<?php echo $incident['id']; ?>][lieu]" 
                                   value="<?php echo clean($incident['lieu_incident']); ?>" 
                                   class="w-full border rounded-lg px-4 py-2">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-600">Description</label>
                        <textarea name="incidents[<?php echo $incident['id']; ?>][description]" 
                                  class="w-full border rounded-lg px-4 py-2 h-24"><?php echo clean($incident['description']); ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-600">Personnel impliqué</label>
                        <textarea name="incidents[<?php echo $incident['id']; ?>][personnel_implique]" 
                                  class="w-full border rounded-lg px-4 py-2 h-24"><?php echo clean($incident['personnel_implique']); ?></textarea>
                    </div>
                    <!-- ... reste du code ... -->
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Activité médicale -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Activité médicale</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-600">Patients admis</label>
                        <input type="number" name="nb_patients_admis" value="<?php echo $activite_medicale['nb_patients_admis']; ?>" class="w-full border rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-gray-600">Patients sortis</label>
                        <input type="number" name="nb_patients_sortis" value="<?php echo $activite_medicale['nb_patients_sortis']; ?>" class="w-full border rounded-lg px-4 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-600">Interventions importantes</label>
                    <textarea name="interventions_importantes" class="w-full border rounded-lg px-4 py-2 h-32"><?php echo clean($activite_medicale['interventions_importantes']); ?></textarea>
                </div>
            </div>

            <!-- Ressources -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Ressources utilisées</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-600">Médicaments et équipements</label>
                        <textarea name="medicaments_equipements" class="w-full border rounded-lg px-4 py-2 h-32"><?php echo clean($ressources['medicaments_equipements']); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-600">Besoins en ressources supplémentaires</label>
                        <textarea name="besoin_ressources_sup" class="w-full border rounded-lg px-4 py-2 h-32"><?php echo clean($ressources['besoin_ressources_sup']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Communications -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Communications importantes</h2>
                <textarea name="details_communications" class="w-full border rounded-lg px-4 py-2 h-32"><?php echo clean($communications['details_communications']); ?></textarea>
            </div>

            <!-- Observations et recommandations -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Observations et recommandations</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-600">Points à améliorer</label>
                        <textarea name="points_a_ameliorer" class="w-full border rounded-lg px-4 py-2 h-32"><?php echo clean($observations['points_a_ameliorer']); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-600">Suggestions pour le futur</label>
                        <textarea name="suggestions_futures" class="w-full border rounded-lg px-4 py-2 h-32"><?php echo clean($observations['suggestions_futures']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-4">
                <a href="Dashboard" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-all">Annuler</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-all">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</body>
</html>