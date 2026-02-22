<?php
session_start();
require 'db.php';

// 1. SÉCURITÉ : Accès réservé à l'admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: connexion.php");
    exit();
}

// 2. RÉCUPÉRATION DU MESSAGE CONCERNÉ
if (!isset($_GET['id'])) {
    header("Location: admin_messages.php");
    exit();
}

$id_message = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
$stmt->execute([$id_message]);
$msg = $stmt->fetch();

if (!$msg) {
    die("Message introuvable.");
}

$confirmation = "";
$erreur = "";

// 3. TRAITEMENT DE L'ENVOI DU MAIL
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['envoyer_reponse'])) {
    $destinataire = $msg['email'];
    $sujet_reponse = "Réponse de MeublesDesign : " . htmlspecialchars($_POST['sujet']);
    $corps_message = htmlspecialchars($_POST['message_reponse']);
    
    // Configuration des en-têtes (Headers) pour l'email
    $headers = "From: contact@meublesdesign.com\r\n";
    $headers .= "Reply-To: contact@meublesdesign.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Corps du mail en HTML pour un rendu plus pro
    $email_content = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2 style='color: #3498db;'>MeublesDesign</h2>
        <p>Bonjour <strong>{$msg['nom']}</strong>,</p>
        <p>Nous avons bien reçu votre message concernant : <em>'{$msg['sujet']}'</em>.</p>
        <hr>
        <p style='background: #f9f9f9; padding: 15px;'>$corps_message</p>
        <hr>
        <p>Cordialement,<br>L'équipe MeublesDesign</p>
    </body>
    </html>";

    // Envoi réel
    if (@mail($destinataire, $sujet_reponse, $email_content, $headers)) {
        $confirmation = "Votre réponse a été envoyée avec succès à " . htmlspecialchars($destinataire);
    } else {
        $erreur = "L'envoi a échoué. (Note : En local avec Wamp, l'envoi de mail nécessite une configuration SMTP spécifique).";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Répondre au message - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .reponse-container { max-width: 800px; margin: 50px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .original-msg { background: #f1f2f6; padding: 20px; border-radius: 10px; margin-bottom: 30px; border-left: 4px solid #bdc3c7; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input[type="text"], textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; }
        textarea { height: 200px; resize: vertical; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<header class="navbar">
    <h1><i class="fas fa-reply"></i> Espace Réponse</h1>
    <nav>
        <a href="admin_messages.php"><i class="fas fa-arrow-left"></i> Retour aux messages</a>
    </nav>
</header>

<main class="container">
    <div class="reponse-container">
        <h2>Répondre à <?php echo htmlspecialchars($msg['nom']); ?></h2>

        <?php if ($confirmation): ?>
            <div class="alert alert-success"><?php echo $confirmation; ?></div>
            <a href="admin_messages.php" class="btn-valider">Retourner à la liste</a>
        <?php else: ?>

            <?php if ($erreur): ?>
                <div class="alert alert-error"><?php echo $erreur; ?></div>
            <?php endif; ?>

            <div class="original-msg">
                <strong>Rappel du message client :</strong><br>
                <small>Envoyé le : <?php echo date('d/m/Y', strtotime($msg['created_at'])); ?></small>
           <p style="margin-top:10px;">
    <em>"<?php 
        // On vérifie manuellement chaque nom de colonne possible
        $texte_final = "Contenu introuvable";
        
        if (isset($msg['message'])) {
            $texte_final = $msg['message'];
        } elseif (isset($msg['msg'])) {
            $texte_final = $msg['msg'];
        } elseif (isset($msg['contenu'])) {
            $texte_final = $msg['contenu'];
        } elseif (isset($msg['texte'])) {
            $texte_final = $msg['texte'];
        }

        echo nl2br(htmlspecialchars($texte_final)); 
    ?>"</em>
</p>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br>


<p style="margin-top:10px;"><em>"<?php echo nl2br(htmlspecialchars($msg['contenu'])); ?>"</em></p>
            </div>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Sujet de l'email :</label>
                    <input type="text" name="sujet" value="Suite à votre demande" required>
                </div>

                <div class="form-group">
                    <label>Votre message :</label>
                    <textarea name="message_reponse" required placeholder="Tapez votre réponse ici..."></textarea>
                </div>

                <button type="submit" name="envoyer_reponse" class="btn-valider" style="width: 100%;">
                    <i class="fas fa-paper-plane"></i> Envoyer la réponse
                </button>
            </form>
        <?php endif; ?>
    </div>
</main>

</body>
</html>