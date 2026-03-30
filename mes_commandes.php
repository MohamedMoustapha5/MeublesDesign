<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les commandes avec le nombre d'articles
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) as nb_articles 
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ? 
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$mes_commandes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Commandes - MeublesDesign</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .statut-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .statut-en_attente { background: #fff3cd; color: #856404; }
        .statut-Payé { background: #d4edda; color: #155724; }
        .statut-expediee { background: #cce5ff; color: #004085; }
        .statut-livree { background: #d1e7dd; color: #0a3622; }
        
        .btn-telecharger {
            background: #2ecc71;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-telecharger:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }
        .btn-voir {
            background: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-voir:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: flex-end;
        }
    </style>
</head>
<body style="display: block; padding-top: 100px;">

    <header class="navbar">
        <h1><i class="fas fa-couch"></i> MeublesDesign</h1>
        <nav>
            <a href="index.php"><i class="fas fa-home"></i> Boutique</a>
            <a href="panier.php"><i class="fas fa-shopping-basket"></i> Panier</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </header>

    <main class="container">
        <h2><i class="fas fa-box"></i> Mes commandes</h2>

        <?php if (empty($mes_commandes)): ?>
            <div style="text-align: center; padding: 60px; background: white; border-radius: 15px;">
                <i class="fas fa-box-open fa-4x" style="color: #ddd; margin-bottom: 20px;"></i>
                <p>Vous n'avez pas encore passé de commande.</p>
                <a href="index.php" class="btn-valider" style="display: inline-block; width: auto; margin-top: 20px;">
                    Découvrir nos meubles
                </a>
            </div>
        <?php else: ?>
            <div class="commandes-list">
                <?php foreach ($mes_commandes as $c): ?>
                    <div style="background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                            <div>
                                <strong style="font-size: 18px;">Commande #<?php echo $c['id']; ?></strong>
                                <span style="color: #666; margin-left: 15px;">
                                    <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($c['created_at'])); ?>
                                </span>
                            </div>
                            <span class="statut-badge statut-<?php echo $c['status']; ?>">
                                <?php 
                                if($c['status'] == 'en_attente') echo 'En attente de validation';
                                elseif($c['status'] == 'Payé') echo 'Paiement accepté';
                                elseif($c['status'] == 'expediee') echo 'Expédiée';
                                elseif($c['status'] == 'livree') echo 'Livrée';
                                else echo $c['status'];
                                ?>
                            </span>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; text-align: center;">
                            <div>
                                <div style="color: #666; font-size: 13px;">Total</div>
                                <div style="font-weight: 700; color: var(--primary-color);"><?php echo number_format($c['total_price'] * 655.96, 0); ?> FCFA</div>
                            </div>
                            <div>
                                <div style="color: #666; font-size: 13px;">Articles</div>
                                <div style="font-weight: 700;"><?php echo $c['nb_articles']; ?></div>
                            </div>
                            <div>
                                <div style="color: #666; font-size: 13px;">Statut</div>
                                <div>
                                    <?php 
                                    if($c['status'] == 'en_attente') echo '<i class="fas fa-clock" style="color: #856404;"></i> En cours';
                                    elseif($c['status'] == 'Payé') echo '<i class="fas fa-check" style="color: #155724;"></i> Confirmée';
                                    elseif($c['status'] == 'expediee') echo '<i class="fas fa-truck" style="color: #004085;"></i> En route';
                                    elseif($c['status'] == 'livree') echo '<i class="fas fa-home" style="color: #0a3622;"></i> Livrée';
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- BOUTONS D'ACTION -->
                        <div class="action-buttons">
                            <?php if($c['status'] == 'Payé' || $c['status'] == 'expediee' || $c['status'] == 'livree'): ?>
                                <a href="generer_recu.php?order_id=<?php echo $c['id']; ?>" class="btn-telecharger" target="_blank">
                                    <i class="fas fa-download"></i> Télécharger reçu
                                </a>
                            <?php endif; ?>
                            <a href="detail_commande.php?id=<?php echo $c['id']; ?>" class="btn-voir">
                                <i class="fas fa-eye"></i> Voir détail
                            </a>
                        </div>
                        
                        <?php if($c['status'] == 'en_attente'): ?>
                            <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-radius: 8px; font-size: 13px; color: #856404;">
                                <i class="fas fa-info-circle"></i> Votre commande est en attente de validation par nos équipes. Vous recevrez un email dès qu'elle sera confirmée.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer style="text-align: center; padding: 40px; color: #aaa;">
        <p>&copy; 2026 MeublesDesign - Tous droits réservés</p>
    </footer>

</body>
</html>