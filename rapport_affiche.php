<?php
require_once 'vendor/autoload.php'; // Pour TCPDF
use TCPDF as TCPDF;

// Récupération de l'ID du rapport
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Connexion à la base de données
$conn = new mysqli('132.148.183.180', 'a7zq6mn2qjza', 'Sna#2024@!!', 'Officier_de_garde');

// Fonction pour échapper les caractères spéciaux HTML
function clean($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Récupération des données du rapport principal
$rapport = $conn->query("SELECT * FROM reporting_garde WHERE id = $id")->fetch_assoc();

// Récupération du personnel
$personnel = $conn->query("SELECT * FROM personnel WHERE reporting_id = $id");

// Récupération des incidents
$incidents = $conn->query("SELECT i.*, io.services_affectes, io.consequences_sur_patients 
                          FROM incidents i 
                          LEFT JOIN impacts_operations io ON i.id = io.incident_id 
                          WHERE i.reporting_id = $id");

// Récupération des autres informations
$actions = $conn->query("SELECT * FROM actions_prises WHERE reporting_id = $id")->fetch_assoc();
$ameliorations = $conn->query("SELECT * FROM ameliorations WHERE reporting_id = $id")->fetch_assoc();
$activite = $conn->query("SELECT * FROM activite_medicale WHERE reporting_id = $id")->fetch_assoc();
$ressources = $conn->query("SELECT * FROM ressources_utilisees WHERE reporting_id = $id")->fetch_assoc();
$communications = $conn->query("SELECT * FROM communications WHERE reporting_id = $id")->fetch_assoc();
$observations = $conn->query("SELECT * FROM observations_recommandations WHERE reporting_id = $id")->fetch_assoc();

// Vérifier si on demande le PDF
if(isset($_GET['pdf'])) {
    // Création du PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Métadonnées du document
    $pdf->SetCreator('Système de rapport de garde');
    $pdf->SetAuthor($rapport['directeur_de_garde']);
    $pdf->SetTitle('Rapport de garde - ' . $rapport['date_heure_garde']);
    
    // En-tête
    $pdf->SetHeaderData('', 0, 'Rapport de garde', $rapport['date_heure_garde']);
    
    // Police
    $pdf->setHeaderFont(Array('helvetica', '', 12));
    $pdf->setFooterFont(Array('helvetica', '', 8));
    
    // Marges
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    
    // Nouvelle page
    $pdf->AddPage();
    
    // Contenu du PDF
    $html = "<h1>Rapport de garde</h1>";
    $html .= "<h2>Informations générales</h2>";
    $html .= "<p>Date: " . $rapport['date_heure_garde'] . "</p>";
    $html .= "<p>Directeur de garde: " . $rapport['directeur_de_garde'] . "</p>";
    $html .= "<p>Hôpital: " . $rapport['hopital_concerne'] . "</p>";
    
    // Ajout du personnel
    $html .= "<h2>Personnel présent</h2><ul>";
    while($p = $personnel->fetch_assoc()) {
        $html .= "<li>" . $p['nom_personnel'] . " - " . $p['fonction'] . "</li>";
    }
    $html .= "</ul>";
    
    // ... Ajoutez le reste des sections
    
    // Génération du PDF
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Téléchargement du PDF
    $pdf->Output('rapport_garde_' . $id . '.pdf', 'D');
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de garde détaillé</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Rapport de garde détaillé</h1>
            <a href="pdf.php?id=<?php echo $id; ?>&pdf=1" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-all">
                <i class="fas fa-file-pdf mr-2"></i>Télécharger PDF
            </a>
        </div>

        <!-- Informations générales -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Informations générales</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-gray-600">Date et heure</p>
                    <p class="font-medium"><?php echo clean($rapport['date_heure_garde']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Directeur de garde</p>
                    <p class="font-medium"><?php echo clean($rapport['directeur_de_garde']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Hôpital</p>
                    <p class="font-medium"><?php echo clean($rapport['hopital_concerne']); ?></p>
                </div>
            </div>
        </div>

        <!-- Personnel -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Personnel présent</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fonction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remplacement/Absence</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($p = $personnel->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo clean($p['nom_personnel']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo clean($p['fonction']); ?></td>
                            <td class="px-6 py-4"><?php echo clean($p['remplacement_ou_absence']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Incidents -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Incidents</h2>
            <?php while($incident = $incidents->fetch_assoc()): ?>
            <div class="border-b border-gray-200 pb-4 mb-4 last:border-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Type d'incident</p>
                        <p class="font-medium"><?php echo clean($incident['type_incident']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Heure</p>
                        <p class="font-medium"><?php echo clean($incident['heure_incident']); ?></p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-gray-600">Description</p>
                    <p class="mt-1"><?php echo clean($incident['description']); ?></p>
                </div>
                <div class="mt-4">
                    <p class="text-gray-600">Services affectés</p>
                    <p class="mt-1"><?php echo clean($incident['services_affectes']); ?></p>
                </div>
                <div class="mt-4">
                    <p class="text-gray-600">Conséquences sur les patients</p>
                    <p class="mt-1"><?php echo clean($incident['consequences_sur_patients']); ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Activité médicale -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Activité médicale</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Patients admis</p>
                    <p class="font-medium"><?php echo $activite['nb_patients_admis']; ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Patients sortis</p>
                    <p class="font-medium"><?php echo $activite['nb_patients_sortis']; ?></p>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-gray-600">Interventions importantes</p>
                <p class="mt-1"><?php echo clean($activite['interventions_importantes']); ?></p>
            </div>
        </div>

        <!-- Actions et améliorations -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Actions prises</h2>
                <p><?php echo clean($actions['actions_prises']); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Améliorations proposées</h2>
                <p><?php echo clean($ameliorations['ameliorations']); ?></p>
            </div>
        </div>

        <!-- Ressources -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Ressources utilisées</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Médicaments et équipements</p>
                    <p class="mt-1"><?php echo clean($ressources['medicaments_equipements']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Besoins en ressources supplémentaires</p>
                    <p class="mt-1"><?php echo clean($ressources['besoin_ressources_sup']); ?></p>
                </div>
            </div>
        </div>

        <!-- Communications -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Communications importantes</h2>
            <p><?php echo clean($communications['details_communications']); ?></p>
        </div>

        <!-- Observations et recommandations -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Observations et recommandations</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Points à améliorer</p>
                    <p class="mt-1"><?php echo clean($observations['points_a_ameliorer']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Suggestions pour le futur</p>
                    <p class="mt-1"><?php echo clean($observations['suggestions_futures']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Ajoutez ici vos scripts JavaScript si nécessaire
    </script>
</body>
</html>