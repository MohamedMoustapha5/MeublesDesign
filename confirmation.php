<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$stripe_session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;
$order_id = null;

// Vérifier si le panier n'est pas vide
if (!empty($_SESSION['panier'])) {
    try {
        $pdo->beginTransaction();

        $user_id = $_SESSION['user_id'];
        $total_price = 0;

        // 1. Calculer le total et préparer les détails
        $panier_details = [];
        foreach ($_SESSION['panier'] as $id => $quantite) {
            $stmt = $pdo->prepare("SELECT prix FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $prix = $stmt->fetchColumn();
            $total_price += $prix * $quantite;
            $panier_details[] = [
                'product_id' => $id,
                'quantite' => $quantite,
                'prix' => $prix
            ];
        }

        // 2. Créer la commande
        $ins_order = $pdo->prepare("INSERT INTO orders (user_id, total_price, status, created_at, stripe_session_id) VALUES (?, ?, 'en_attente', NOW(), ?)");
        $ins_order->execute([$user_id, $total_price, $stripe_session_id]);
        $order_id = $pdo->lastInsertId();

        // 3. Enregistrer les détails
        if (!empty($panier_details)) {
            $ins_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            
            foreach ($panier_details as $item) {
                $ins_item->execute([
                    $order_id,
                    $item['product_id'],
                    $item['quantite'],
                    $item['prix']
                ]);
                
                // Mettre à jour le stock
                $upd_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $upd_stock->execute([$item['quantite'], $item['product_id']]);
            }
        }

        $pdo->commit();
        
        // 4. Vider le panier
        $_SESSION['panier'] = array();
        $_SESSION['last_order'] = $order_id;
        
        // Redirection vers mes_commandes avec message de succès
        $_SESSION['success'] = "Commande #$order_id enregistrée avec succès !";
        header("Location: mes_commandes.php");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur lors de l'enregistrement de la commande : " . $e->getMessage());
    }
} else {
    // Panier vide, rediriger
    header("Location: panier.php");
    exit();
}
?>