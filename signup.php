<?php
session_start();
require 'db.php';

$error = '';
$success = '';

// Vous pouvez changer cette valeur pour modifier la longueur minimale
$min_password_length = 6; // Mettez 2 si vous voulez 2 caractères minimum

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validations
    if (empty($nom) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Tous les champs sont obligatoires";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } elseif (strlen($password) < $min_password_length) {
        $error = "Le mot de passe doit contenir au moins $min_password_length caractères";
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $password)) {
        $error = "Le mot de passe ne peut contenir que des lettres et des chiffres (pas de caractères spéciaux)";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = "Cet email est déjà utilisé";
        } else {
            // Hash du mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insérer l'utilisateur
            $stmt = $pdo->prepare("INSERT INTO users (nom, email, password, role) VALUES (?, ?, ?, 'client')");
            
            if ($stmt->execute([$nom, $email, $hashed_password])) {
                $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                header("refresh:2;url=connexion.php");
            } else {
                $error = "Une erreur est survenue lors de l'inscription";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeublesDesign | Inscription</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f4f1ea 0%, #e8e2d9 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .signup-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            padding: 40px;
        }
        
        h2 {
            text-align: center;
            color: #5d4037;
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .input-group input:focus {
            border-color: #8d6e63;
            outline: none;
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: #5d4037;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        
        button:hover {
            background: #8d6e63;
        }
        
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .password-rule {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
            color: #555;
            border-left: 4px solid #8d6e63;
        }
        
        .password-rule i {
            color: #5d4037;
            margin-right: 8px;
        }
        
        .password-rule ul {
            margin-top: 10px;
            margin-left: 25px;
        }
        
        .password-rule li {
            margin: 5px 0;
            color: #666;
        }
        
        .password-rule .valid {
            color: #28a745;
        }
        
        .password-rule .invalid {
            color: #dc3545;
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
        }
        
        .links a {
            color: #8d6e63;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-home {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #999;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2><i class="fas fa-user-plus"></i> Inscription</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="success-msg">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <div class="password-rule">
            <i class="fas fa-info-circle"></i> 
            <strong>Règles du mot de passe :</strong>
            <ul>
                <li>✓ Minimum <?php echo $min_password_length; ?> caractères</li>
                <li>✓ Uniquement des lettres (a-z, A-Z)</li>
                <li>✓ Uniquement des chiffres (0-9)</li>
                <li>✓ Ou un mélange de lettres et chiffres</li>
                <li>✗ Pas de caractères spéciaux (!@#$%^&*)</li>
            </ul>
            <p style="margin-top:10px; font-style:italic; color:#8d6e63;">
                Exemples: "123456", "abcdef", "abc123", "AZERTY", "pass42"
            </p>
        </div>
        
        <form method="POST" action="">
            <div class="input-group">
                <label><i class="fas fa-user"></i> Nom complet</label>
                <input type="text" name="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-lock"></i> Mot de passe</label>
                <input type="password" name="password" required placeholder="Minimum <?php echo $min_password_length; ?> caractères">
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-lock"></i> Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" required>
            </div>
            
            <button type="submit">
                <i class="fas fa-user-check"></i> S'inscrire
            </button>
        </form>
        
        <div class="links">
            <a href="connexion.php"><i class="fas fa-sign-in-alt"></i> Déjà un compte ? Se connecter</a>
        </div>
        
        <a href="index.php" class="back-home">
            <i class="fas fa-home"></i> Retour à l'accueil
        </a>
    </div>
</body>
</html>