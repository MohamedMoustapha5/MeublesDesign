<?php
session_start();
require 'db.php';
// LOGIQUE DE SUPPRESSION
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // 1. On récupère le chemin de l'image pour la supprimer du dossier uploads
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists($img)) {
        unlink($img); // Supprime le fichier image
    }

    // 2. On supprime l'entrée en base de données
    $delete = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($delete->execute([$id])) {
        header("Location: admin_dashboard.php?msg=deleted");
        exit();
    }
}

// 1. SÉCURITÉ : Vérification de l'accès Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: connexion.php");
    exit();
}

$message_success = "";

// 2. LOGIQUE : Ajout d'un meuble
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_meuble'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $prix_fcfa = $_POST['prix']; // Prix saisi en FCFA
    $prix = $prix_fcfa / 655.96; // Conversion en Euro pour la BDD
    $description = htmlspecialchars($_POST['description']);
    $stock = $_POST['stock'];
    $categorie = $_POST['categorie'];

    // Gestion de l'image
    $target_dir = "uploads/";
    $image_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
 
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $ins = $pdo->prepare("INSERT INTO products (nom, description, prix, image, stock, categorie) VALUES (?, ?, ?, ?, ?, ?)");
        if ($ins->execute([$nom, $description, $prix, $target_file, $stock, $categorie])) {
            $message_success = "Meuble ajouté avec succès !";
        }
    }
}

// 3. RÉCUPÉRATION DES DONNÉES
// Liste des meubles
$stmt_products = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$all_products = $stmt_products->fetchAll();

