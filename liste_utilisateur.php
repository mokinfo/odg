<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Initialisation de la session
session_start();

// Configuration de la base de données
$conn = new mysqli('132.148.183.180', 'a7zq6mn2qjza', 'Sna#2024@!!', 'Officier_de_garde');

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success_message = '';
$error_message = '';

// Traitement de la suppression si demandée
if(isset($_POST['delete_user']) && !empty($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Empêcher la suppression de son propre compte
    if($user_id == $_SESSION['id']) {
        $error_message = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        $stmt = $conn->prepare("UPDATE utilisateurs SET actif = 0 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if($stmt->execute()) {
            $success_message = "L'utilisateur a été désactivé avec succès.";
        } else {
            $error_message = "Une erreur est survenue lors de la désactivation de l'utilisateur.";
        }
        $stmt->close();
    }
}

// Récupération des utilisateurs
$users = array();
$sql = "SELECT * FROM utilisateurs ORDER BY actif DESC, nom, prenom";
$result = $conn->query($sql);

if ($result) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des utilisateurs - Gestion des Urgences</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .add-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }
        .add-button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .edit-btn {
            background-color: #007bff;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .role-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .role-admin {
            background-color: #dc3545;
            color: white;
        }
        .role-medecin {
            background-color: #28a745;
            color: white;
        }
        .role-infirmier {
            background-color: #17a2b8;
            color: white;
        }
        .role-reception {
            background-color: #ffc107;
            color: black;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="Dashboard" class="add-button">Retour au dashboard</a>
            <h2>Liste des utilisateurs</h2>
            <a href="Ajout_utilisateur" class="add-button">Ajouter un utilisateur</a>
        </div>

        <?php if(!empty($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if(!empty($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Nom d'utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['nom']); ?></td>
                        <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo strtolower($user['role']); ?>">
                                <?php 
                                    switch($user['role']) {
                                        case 'admin':
                                            echo 'Administrateur';
                                            break;
                                        case 'Officier':
                                            echo 'Officier';
                                            break;
                                        default:
                                            echo htmlspecialchars($user['role']);
                                    }
                                ?>
                            </span>
                        </td>
                        <td>
                            <span class="<?php echo $user['actif'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $user['actif'] ? 'Actif' : 'Inactif'; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="Modifier_utilisateur?id=<?php echo $user['id']; ?>" class="edit-btn">Modifier</a>
                            <?php if($user['id'] != $_SESSION['id']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirmDelete();">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="delete-btn">Désactiver</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function confirmDelete() {
            return confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ?');
        }
    </script>
</body>
</html>
<?php
// Fermer la connexion
$conn->close();
?>