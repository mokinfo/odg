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

$error = '';
$success = '';
$user_data = null;

// Vérifier si un ID d'utilisateur est fourni
if(isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $id = trim($_GET['id']);
    
    // Récupérer les informations de l'utilisateur
    $sql = "SELECT * FROM utilisateurs WHERE id = ?";
    if($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if($stmt->execute()) {
            $result = $stmt->get_result();
            if($result->num_rows == 1) {
                $user_data = $result->fetch_assoc();
            } else {
                header("location: error.php");
                exit();
            }
        }
        $stmt->close();
    }
}

// Traitement du formulaire de modification
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $username = trim($_POST["username"]);
    $role = trim($_POST["role"]);
    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenom"]);
    $email = trim($_POST["email"]);
    $actif = isset($_POST["actif"]) ? 1 : 0;
    
    // Validation des données
    if(empty($username) || empty($role) || empty($nom) || empty($prenom) || empty($email)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Vérifier si le nom d'utilisateur existe déjà (sauf pour l'utilisateur actuel)
        $sql = "SELECT id FROM utilisateurs WHERE username = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $error = "Ce nom d'utilisateur existe déjà.";
        } else {
            // Mise à jour des informations de l'utilisateur
            $sql = "UPDATE utilisateurs SET username = ?, role = ?, nom = ?, prenom = ?, email = ?, actif = ? WHERE id = ?";
            
            if($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sssssii", $username, $role, $nom, $prenom, $email, $actif, $id);
                
                if($stmt->execute()) {
                    $success = "Les modifications ont été enregistrées avec succès.";
                    
                    // Si un nouveau mot de passe est fourni
                    if(!empty(trim($_POST["new_password"]))) {
                        $new_password = trim($_POST["new_password"]);
                        if(strlen($new_password) >= 6) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $sql = "UPDATE utilisateurs SET password = ? WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("si", $hashed_password, $id);
                            $stmt->execute();
                            $success .= " Le mot de passe a été mis à jour.";
                        } else {
                            $error = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
                        }
                    }
                    
                    // Recharger les données de l'utilisateur
                    $sql = "SELECT * FROM utilisateurs WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user_data = $result->fetch_assoc();
                } else {
                    $error = "Une erreur est survenue lors de la modification.";
                }
            }
        }
    }
}

// Si aucun utilisateur n'est trouvé
if($user_data === null) {
    header("location: error.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'utilisateur - Gestion des Urgences</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="password"],
        input[type="email"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
        .password-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Modifier l'utilisateur</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="id" value="<?php echo $user_data['id']; ?>">
            
            <div class="form-group">
                <label for="username">Nom d'utilisateur*</label>
                <input type="text" id="username" name="username" value="<?php echo $user_data['username']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="role">Rôle*</label>
                <select id="role" name="role" required>
                    <option value="admin" <?php echo ($user_data['role'] == 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                    <option value="officier" <?php echo ($user_data['role'] == 'officier') ? 'selected' : ''; ?>>Officier</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="nom">Nom*</label>
                <input type="text" id="nom" name="nom" value="<?php echo $user_data['nom']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom*</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo $user_data['prenom']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email*</label>
                <input type="email" id="email" name="email" value="<?php echo $user_data['email']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="actif" <?php echo $user_data['actif'] ? 'checked' : ''; ?>>
                    Compte actif
                </label>
            </div>
            
            <div class="password-section">
                <h3>Changer le mot de passe</h3>
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe (laisser vide pour ne pas modifier)</label>
                    <input type="password" id="new_password" name="new_password">
                    <small>Minimum 6 caractères</small>
                </div>
            </div>
            
            <button type="submit">Enregistrer les modifications</button>
            <a href="Gestion" style="margin-left: 10px;">Retour à la liste</a>
        </form>
    </div>
</body>
</html>
