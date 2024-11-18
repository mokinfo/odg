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
    // Création du PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configuration du PDF
$pdf->SetCreator('Système de rapport de garde');
$pdf->SetAuthor($rapport['directeur_de_garde']);
$pdf->SetTitle('Rapport de garde - ' . $rapport['date_heure_garde']);

// Suppression des en-têtes et pieds de page par défaut
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configuration de la police
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(15, 15, 15);

// Police pour tout le document
$pdf->SetFont('helvetica', '', 10);

// Ajout d'une nouvelle page
$pdf->AddPage();

// Style CSS pour le PDF
$style = '
<style>
    h1 { font-size: 24px; font-weight: bold; margin-bottom: 20px; }
    h2 { font-size: 18px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; background-color: #f8f9fa; padding: 8px; }
    .info-block { background-color: #ffffff; padding: 15px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
    .grid { display: block; margin-bottom: 10px; }
    .grid-item { margin-bottom: 10px; }
    .label { color: #666666; font-size: 12px; }
    .value { font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background-color: #f8f9fa; padding: 8px; text-align: left; font-weight: bold; border: 1px solid #e2e8f0; }
    td { padding: 8px; border: 1px solid #e2e8f0; }
</style>';

// Début du contenu HTML
$html = $style . '
<h1>Rapport de garde détaillé</h1>

<!-- Informations générales -->
<div class="info-block">
    <h2>Informations générales</h2>
    <div class="grid">
        <div class="grid-item">
            <span class="label">Date et heure</span><br>
            <span class="value">' . clean($rapport['date_heure_garde']) . '</span>
        </div>
        <div class="grid-item">
            <span class="label">Directeur de garde</span><br>
            <span class="value">' . clean($rapport['directeur_de_garde']) . '</span>
        </div>
        <div class="grid-item">
            <span class="label">Hôpital</span><br>
            <span class="value">' . clean($rapport['hopital_concerne']) . '</span>
        </div>
    </div>
</div>

<!-- Personnel -->
<div class="info-block">
    <h2>Personnel présent</h2>
    <table>
        <tr>
            <th>Nom</th>
            <th>Fonction</th>
            <th>Remplacement/Absence</th>
        </tr>';

// Reset du pointeur de résultat pour le personnel
$personnel->data_seek(0);
while($p = $personnel->fetch_assoc()) {
    $html .= '
        <tr>
            <td>' . clean($p['nom_personnel']) . '</td>
            <td>' . clean($p['fonction']) . '</td>
            <td>' . clean($p['remplacement_ou_absence']) . '</td>
        </tr>';
}

$html .= '
    </table>
</div>

<!-- Incidents -->
<div class="info-block">
    <h2>Incidents</h2>';

// Reset du pointeur de résultat pour les incidents
$incidents->data_seek(0);
while($incident = $incidents->fetch_assoc()) {
    $html .= '
    <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">
        <div class="grid">
            <div class="grid-item">
                <span class="label">Type d\'incident</span><br>
                <span class="value">' . clean($incident['type_incident']) . '</span>
            </div>
            <div class="grid-item">
                <span class="label">Heure</span><br>
                <span class="value">' . clean($incident['heure_incident']) . '</span>
            </div>
        </div>
        <div class="grid-item">
            <span class="label">Description</span><br>
            <span class="value">' . clean($incident['description']) . '</span>
        </div>
        <div class="grid-item">
            <span class="label">Services affectés</span><br>
            <span class="value">' . clean($incident['services_affectes']) . '</span>
        </div>
        <div class="grid-item">
            <span class="label">Conséquences sur les patients</span><br>
            <span class="value">' . clean($incident['consequences_sur_patients']) . '</span>
        </div>
    </div>';
}

// Activité médicale
$html .= '
<div class="info-block">
    <h2>Activité médicale</h2>
    <div class="grid">
        <div class="grid-item">
            <span class="label">Patients admis</span><br>
            <span class="value">' . $activite['nb_patients_admis'] . '</span>
        </div>
        <div class="grid-item">
            <span class="label">Patients sortis</span><br>
            <span class="value">' . $activite['nb_patients_sortis'] . '</span>
        </div>
    </div>
    <div class="grid-item">
        <span class="label">Interventions importantes</span><br>
        <span class="value">' . clean($activite['interventions_importantes']) . '</span>
    </div>
</div>

<!-- Actions et améliorations -->
<div class="info-block">
    <h2>Actions prises</h2>
    <p>' . clean($actions['actions_prises']) . '</p>
</div>

<div class="info-block">
    <h2>Améliorations proposées</h2>
    <p>' . clean($ameliorations['ameliorations']) . '</p>
</div>

<!-- Ressources -->
<div class="info-block">
    <h2>Ressources utilisées</h2>
    <div class="grid">
        <div class="grid-item">
            <span class="label">Médicaments et équipements</span><br>
            <span class="value">' . clean($ressources['medicaments_equipements']) . '</span>
        </div>
        <div class="grid-item">
            <span class="label">Besoins en ressources supplémentaires</span><br>
            <span class="value">' . clean($ressources['besoin_ressources_sup']) . '</span>
        </div>
    </div>
</div>

<!-- Communications -->
<div class="info-block">
    <h2>Communications importantes</h2>
    <p>' . clean($communications['details_communications']) . '</p>
</div>

<!-- Observations et recommandations -->
<div class="info-block">
    <h2>Observations et recommandations</h2>
    <div class="grid">
        <div class="grid-item">
            <span class="label">Points à améliorer</span><br>
            <span class="value">' . clean($observations['points_a_ameliorer']) . '</span>
        </div>
        <div class="grid-item">
            <span class="label">Suggestions pour le futur</span><br>
            <span class="value">' . clean($observations['suggestions_futures']) . '</span>
        </div>
    </div>
</div>';

// Génération du PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Force le téléchargement du PDF
$pdf->Output('rapport_garde_' . $id . '.pdf', 'D');
exit();
}

