<?php
// Initialisation de la session
session_start();

// Connexion à la base de données
$conn = new mysqli('132.148.183.180', 'a7zq6mn2qjza', 'Sna#2024@!!', 'Officier_de_garde');

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $role = trim($_POST["role"]);
    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenom"]);
    $email = trim($_POST["email"]);

    // Validation des données
    if (empty($username) || empty($password) || empty($role) || empty($nom) || empty($prenom) || empty($email)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        // Vérifier si le nom d'utilisateur existe déjà
        $check_query = "SELECT id FROM utilisateurs WHERE username = ?";
        if ($stmt = $conn->prepare($check_query)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Ce nom d'utilisateur existe déjà.";
            } else {
                // Vérifier si l'email existe déjà
                $check_email = "SELECT id FROM utilisateurs WHERE email = ?";
                if ($stmt_email = $conn->prepare($check_email)) {
                    $stmt_email->bind_param("s", $email);
                    $stmt_email->execute();
                    $result_email = $stmt_email->get_result();
                    
                    if ($result_email->num_rows > 0) {
                        $error = "Cet email est déjà utilisé.";
                    } else {
                        // Préparation de la requête d'insertion
                        $sql = "INSERT INTO utilisateurs (username, password, role, nom, prenom, email) VALUES (?, ?, ?, ?, ?, ?)";
                        
                        if ($stmt_insert = $conn->prepare($sql)) {
                            // Hash du mot de passe
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            
                            // Liaison des paramètres
                            $stmt_insert->bind_param("ssssss", $username, $hashed_password, $role, $nom, $prenom, $email);
                            
                            // Exécution de la requête
                            if ($stmt_insert->execute()) {
                                $success = "L'utilisateur a été créé avec succès.";
                                // Réinitialiser les champs du formulaire
                                $_POST = array();
                            } else {
                                $error = "Une erreur est survenue lors de la création de l'utilisateur: " . $stmt_insert->error;
                            }
                            $stmt_insert->close();
                        } else {
                            $error = "Erreur de préparation de la requête: " . $conn->error;
                        }
                    }
                    $stmt_email->close();
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un utilisateur - Gestion des Urgences</title>
    <style>
        /* Votre CSS reste inchangé */
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Ajouter un nouvel utilisateur</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">Nom d'utilisateur*</label>
                <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe*</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe*</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <label for="role">Rôle*</label>
                <select id="role" name="role" required>
                    <option value="">Sélectionnez un rôle</option>
                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                    <option value="officier" <?php echo (isset($_POST['role']) && $_POST['role'] === 'officier') ? 'selected' : ''; ?>>Officier</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="nom">Nom*</label>
                <input type="text" id="nom" name="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom*</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email*</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <button type="submit">Créer l'utilisateur</button>
            <a href="dashboard.php" style="margin-left: 10px;">Retour au tableau de bord</a>
        </form>
    </div>
</body>
</html>