<?php
session_start();
require 'db.php';

$error = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        // Vérifier dans la base de données
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nom'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirection vers la page d'accueil
            header('Location: index.php');
            exit();
        } else {
            $error = "Email ou mot de passe incorrect";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeublesDesign | Connexion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* MÊMES COULEURS QUE LA PAGE D'INSCRIPTION */
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
        
        .login-container {
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
        
        h2 i {
            color: #8d6e63;
            margin-right: 10px;
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
            border-left: 4px solid #dc3545;
        }
        
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #28a745;
        }
        
        .info-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
            color: #555;
            border-left: 4px solid #8d6e63;
        }
        
        .info-box i {
            color: #5d4037;
            margin-right: 8px;
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
        }
        
        .links a {
            color: #8d6e63;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .links a:hover {
            color: #5d4037;
            text-decoration: underline;
        }
        
        .back-home {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #999;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .back-home:hover {
            color: #8d6e63;
        }
        
        .back-home i {
            margin-right: 5px;
        }
        
        .forgot-password {
            text-align: right;
            margin-top: 5px;
        }
        
        .forgot-password a {
            color: #999;
            font-size: 13px;
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            color: #8d6e63;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2><i class="fas fa-lock"></i> Connexion</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <i class="fas fa-info-circle"></i> 
            <strong>Utilisateurs :</strong><br>
            • Email  / Mot de passe<br>
            • Utilisez vos identifiants habituels
        </div>
        
        <form method="POST" action="">
            <div class="input-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required placeholder="">
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-lock"></i> Mot de passe</label>
                <input type="password" name="password" required placeholder="">
                <div class="forgot-password">
                    <a href="#"><i class="fas fa-key"></i> Mot de passe oublié ?</a>
                </div>
            </div>
            
            <button type="submit">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>
        
        <div class="links">
            <a href="signup.php"><i class="fas fa-user-plus"></i> Pas encore de compte ? Créer un compte</a>
        </div>
        
        <a href="index.php" class="back-home">
            <i class="fas fa-home"></i> Retour à l'accueil
        </a>
    </div>
</body>
</html>