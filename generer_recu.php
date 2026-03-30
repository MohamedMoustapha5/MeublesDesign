<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

if (!isset($_GET['order_id'])) {
    header("Location: mes_commandes.php");
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

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

$stmt = $pdo->prepare("
    SELECT oi.*, p.nom as product_name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Configuration Dompdf
$options = new Options();
$options->set('defaultFont', 'Helvetica');
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Logo (un seul)
$logo_base64 = '';
$logo_path = 'uploads/logo.png';
if (file_exists($logo_path)) {
    $logo_data = base64_encode(file_get_contents($logo_path));
    $logo_base64 = '<img src="data:image/png;base64,' . $logo_data . '" style="width: 100px;">';
}

$total = 0;
foreach ($items as $item) {
    $prix_unitaire = $item['price'] * 655.96;
    $total += $prix_unitaire * $item['quantity'];
}

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reçu officiel - Commande #' . $order['id'] . '</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: "Helvetica", sans-serif;
            margin: 0;
            padding: 0;
            color: #2c3e50;
        }
        
        /* En-tête avec logo et cachet */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #5d4037;
        }
        
        /* Cachet officiel */
        .stamp {
            position: absolute;
            top: 100px;
            right: 50px;
            width: 150px;
            height: 150px;
            border: 3px solid #5d4037;
            border-radius: 50%;
            text-align: center;
            line-height: 150px;
            font-size: 14px;
            font-weight: bold;
            color: #5d4037;
            opacity: 0.7;
            transform: rotate(-15deg);
            font-family: "Times New Roman", serif;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #5d4037;
            margin: 5px 0;
            letter-spacing: 2px;
        }
        
        .company-slogan {
            font-size: 12px;
            color: #8d6e63;
            letter-spacing: 1px;
        }
        
        .document-title {
            font-size: 22px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        /* Informations */
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            padding: 20px;
            background: #f9f6f0;
            border-radius: 10px;
        }
        
        .info-box {
            width: 48%;
        }
        
        .info-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #5d4037;
            border-bottom: 1px solid #5d4037;
            padding-bottom: 5px;
            display: inline-block;
        }
        
        .info-line {
            margin: 8px 0;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
            color: #5d4037;
        }
        
        /* Tableau des produits */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
        }
        
        .products-table th {
            background: #5d4037;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        
        .products-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        
        .products-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Total */
        .total-section {
            text-align: right;
            margin-top: 20px;
            padding: 20px;
            background: #f9f6f0;
            border-radius: 10px;
        }
        
        .total-line {
            margin: 8px 0;
        }
        
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #5d4037;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #5d4037;
        }
        
        /* Pied de page */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #95a5a6;
            border-top: 1px solid #ddd;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .qr-code {
            text-align: center;
            margin-top: 20px;
        }
        
        .qr-code img {
            width: 80px;
            height: 80px;
        }
    </style>
</head>
<body>

    <!-- Cachet officiel -->
    <div class="stamp">
        MEUBLESDESIGN<br>
        ⚡<br>
        OFFICIEL
    </div>

    <!-- En-tête -->
    <div class="header">
        ' . $logo_base64 . '
        <div class="company-name">MEUBLESDESIGN</div>
        <div class="company-slogan">VOTRE INTÉRIEUR, NOTRE PASSION</div>
        <div class="company-slogan">Cameroun • Livraison gratuite</div>
        <div class="document-title">📄 REÇU OFFICIEL DE PAIEMENT</div>
    </div>

    <!-- Informations client et commande -->
    <div class="info-grid">
        <div class="info-box">
            <div class="info-title">📌 INFORMATIONS COMMANDE</div>
            <div class="info-line"><span class="info-label">N° Commande :</span> <strong>#' . $order['id'] . '</strong></div>
            <div class="info-line"><span class="info-label">Date :</span> ' . date('d/m/Y', strtotime($order['created_at'])) . '</div>
            <div class="info-line"><span class="info-label">Heure :</span> ' . date('H:i', strtotime($order['created_at'])) . '</div>
            <div class="info-line"><span class="info-label">Statut :</span> <span style="background:#d4edda; color:#155724; padding:3px 10px; border-radius:15px;">' . ucfirst($order['status']) . '</span></div>
        </div>
        <div class="info-box">
            <div class="info-title">👤 INFORMATIONS CLIENT</div>
            <div class="info-line"><span class="info-label">Client :</span> ' . htmlspecialchars($order['client_nom']) . '</div>
            <div class="info-line"><span class="info-label">Email :</span> ' . htmlspecialchars($order['email']) . '</div>
            <div class="info-line"><span class="info-label">Paiement :</span> Stripe (Carte bancaire)</div>
            <div class="info-line"><span class="info-label">Transaction :</span> stripe_' . $order['id'] . '</div>
        </div>
    </div>

    <!-- Tableau des produits -->
    <table class="products-table">
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Quantité</th>
                <th>Prix unitaire (FCFA)</th>
                <th>Total (FCFA)</th>
            </tr>
        </thead>
        <tbody>';

foreach ($items as $item) {
    $prix_unitaire = $item['price'] * 655.96;
    $total_item = $prix_unitaire * $item['quantity'];
    $html .= '
            <tr>
                <td>' . htmlspecialchars($item['product_name']) . '</td>
                <td>' . $item['quantity'] . '</td>
                <td>' . number_format($prix_unitaire, 0) . '</td>
                <td><strong>' . number_format($total_item, 0) . '</strong></td>
            </tr>';
}

$html .= '
        </tbody>
    </table>

    <!-- Total -->
    <div class="total-section">
        <div class="total-line">Sous-total : ' . number_format($total, 0) . ' FCFA</div>
        <div class="total-line">Livraison : <strong>OFFERTE</strong></div>
        <div class="total-amount">TOTAL : ' . number_format($total, 0) . ' FCFA</div>
    </div>

    <!-- Mentions légales -->
    <div class="footer">
        <p>MeublesDesign - RC : CM-DLA-2026-001 - NUI : M123456789</p>
        <p>Adresse : Douala, Cameroun - Tél : +237 6XX XX XX XX - Email : contact@meublesdesign.cm</p>
        <p>Ce document fait office de reçu officiel de paiement et vaut garantie.</p>
        <p>Merci de votre confiance !</p>
        <p>Généré le ' . date('d/m/Y à H:i') . '</p>
    </div>

</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("recu_officiel_commande_" . $order['id'] . ".pdf", array("Attachment" => true));
exit();
?>