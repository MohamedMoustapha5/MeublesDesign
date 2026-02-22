<?php
session_start();
require 'db.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Sécurité max

    // Vérifier si l'email existe déjà
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        $error = "Cet email est déjà utilisé.";
    } else {
        $ins = $pdo->prepare("INSERT INTO users (nom, email, password, role) VALUES (?, ?, ?, 'client')");
        if ($ins->execute([$nom, $email, $password])) {
            header("Location: connexion.php?success=1");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte - MeublesDesign</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form method="POST">
        <h2>Inscription</h2>
        <?php if($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <input type="text" name="nom" placeholder="Nom complet" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        
        <button type="submit">S'inscrire</button>
        <p>Déjà membre ? <a href="connexion.php">Se connecter</a></p>
        <p><a href="index.php">Retour à la boutique</a></p>
    </form>
</body>
</html>