<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuration Telegram
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN'); // Remplacez par votre token de bot Telegram
define('TELEGRAM_CHAT_ID', 'YOUR_CHAT_ID'); // Remplacez par l'ID du chat où envoyer les messages

// Configuration WhatsApp Business API
define('WHATSAPP_TOKEN', 'YOUR_WHATSAPP_TOKEN'); // Remplacez par votre token WhatsApp Business
define('WHATSAPP_PHONE_NUMBER_ID', 'YOUR_PHONE_NUMBER_ID'); // Remplacez par votre Phone Number ID

// Fonction pour envoyer un message via Telegram
function sendTelegramMessage($message) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $data = array(
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML'
    );

    $options = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => 'Content-Type: application/json',
            'content' => json_encode($data)
        )
    );

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return $result !== FALSE;
}

// Fonction pour envoyer un message via WhatsApp
function sendWhatsAppMessage($to, $message) {
    $url = "https://graph.facebook.com/v17.0/" . WHATSAPP_PHONE_NUMBER_ID . "/messages";
    
    $data = array(
        'messaging_product' => 'whatsapp',
        'to' => $to,
        'type' => 'text',
        'text' => array(
            'body' => $message
        )
    );

    $options = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n" .
                        "Authorization: Bearer " . WHATSAPP_TOKEN,
            'content' => json_encode($data)
        )
    );

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return $result !== FALSE;
}

$servername = "132.148.183.180";
$username = "a7zq6mn2qjza";
$password = "Sna#2024@!!";
$dbname = "Officier_de_garde"; // Remplacez par le nom de votre base de données

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

// Définit l'encodage de la connexion en UTF-8
$conn->set_charset("utf8mb4");

// Démarre une transaction pour garantir que toutes les insertions sont exécutées ou aucune
$conn->begin_transaction();

