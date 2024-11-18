<?php
// Supprimer.php
session_start();

header('Content-Type: application/json');

// Vérifier si l'ID est fourni
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID non fourni']);
    exit;
}

try {
    $conn = new mysqli('132.148.183.180', 'a7zq6mn2qjza', 'Sna#2024@!!', 'Officier_de_garde');
    
    if ($conn->connect_error) {
        throw new Exception("Erreur de connexion à la base de données");
    }
    
    // Commencer la transaction
    $conn->begin_transaction();
    
    $id = intval($_POST['id']);
    
    try {
        // Liste des tables avec une clé étrangère vers reporting_garde
        $tables = [
            'actions_prises',
            'activite_medicale',
            'ameliorations',
            'communications',
            'observations_recommandations',
            'personnel',
            'ressources_utilisees'
        ];
        
        // Supprimer d'abord les incidents et leurs impacts
        $sql = "DELETE impacts_operations FROM impacts_operations 
                INNER JOIN incidents ON impacts_operations.incident_id = incidents.id 
                WHERE incidents.reporting_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Supprimer les incidents
        $sql = "DELETE FROM incidents WHERE reporting_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Supprimer les données des autres tables liées
        foreach ($tables as $table) {
            $sql = "DELETE FROM $table WHERE reporting_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        
        // Enfin, supprimer l'enregistrement principal
        $sql = "DELETE FROM reporting_garde WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Valider la transaction
        $conn->commit();
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur de connexion à la base de données']);
}
?>