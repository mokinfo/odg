<?php
// Activation du rapport d'erreurs

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification et démarrage de session sécurisée
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true
    ]);
    session_start();
}

// Vérification de l'authentification
if (!isset($_SESSION['nom_utilisateur'])) {
    header('Location: login.php');
    exit();
}
// Configuration de la base de données
define('DB_HOST', '132.148.183.180');
define('DB_USER', 'a7zq6mn2qjza');
define('DB_PASS', 'Sna#2024@!!');
define('DB_NAME', 'Officier_de_garde');


// Classe de gestion des erreurs personnalisée
class DatabaseException extends Exception {
    public function __construct($message, $query = '', $code = 0) {
        $errorMessage = $message;
        if (!empty($query)) {
            $errorMessage .= " [Query: $query]";
        }
        parent::__construct($errorMessage, $code);
    }
}

// Classe de connexion à la base de données
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new DatabaseException("Erreur de connexion : " . $this->connection->connect_error);
            }

            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new DatabaseException("Erreur critique de base de données");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query($sql) {
        try {
            $result = $this->connection->query($sql);
            if ($result === false) {
                throw new DatabaseException("Erreur de requête : " . $this->connection->error, $sql);
            }
            return $result;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new DatabaseException("Erreur lors de l'exécution de la requête");
        }
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Classe de gestion des données
class DashboardData {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getPatientsData() {
        try {
            $sql = "SELECT 
                        COALESCE(SUM(nb_patients_admis), 0) as total_admis, 
                        COALESCE(SUM(nb_patients_sortis), 0) as total_sortis 
                    FROM activite_medicale 
                    WHERE reporting_id IN (
                        SELECT id FROM reporting_garde 
                        WHERE date_heure_garde >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    )";
            $result = $this->db->query($sql);
            return $result->fetch_assoc();
        } catch (DatabaseException $e) {
            error_log($e->getMessage());
            return ['total_admis' => 0, 'total_sortis' => 0];
        }
    }

    public function getIncidentsData() {
        try {
            $sql = "SELECT 
                        COALESCE(type_incident, 'Non catégorisé') as type_incident, 
                        COUNT(*) as count 
                    FROM incidents 
                    WHERE reporting_id IN (
                        SELECT id FROM reporting_garde 
                        WHERE date_heure_garde >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    )
                    GROUP BY type_incident";
            $result = $this->db->query($sql);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            return $data;
        } catch (DatabaseException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getPersonnelData() {
        try {
            $sql = "SELECT 
                        COUNT(*) as absences,
                        (SELECT COUNT(*) FROM personnel) as total_personnel
                    FROM personnel 
                    WHERE remplacement_ou_absence IS NOT NULL 
                    AND reporting_id IN (
                        SELECT id FROM reporting_garde 
                        WHERE date_heure_garde >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    )";
            $result = $this->db->query($sql);
            return $result->fetch_assoc();
        } catch (DatabaseException $e) {
            error_log($e->getMessage());
            return ['absences' => 0, 'total_personnel' => 0];
        }
    }

    public function getDerniersIncidents() {
        try {
            $sql = "SELECT 
                        r.date_heure_garde,
                        i.type_incident,
                        i.description,
                        i.lieu_incident,
                        io.services_affectes
                    FROM incidents i
                    JOIN reporting_garde r ON i.reporting_id = r.id
                    LEFT JOIN impacts_operations io ON io.incident_id = i.id
                    ORDER BY r.date_heure_garde DESC
                    LIMIT 10";
            $result = $this->db->query($sql);
            $incidents = [];
            while ($row = $result->fetch_assoc()) {
                $incidents[] = array_map('htmlspecialchars', $row);
            }
            return $incidents;
        } catch (DatabaseException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
}

// Initialisation des données
try {
    $dashboard = new DashboardData();
    $patients_data = $dashboard->getPatientsData();
    $incidents_data = $dashboard->getIncidentsData();
    $personnel_data = $dashboard->getPersonnelData();
    $derniers_incidents = $dashboard->getDerniersIncidents();

    // Préparation des données pour JavaScript
    $data_for_js = [
        'patients' => $patients_data,
        'incidents' => $incidents_data,
        'personnel' => $personnel_data
    ];

    // Conversion en JSON avec gestion des erreurs
    $json_data = json_encode($data_for_js, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    if ($json_data === false) {
        throw new Exception("Erreur lors de l'encodage JSON");
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $json_data = json_encode(['error' => true]);
}
?>