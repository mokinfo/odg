<?php
// Inclure les dépendances (à installer via Composer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/PHPMailer/PHPMailer/src/Exception.php';
require 'vendor/PHPMailer/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/PHPMailer/src/SMTP.php';

// Utilisez plutôt
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');



class ReportPDF extends TCPDF {
    // En-tête personnalisé
    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 15, 'Rapport de Garde Hospitalière', 0, true, 'C');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 10, 'Date du rapport: ' . date('d/m/Y'), 0, true, 'C');
        $this->Ln(10);
    }

    // Pied de page personnalisé
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C');
    }
}

// Configuration de la base de données
$config = [
    'host' => 'localhost',
    'dbname' => 'Officier_de_garde',
    'user' => 'root',
    'pass' => ''
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonction pour récupérer les données du rapport
function getReportData($pdo, $dateDebut, $dateFin) {
    // Récupérer les rapports de garde
    $query = "SELECT 
                rg.date_heure_garde,
                rg.directeur_de_garde,
                rg.hopital_concerne,
                COUNT(DISTINCT i.id) as nb_incidents,
                COUNT(DISTINCT p.id) as nb_patients,
                SUM(p.nb_admis) as total_admis,
                SUM(p.nb_sortis) as total_sortis
              FROM reporting_garde rg
              LEFT JOIN incidents i ON i.reporting_id = rg.id
              LEFT JOIN patients p ON p.reporting_id = rg.id
              WHERE rg.date_heure_garde BETWEEN :date_debut AND :date_fin
              GROUP BY rg.id
              ORDER BY rg.date_heure_garde DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':date_debut' => $dateDebut,
        ':date_fin' => $dateFin
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour générer le PDF
function generatePDF($data) {
    // Création d'une nouvelle instance de PDF
    $pdf = new ReportPDF('P', 'mm', 'A4', true, 'UTF-8');

    // Paramètres du document
    $pdf->SetCreator('Système de Reporting');
    $pdf->SetAuthor('Hôpital');
    $pdf->SetTitle('Rapport de Garde ' . date('d/m/Y'));

    // Ajout d'une page
    $pdf->AddPage();

    // En-tête du rapport
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Synthèse des activités', 0, 1, 'L');
    $pdf->Ln(5);

    // Tableau des données
    $pdf->SetFont('helvetica', '', 10);
    
    // En-têtes du tableau
    $header = array('Date', 'Directeur', 'Hôpital', 'Incidents', 'Admissions', 'Sorties');
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(35, 7, $header[0], 1, 0, 'C', true);
    $pdf->Cell(40, 7, $header[1], 1, 0, 'C', true);
    $pdf->Cell(40, 7, $header[2], 1, 0, 'C', true);
    $pdf->Cell(25, 7, $header[3], 1, 0, 'C', true);
    $pdf->Cell(25, 7, $header[4], 1, 0, 'C', true);
    $pdf->Cell(25, 7, $header[5], 1, 1, 'C', true);

    // Données
    $pdf->SetFillColor(245, 245, 245);
    $fill = false;
    foreach($data as $row) {
        $pdf->Cell(35, 6, date('d/m/Y H:i', strtotime($row['date_heure_garde'])), 1, 0, 'C', $fill);
        $pdf->Cell(40, 6, $row['directeur_de_garde'], 1, 0, 'L', $fill);
        $pdf->Cell(40, 6, $row['hopital_concerne'], 1, 0, 'L', $fill);
        $pdf->Cell(25, 6, $row['nb_incidents'], 1, 0, 'C', $fill);
        $pdf->Cell(25, 6, $row['total_admis'], 1, 0, 'C', $fill);
        $pdf->Cell(25, 6, $row['total_sortis'], 1, 1, 'C', $fill);
        $fill = !$fill;
    }

    // Statistiques globales
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Statistiques globales', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);

    $totalIncidents = array_sum(array_column($data, 'nb_incidents'));
    $totalAdmissions = array_sum(array_column($data, 'total_admis'));
    $totalSorties = array_sum(array_column($data, 'total_sortis'));

    $pdf->Cell(0, 6, "Total des incidents: " . $totalIncidents, 0, 1, 'L');
    $pdf->Cell(0, 6, "Total des admissions: " . $totalAdmissions, 0, 1, 'L');
    $pdf->Cell(0, 6, "Total des sorties: " . $totalSorties, 0, 1, 'L');

    return $pdf;
}

// Fonction pour envoyer l'email
function sendEmailReport($pdfContent, $emailTo) {
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Votre serveur SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'djama.said.beile@gmail.com';
        $mail->Password = 'Mokinfo#@2024!';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Destinataires
        $mail->setFrom('moktarsaid@gmail.com', 'Système de Reporting');
        $mail->addAddress($emailTo);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Rapport de Garde - ' . date('d/m/Y');
        $mail->Body = 'Veuillez trouver ci-joint le rapport de garde.';

        // Pièce jointe
        $mail->addStringAttachment(
            $pdfContent,
            'rapport_garde_' . date('Y-m-d') . '.pdf',
            'base64',
            'application/pdf'
        );

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Erreur d'envoi d'email: {$mail->ErrorInfo}";
    }
}

// Page de formulaire et traitement
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Génération et Envoi de Rapport</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6">Génération et Envoi de Rapport</h1>
            
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    // Récupération des données du formulaire
                    $dateDebut = $_POST['date_debut'];
                    $dateFin = $_POST['date_fin'];
                    $emailTo = $_POST['email'];

                    // Validation des données
                    if (empty($dateDebut) || empty($dateFin) || empty($emailTo)) {
                        throw new Exception("Tous les champs sont requis.");
                    }

                    // Récupération des données
                    $reportData = getReportData($pdo, $dateDebut, $dateFin);

                    // Génération du PDF
                    $pdf = generatePDF($reportData);
                    $pdfContent = $pdf->Output('', 'S');

                    // Envoi de l'email
                    $result = sendEmailReport($pdfContent, $emailTo);

                    if ($result === true) {
                        echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                                Le rapport a été généré et envoyé avec succès.
                              </div>';
                    } else {
                        throw new Exception($result);
                    }

                } catch (Exception $e) {
                    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                            Erreur : ' . htmlspecialchars($e->getMessage()) . '
                          </div>';
                }
            }
            ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="date_debut">
                        Date de début
                    </label>
                    <input type="datetime-local" 
                           name="date_debut" 
                           id="date_debut"
                           required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="date_fin">
                        Date de fin
                    </label>
                    <input type="datetime-local" 
                           name="date_fin" 
                           id="date_fin"
                           required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email du destinataire
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email"
                           required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Générer et Envoyer le Rapport
                </button>
            </form>
        </div>
    </div>
</body>
</html>