// Liste des commandes avec le nom du client
$stmt_orders = $pdo->query("SELECT orders.*, users.nom as client_nom FROM orders 
                            JOIN users ON orders.user_id = users.id 
                            ORDER BY orders.created_at DESC");
$orders = $stmt_orders->fetchAll();

// Compteur de messages non lus
$stmt_count_msg = $pdo->query("SELECT COUNT(*) FROM messages WHERE lu = 0");
$unread_msg = $stmt_count_msg->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - MeublesDesign</title>
    <link rel="stylesheet" href="/Meubless/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-main { padding-top: 100px; display: flex; flex-direction: column; gap: 40px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .stat-card i { font-size: 24px; color: var(--primary-color); margin-bottom: 10px; }
        .admin-section { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .badge-unread { background: #d32f2f; color: white; padding: 2px 7px; border-radius: 50%; font-size: 12px; }
    </style>
</head>
<body style="display: block;">

    <header class="navbar">
        <h1><i class="fas fa-tools"></i> Espace Admin</h1>
        <nav>
            <a href="index.php">Boutique</a>
            <a href="admin_messages.php">Messages 
                <?php if($unread_msg > 0): ?><span class="badge-unread"><?php echo $unread_msg; ?></span><?php endif; ?>
            </a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
        </nav>
    </header>

    <main class="container admin-main">

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-couch"></i>
                <p>Produits</p>
                <h3><?php echo count($all_products); ?></h3>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-bag"></i>
                <p>Commandes</p>
                <h3><?php echo count($orders); ?></h3>
            </div>
            <div class="stat-card">
                <i class="fas fa-envelope"></i>
                <p>Nouveaux Messages</p>
                <h3><?php echo $unread_msg; ?></h3>
            </div>
        </div>

        <section class="admin-section">
            <h2><i class="fas fa-plus-circle"></i> Ajouter un Meuble</h2>
            <?php if($message_success): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $message_success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" style="max-width: 100%; box-shadow: none; padding: 0;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <input type="text" name="nom" placeholder="Nom du meuble" required>
                    <select name="categorie" style="width:100%; padding:10px; margin:10px 0; border-radius:6px; border:1px solid #ddd;">
                        <option value="Salon">Salon</option>
                        <option value="Chambre">Chambre</option>
                        <option value="Bureau">Bureau</option>
                        <option value="Cuisine">Cuisine</option>
                    </select>
                   <input type="number" step="1" name="prix" placeholder="Prix (FCFA)" required>
                    <input type="number" name="stock" placeholder="Stock initial" required>
                </div>
                <textarea name="description" placeholder="Description complète..." style="width:100%; height:80px; margin:10px 0; padding:10px; border-radius:6px; border:1px solid #ddd;"></textarea>
                <label>Image du produit :</label>
                <input type="file" name="image" accept="image/*" required>
                <button type="submit" name="ajouter_meuble" class="btn-add-product">
                    <i class="fas fa-upload"></i> Mettre en ligne
                </button>
            </form>
        </section>

       <section class="admin-section">
    <h2><i class="fas fa-boxes"></i> Gestion du Stock</h2>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Prix</th>
                <th>Stock</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($all_products as $p): ?>
            <tr>
                <td><img src="<?php echo $p['image']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                <td><strong><?php echo htmlspecialchars($p['nom']); ?></strong></td>
                <td><span class="badge"><?php echo htmlspecialchars($p['categorie']); ?></span></td>
             <td><?php echo number_format($p['prix'] * 655.96, 0); ?> FCFA</td>
                <td>
                    <?php if($p['stock'] <= 5): ?>
                        <span style="color: red; font-weight: bold;"><i class="fas fa-exclamation-triangle"></i> <?php echo $p['stock']; ?></span>
                    <?php else: ?>
                        <?php echo $p['stock']; ?>
                    <?php endif; ?>
                </td>
                <td style="text-align: right;">
                    <a href="admin_edit.php?id=<?php echo $p['id']; ?>" class="btn-action-edit" title="Modifier">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    
                    <a href="admin_dashboard.php?delete=<?php echo $p['id']; ?>" 
                       class="btn-action-delete" 
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')" 
                       title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<!-- ===== SECTION GESTION DES COMMANDES ===== -->
<section class="admin-section" style="margin-top: 30px;">
    <h2><i class="fas fa-shopping-cart"></i> Gestion des Commandes</h2>
    
    <?php if(isset($_SESSION['admin_success'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['admin_success']; unset($_SESSION['admin_success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['admin_error'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
        </div>
    <?php endif; ?>
    
    <?php
    // Récupérer les commandes avec le nom du client
    $stmt_orders = $pdo->query("SELECT orders.*, users.nom as client_nom 
                                FROM orders 
                                JOIN users ON orders.user_id = users.id 
                                ORDER BY orders.created_at DESC");
    $orders = $stmt_orders->fetchAll();
    ?>
    
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="background: var(--primary-color); color: white;">
                <th style="padding: 12px;">N° Commande</th>
                <th style="padding: 12px;">Client</th>
                <th style="padding: 12px;">Date</th>
                <th style="padding: 12px;">Total (FCFA)</th>
                <th style="padding: 12px;">Statut</th>
                <th style="padding: 12px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($orders) > 0): ?>
                <?php foreach($orders as $c): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;"><strong>#<?php echo $c['id']; ?></strong></td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($c['client_nom']); ?></td>
                    <td style="padding: 12px;"><?php echo date('d/m/Y', strtotime($c['created_at'])); ?></td>
                    <td style="padding: 12px;"><strong><?php echo number_format($c['total_price'] * 655.96, 0); ?> FCFA</strong></td>
                    <td style="padding: 12px;">
                        <span style="padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; 
                            <?php 
                            if($c['status'] == 'en_attente') echo 'background: #fff3cd; color: #856404;';
                            elseif($c['status'] == 'Payé') echo 'background: #d4edda; color: #155724;';
                            elseif($c['status'] == 'expediee') echo 'background: #cce5ff; color: #004085;';
                            elseif($c['status'] == 'livree') echo 'background: #d1e7dd; color: #0a3622;';
                            else echo 'background: #f8f7da; color: #856404;';
                            ?>">
                            <?php 
                            if($c['status'] == 'en_attente') echo 'En attente';
                            elseif($c['status'] == 'Payé') echo 'Payé';
                            elseif($c['status'] == 'expediee') echo 'Expédiée';
                            elseif($c['status'] == 'livree') echo 'Livrée';
                            else echo 'En attente';
                            ?>
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        <form method="POST" action="admin_update_order.php" style="display: flex; gap: 5px;">
                            <input type="hidden" name="order_id" value="<?php echo $c['id']; ?>">
                            <select name="statut" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                                <option value="en_attente" <?php echo $c['status'] == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                                <option value="Payé" <?php echo $c['status'] == 'Payé' ? 'selected' : ''; ?>>Payé</option>
                                <option value="expediee" <?php echo $c['status'] == 'expediee' ? 'selected' : ''; ?>>Expédiée</option>
                                <option value="livree" <?php echo $c['status'] == 'livree' ? 'selected' : ''; ?>>Livrée</option>
                            </select>
                            <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;">
                                <i class="fas fa-save"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">Aucune commande pour le moment</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

    </main>

    <footer style="text-align: center; padding: 40px; color: #888;">
        &copy; 2026 MeublesDesign Admin Panel
    </footer>

</body>
</html>