<?php
session_start();
require 'db.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: connexion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['statut'])) {
    $order_id = $_POST['order_id'];
    $statut = $_POST['statut'];
    
    // Liste des statuts autorisés
    $statuts_autorises = ['en_attente', 'Payé', 'expediee', 'livree', 'annulee'];
    
    if (in_array($statut, $statuts_autorises)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$statut, $order_id])) {
            $_SESSION['admin_success'] = "Statut de la commande #$order_id mis à jour avec succès.";
        } else {
            $_SESSION['admin_error'] = "Erreur lors de la mise à jour.";
        }
    }
}

header("Location: admin_dashboard.php");
exit();