<?php
session_start();
require 'db.php';

// Sécurité Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: connexion.php");
    exit();
}

// 1. Chiffre d'affaires total et nombre de commandes
$stats_query = $pdo->query("SELECT SUM(total_price) as total_ca, COUNT(id) as total_orders FROM orders WHERE status = 'Payé'");
$global_stats = $stats_query->fetch();

// 2. Produits les plus vendus (Top 5)
// Note : Cette requête nécessite que tu aies une table 'order_items' ou similaire. 
// Si tu n'as pas de table de liaison, on peut simuler avec les produits ayant le moins de stock (les plus achetés).
$top_products = $pdo->query("SELECT nom, stock, prix FROM products ORDER BY stock ASC LIMIT 5")->fetchAll();

// 3. Répartition par catégorie
$cat_stats = $pdo->query("SELECT categorie, COUNT(*) as nb FROM products GROUP BY categorie")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques - MeublesDesign</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; }
        .stat-card i { font-size: 40px; color: var(--primary-color); margin-bottom: 15px; }
        .stat-number { font-size: 28px; font-weight: bold; display: block; margin-top: 10px; }
        .table-stats { width: 100%; background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <header class="navbar">
        <h1><i class="fas fa-chart-line"></i> Dashboard Business</h1>
        <nav>
            <a href="admin_dashboard.php">Boutique</a>
            <a href="admin_messages.php">Messages</a>
            <a href="admin_stats.php" class="active">Stats</a>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>

    <main class="container">
        <h2 class="section-title">Performances de la boutique</h2>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-euro-sign"></i>
                <p>Chiffre d'Affaires</p>
                <span class="stat-number"><?php echo number_format($global_stats['total_ca'], 2); ?> €</span>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-bag"></i>
                <p>Commandes Validées</p>
                <span class="stat-number"><?php echo $global_stats['total_orders']; ?></span>
            </div>
            <div class="stat-card">
                <i class="fas fa-box-open"></i>
                <p>Articles en Stock</p>
                <?php 
                    $total_stock = $pdo->query("SELECT SUM(stock) FROM products")->fetchColumn();
                ?>
                <span class="stat-number"><?php echo $total_stock; ?></span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div class="table-stats">
                <h3><i class="fas fa-star"></i> Alerte Stock Bas (Potentiels Top Ventes)</h3>
                <table style="width: 100%; margin-top: 15px; text-align: left;">
                    <tr style="border-bottom: 1px solid #eee;">
                        <th>Meuble</th>
                        <th>Restant</th>
                        <th>Prix</th>
                    </tr>
                    <?php foreach($top_products as $tp): ?>
                    <tr>
                        <td><?php echo $tp['nom']; ?></td>
                        <td style="color: <?php echo $tp['stock'] < 5 ? 'red' : 'green'; ?>; font-weight: bold;">
                            <?php echo $tp['stock']; ?>
                        </td>
                        <td><?php echo $tp['prix']; ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div class="table-stats">
                <h3><i class="fas fa-pie-chart"></i> Inventaire par Catégorie</h3>
                <ul style="list-style: none; padding: 0; margin-top: 15px;">
                    <?php foreach($cat_stats as $cs): ?>
                    <li style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f9f9f9;">
                        <span><?php echo $cs['categorie']; ?></span>
                        <span class="badge" style="background: var(--primary-color); color: white; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center;">
                            <?php echo $cs['nb']; ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </main>
</body>
</html>