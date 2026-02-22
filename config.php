<?php
// Configuration du site - plus de clés secrètes ici !
// Les clés API sont maintenant dans le fichier .env

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'meubles_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// URL du site
define('SITE_URL', 'http://localhost/Meubless');

// Connexion PDO (si tu veux l'inclure ici)
try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>