<?php
session_start();
require 'db.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if(!$p) { header("Location: index.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $p['nom']; ?> - Détails</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="navbar"><h1>MeublesDesign</h1><nav><a href="index.php">Retour</a></nav></header>

    <div class="container" style="display: flex; gap: 50px; background: white; padding: 40px; border-radius: 20px; margin-top: 120px;">
        <div style="flex: 1;">
            <img src="<?php echo $p['image']; ?>" style="width: 100%; border-radius: 15px;">
        </div>
        <div style="flex: 1;">
            <span style="color: var(--secondary-color); font-weight: bold;"><?php echo $p['categorie']; ?></span>
            <h2 style="margin: 10px 0;"><?php echo $p['nom']; ?></h2>
           <p style="font-size: 24px; color: var(--primary-color); font-weight: bold;"><?php echo number_format($p['prix'] * 655.96, 0); ?> FCFA</p>
            <p style="color: #666; line-height: 1.6; margin: 20px 0;"><?php echo $p['description']; ?></p>
            <p>Stock disponible : <strong><?php echo $p['stock']; ?></strong></p>
            
            <a href="panier.php?add=<?php echo $p['id']; ?>" class="btn-valider" style="text-align: center; display: block;">Ajouter au panier</a>
        </div>
    </div>
</body>
</html>