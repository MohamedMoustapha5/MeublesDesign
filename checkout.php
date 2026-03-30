<?php
session_start();
require 'db.php';

// Charger les variables d'environnement
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Vérifier si le panier n'est pas vide
if (empty($_SESSION['panier'])) {
    header("Location: panier.php");
    exit();
}

// --- CONFIGURATION STRIPE (depuis .env) ---
$stripe_secret_key = $_ENV['STRIPE_SECRET_KEY'];
$base_url = "http://localhost/Meubless/";

// Préparation des articles pour Stripe
$line_items = [];
foreach ($_SESSION['panier'] as $id => $quantite) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();

    if ($p) {
        $line_items[] = [
          'price_data' => [
    'currency' => 'xaf',
    'product_data' => [
        'name' => $p['nom'],
    ],
    'unit_amount' => intval($p['prix'] * 655.96),
],
            'quantity' => $quantite,
        ];
    }
}

// Appel à l'API Stripe
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/checkout/sessions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_USERPWD, $stripe_secret_key . ':');

// Construction des paramètres
$params = [
    'payment_method_types[]' => 'card',
    'mode' => 'payment',
    'success_url' => $base_url . 'confirmation.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => $base_url . 'panier.php',
    'client_reference_id' => $_SESSION['user_id'],
];

foreach ($line_items as $index => $item) {
    $params["line_items[$index][price_data][currency]"] = $item['price_data']['currency'];
    $params["line_items[$index][price_data][product_data][name]"] = $item['price_data']['product_data']['name'];
    $params["line_items[$index][price_data][unit_amount]"] = $item['price_data']['unit_amount'];
    $params["line_items[$index][quantity]"] = $item['quantity'];
}

curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "Erreur cURL : " . curl_error($ch);
} else {
    $session = json_decode($result, true);
    
    if ($http_code == 200 && isset($session['url'])) {
        header("Location: " . $session['url']);
        exit();
    } else {
        echo "<h3>❌ Erreur Stripe (Code $http_code)</h3>";
        echo "<pre>";
        print_r($session);
        echo "</pre>";
        echo '<p><a href="panier.php">⬅ Retour au panier</a></p>';
    }
}
curl_close($ch);
?>