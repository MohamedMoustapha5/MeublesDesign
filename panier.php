<?php
session_start();
require 'db.php';

// 1. Ajouter un produit au panier
if (isset($_GET['add'])) {
    $id_produit = $_GET['add'];
    
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = array();
    }

    if (isset($_SESSION['panier'][$id_produit])) {
        $_SESSION['panier'][$id_produit]++;
    } else {
        $_SESSION['panier'][$id_produit] = 1;
    }
    header("Location: panier.php");
    exit();
}

// 2. Supprimer un produit
if (isset($_GET['remove'])) {
    unset($_SESSION['panier'][$_GET['remove']]);
    header("Location: panier.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier - MeublesDesign</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="display: block; padding-top: 100px;"> 
    <header class="navbar">
        <h1><i class="fas fa-couch"></i> MeublesDesign</h1>
        <nav>
            <a href="index.php"><i class="fas fa-home"></i> Accueil</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="mes_commandes.php"><i class="fas fa-box"></i> Mes Commandes</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
            <?php else: ?>
                <a href="connexion.php">Connexion</a>
                <a href="signup.php">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <h2><i class="fas fa-shopping-basket"></i> Votre Panier</h2>

        <?php if (empty($_SESSION['panier'])): ?>
            <div style="text-align: center; padding: 50px;">
                <i class="fas fa-shopping-cart fa-4x" style="color: #ddd;"></i>
                <p>Votre panier est vide.</p>
                <a href="index.php" class="btn-valider" style="display: inline-block; width: auto;">Continuer mes achats</a>
            </div>
        <?php else: ?>
            <table style="width:100%; background:white; border-radius:10px; padding:20px; box-shadow:0 5px 15px rgba(0,0,0,0.05);">
                <thead>
                    <tr style="text-align:left; border-bottom:2px solid #eee;">
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Quantité</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_general = 0;
                    foreach ($_SESSION['panier'] as $id => $quantite): 
                        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                        $stmt->execute([$id]);
                        $p = $stmt->fetch();
                        if (!$p) continue;
                        $sous_total = $p['prix'] * $quantite;
                        $total_general += $sous_total;
                    ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <img src="<?php echo $p['image']; ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                <?php echo htmlspecialchars($p['nom']); ?>
                            </div>
                        </td>
                        <td><?php echo number_format($p['prix'], 2); ?> €</td>
                        <td><?php echo $quantite; ?></td>
                        <td><strong><?php echo number_format($sous_total, 2); ?> €</strong></td>
                        <td><a href="panier.php?remove=<?php echo $id; ?>" style="color:red;"><i class="fas fa-trash"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="text-align:right; margin-top:20px;">
                <h3>Total : <?php echo number_format($total_general, 2); ?> €</h3>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="checkout.php" class="btn-valider" style="display: inline-block; width: auto; padding: 15px 40px;">
                        <i class="fas fa-credit-card"></i> Procéder au paiement
                    </a>
                <?php else: ?>
                    <p style="color: red;">Veuillez vous <a href="connexion.php">connecter</a> pour payer</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>