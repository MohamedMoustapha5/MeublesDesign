<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: mes_commandes.php");
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Récupérer la commande
$stmt = $pdo->prepare("
    SELECT o.*, u.nom as client_nom, u.email 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: mes_commandes.php");
    exit();
}

// Récupérer les détails de la commande
$stmt = $pdo->prepare("
    SELECT oi.*, p.nom as product_name, p.image 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}
$total_fcfa = $total * 655.96;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail Commande #<?php echo $order_id; ?> - MeublesDesign</title>
    <link rel="stylesheet" href="/Meubless/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .detail-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #5d4037;
        }
        .detail-header h1 {
            color: #5d4037;
            font-size: 24px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
        }
        .status-en_attente { background: #fff3cd; color: #856404; }
        .status-Payé { background: #d4edda; color: #155724; }
        .status-expediee { background: #cce5ff; color: #004085; }
        .status-livree { background: #d1e7dd; color: #0a3622; }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 15px;
        }
        .info-box h3 {
            color: #5d4037;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }
        .info-line {
            margin: 10px 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
            color: #666;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .products-table th {
            background: #5d4037;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .products-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .total-section {
            text-align: right;
            margin-top: 20px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 15px;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #5d4037;
        }
        
        .btn-retour {
            display: inline-block;
            background: #5d4037;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
        }
        .btn-retour:hover {
            background: #8d6e63;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .detail-container {
                padding: 20px;
            }
            .products-table {
                font-size: 12px;
            }
            .products-table th, .products-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body style="padding-top: 100px;">

    <header class="navbar">
        <h1><i class="fas fa-couch"></i> MeublesDesign</h1>
        <nav>
            <a href="index.php"><i class="fas fa-home"></i> Accueil</a>
            <a href="mes_commandes.php"><i class="fas fa-box"></i> Mes commandes</a>
            <a href="panier.php"><i class="fas fa-shopping-basket"></i> Panier</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </header>

    <main class="container">
        <div class="detail-container">
            <div class="detail-header">
                <h1><i class="fas fa-file-invoice"></i> Détail de la commande #<?php echo $order_id; ?></h1>
                <span class="status-badge status-<?php echo $order['status']; ?>">
                    <?php 
                    if($order['status'] == 'en_attente') echo 'En attente de validation';
                    elseif($order['status'] == 'Payé') echo 'Paiement accepté';
                    elseif($order['status'] == 'expediee') echo 'Expédiée';
                    elseif($order['status'] == 'livree') echo 'Livrée';
                    else echo $order['status'];
                    ?>
                </span>
            </div>

            <div class="info-grid">
                <div class="info-box">
                    <h3><i class="fas fa-info-circle"></i> Informations commande</h3>
                    <div class="info-line"><span class="info-label">N° Commande :</span> #<?php echo $order['id']; ?></div>
                    <div class="info-line"><span class="info-label">Date :</span> <?php echo date('d/m/Y à H:i', strtotime($order['created_at'])); ?></div>
                    <div class="info-line"><span class="info-label">Mode paiement :</span> Stripe (Carte bancaire)</div>
                    <div class="info-line"><span class="info-label">Statut :</span> <?php echo ucfirst($order['status']); ?></div>
                </div>
                <div class="info-box">
                    <h3><i class="fas fa-user"></i> Informations client</h3>
                    <div class="info-line"><span class="info-label">Nom :</span> <?php echo htmlspecialchars($order['client_nom']); ?></div>
                    <div class="info-line"><span class="info-label">Email :</span> <?php echo htmlspecialchars($order['email']); ?></div>
                </div>
            </div>

            <h3><i class="fas fa-boxes"></i> Produits commandés</h3>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Produit</th>
                        <th>Prix unitaire</th>
                        <th>Quantité</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): 
                        $prix_unitaire = $item['price'] * 655.96;
                        $total_item = $prix_unitaire * $item['quantity'];
                    ?>
                    <tr>
                        <td><img src="<?php echo $item['image']; ?>" class="product-img" alt=""></td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo number_format($prix_unitaire, 0); ?> FCFA</td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><strong><?php echo number_format($total_item, 0); ?> FCFA</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-section">
                <p>Sous-total : <?php echo number_format($total_fcfa, 0); ?> FCFA</p>
                <p>Livraison : <strong>OFFERTE</strong></p>
                <p class="total-amount">Total : <?php echo number_format($total_fcfa, 0); ?> FCFA</p>
            </div>

            <div style="text-align: center;">
                <a href="generer_recu.php?order_id=<?php echo $order_id; ?>" class="btn-valider" style="margin-right: 15px;">
                    <i class="fas fa-download"></i> Télécharger le reçu
                </a>
                <a href="mes_commandes.php" class="btn-retour">
                    <i class="fas fa-arrow-left"></i> Retour à mes commandes
                </a>
            </div>
        </div>
    </main>

    <footer style="text-align: center; padding: 40px; margin-top: 40px; color: #aaa;">
        <p>&copy; 2026 MeublesDesign - Tous droits réservés</p>
    </footer>

</body>
</html>