try {
    // Récupération et insertion des informations générales
    $date_heure_garde = $_POST['date_heure_garde'];
    $directeur_de_garde = $_POST['directeur_de_garde'];
    $hopital_concerne = $_POST['hopital_concerne'];

    $stmt = $conn->prepare("INSERT INTO reporting_garde (date_heure_garde, directeur_de_garde, hopital_concerne) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $date_heure_garde, $directeur_de_garde, $hopital_concerne);
    $stmt->execute();
    $reporting_id = $stmt->insert_id;
    $stmt->close();

    // Insertion des informations de personnel
    if (!empty($_POST['personnel'])) {
        foreach ($_POST['personnel'] as $person) {
            $nom_personnel = $person['nom'];
            $fonction = $person['fonction'];
            $shift = $person['shift'];
            $remplacement_ou_absence = $person['remplacement_ou_absence'];

            $stmt = $conn->prepare("INSERT INTO personnel (reporting_id, nom_personnel, fonction, shift, remplacement_ou_absence) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $reporting_id, $nom_personnel, $fonction, $shift, $remplacement_ou_absence);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Insertion des incidents
    $incident_type = $_POST['incident_type'];
    $incident_description = $_POST['incident_description'];
    $incident_heure = $_POST['incident_heure'];
    $incident_lieu = $_POST['incident_lieu'];
    $incident_personnel = $_POST['incident_personnel'];

    $stmt = $conn->prepare("INSERT INTO incidents (reporting_id, type_incident, description, heure_incident, lieu_incident, personnel_impliqué) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $reporting_id, $incident_type, $incident_description, $incident_heure, $incident_lieu, $incident_personnel);
    $stmt->execute();
    $incident_id = $stmt->insert_id; // Récupère l'ID de l'incident pour l'impact
    $stmt->close();

    // Insertion des impacts sur les opérations
    $services_affectes = implode(", ", $_POST['services_affectes']);
    $consequences_sur_patients = $_POST['consequences_sur_patients'];

    $stmt = $conn->prepare("INSERT INTO impacts_operations (incident_id, services_affectes, consequences_sur_patients) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $incident_id, $services_affectes, $consequences_sur_patients);
    $stmt->execute();
    $stmt->close();

    // Insertion des réponses et recommandations
    $actions_prises = $_POST['actions_prises'];
    $ameliorations_proposees = $_POST['ameliorations_proposees'];

    $stmt = $conn->prepare("INSERT INTO actions_prises (reporting_id, actions_prises) VALUES (?, ?)");
    $stmt->bind_param("is", $reporting_id, $actions_prises);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO ameliorations (reporting_id, ameliorations) VALUES (?, ?)");
    $stmt->bind_param("is", $reporting_id, $ameliorations_proposees);
    $stmt->execute();
    $stmt->close();

    // Insertion des informations d'activité médicale
    $nb_patients_admis = $_POST['nb_patients_admis'];
    $nb_patients_sortis = $_POST['nb_patients_sortis'];
    $interventions_importantes = $_POST['interventions_importantes'];

    $stmt = $conn->prepare("INSERT INTO activite_medicale (reporting_id, nb_patients_admis, nb_patients_sortis, interventions_importantes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $reporting_id, $nb_patients_admis, $nb_patients_sortis, $interventions_importantes);
    $stmt->execute();
    $stmt->close();

    // Insertion des ressources utilisées
    $medicaments_equipements = $_POST['medicaments_equipements'];
    $besoin_ressources_sup = $_POST['besoin_ressources_sup'];

    $stmt = $conn->prepare("INSERT INTO ressources_utilisees (reporting_id, medicaments_equipements, besoin_ressources_sup) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $reporting_id, $medicaments_equipements, $besoin_ressources_sup);
    $stmt->execute();
    $stmt->close();

    // Insertion des communications
    $details_communications = $_POST['details_communications'];

    $stmt = $conn->prepare("INSERT INTO communications (reporting_id, details_communications) VALUES (?, ?)");
    $stmt->bind_param("is", $reporting_id, $details_communications);
    $stmt->execute();
    $stmt->close();

    // Insertion des observations et recommandations
    $points_a_ameliorer = $_POST['points_a_ameliorer'];
    $suggestions_futures = $_POST['suggestions_futures'];

    $stmt = $conn->prepare("INSERT INTO observations_recommandations (reporting_id, points_a_ameliorer, suggestions_futures) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $reporting_id, $points_a_ameliorer, $suggestions_futures);
    $stmt->execute();

    if ($stmt->execute()) {
        // Préparation du contenu de l'email
        $to = "directeur.technique@chupeltier.com"; // Remplacez par l'adresse email désirée
        $subject = "Nouveau rapport d'officier de garde - " . $hopital_concerne;
        
        $message = "
        <html>
        <head>
            <title>Nouveau rapport d'officier de garde</title>
        </head>
        <body>
            <h2>Rapport d'officier de garde</h2>
            <p><strong>Date et heure de la garde:</strong> {$date_heure_garde}</p>
            <p><strong>Directeur de garde:</strong> {$directeur_de_garde}</p>
            <p><strong>Hôpital concerné:</strong> {$hopital_concerne}</p>
            
            <h3>Incidents</h3>
            <p><strong>Type d'incident:</strong> {$_POST['incident_type']}</p>
            <p><strong>Description:</strong> {$_POST['incident_description']}</p>
            
            <h3>Activité médicale</h3>
            <p><strong>Patients admis:</strong> {$_POST['nb_patients_admis']}</p>
            <p><strong>Patients sortis:</strong> {$_POST['nb_patients_sortis']}</p>
            
            <h3>Recommandations</h3>
            <p><strong>Actions prises:</strong> {$_POST['actions_prises']}</p>
            <p><strong>Points à améliorer:</strong> {$_POST['points_a_ameliorer']}</p>
        </body>
        </html>
        ";

        // Configuration des en-têtes pour l'email HTML
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: CHU@peltier.dj' . "\r\n";

        // Envoi de l'email
        if(mail($to, $subject, $message, $headers)) {
            $_SESSION['success_message'] = "Le rapport a été enregistré et envoyé par email avec succès.";
        } else {
            $_SESSION['warning_message'] = "Le rapport a été enregistré mais l'envoi de l'email a échoué.";
        }
        // Envoyer via Telegram
        $telegram_success = sendTelegramMessage($notification_message);
        
        // Envoyer via WhatsApp (remplacez RECIPIENT_PHONE_NUMBER par le numéro de téléphone du destinataire)
        $whatsapp_success = sendWhatsAppMessage('RECIPIENT_PHONE_NUMBER', $notification_message);
        
        $_SESSION['success_message'] = "Le rapport a été enregistré et les notifications ont été envoyées.";
        if (!$telegram_success || !$whatsapp_success) {
            $_SESSION['warning_message'] = "Certaines notifications n'ont pas pu être envoyées.";
        }
        
        //header("Location: Accueil"); // Redirection vers la page d'accueil après succès
    } else {
        $_SESSION['error_message'] = "Erreur lors de l'enregistrement du rapport.";
        //header("Location: " . $_SERVER['HTTP_REFERER']); // Retour à la page précédente en cas d'erreur
    }

    $stmt->close();
    // Si tout s'est bien passé, on valide la transaction
    $conn->commit();

    // Affichage du message de succès et redirection avec SweetAlert
    echo '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirmation</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                title: "Envoi réussi",
                text: "Les données ont été enregistrées avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "accueil.php";
                }
            });
        </script>
    </body>
    </html>';

} catch (Exception $e) {
    // Si une erreur survient, on annule la transaction
    $conn->rollback();
    echo "Erreur lors de l'insertion des données : " . $e->getMessage();
}

// Fermeture de la connexion
$conn->close();
?>