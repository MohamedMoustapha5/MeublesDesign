<?php
session_start();
require 'db.php';
$message_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $sujet = htmlspecialchars($_POST['sujet']);
    $contenu = htmlspecialchars($_POST['contenu']);

    $ins = $pdo->prepare("INSERT INTO messages (nom, email, sujet, contenu) VALUES (?, ?, ?, ?)");
    if ($ins->execute([$nom, $email, $sujet, $contenu])) {
        $message_status = "Votre message a été envoyé avec succès !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactez-nous - MeublesDesign</title>
    <link rel="stylesheet" href="/Meubless/style.css">
</head>
<body>
    <header class="navbar">
        <h1>MeublesDesign</h1>
        <nav><a href="index.php">Accueil</a></nav>
    </header>

    <div class="container" style="margin-top: 120px; max-width: 600px;">
        <h2>Contactez le support</h2>
        <?php if($message_status): ?>
            <p style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px;"><?php echo $message_status; ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="nom" placeholder="Votre nom" required>
            <input type="email" name="email" placeholder="Votre email" required>
            <input type="text" name="sujet" placeholder="Sujet de votre message" required>
            <textarea name="contenu" placeholder="Comment pouvons-nous vous aider ?" style="width:100%; height:150px; margin:10px 0; padding:10px; border-radius:6px; border:1px solid #ddd;"></textarea>
            <button type="submit" class="btn-send-message">
                <i class="fas fa-paper-plane"></i> Envoyer le message
            </button>
        </form>
    </div>
</body>
</html>