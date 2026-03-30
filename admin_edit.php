<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prix_fcfa = $_POST['prix'];
    $prix = $prix_fcfa / 655.96; // Conversion en Euro pour la BDD
    $stock = $_POST['stock'];

    $update = $pdo->prepare("UPDATE products SET nom = ?, prix = ?, stock = ? WHERE id = ?");
    if ($update->execute([$nom, $prix, $stock, $id])) {
        header("Location: admin_dashboard.php");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Produit</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="padding-top:100px;">
    <div class="container">
        <div class="form-container">
            <h2>Modifier : <?php echo $p['nom']; ?></h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nom du produit</label>
                    <input type="text" name="nom" value="<?php echo $p['nom']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Prix (FCFA)</label>
                    <input type="number" step="1" name="prix" value="<?php echo round($p['prix'] * 655.96); ?>" required>
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" value="<?php echo $p['stock']; ?>" required>
                </div>
                <button type="submit" class="btn-submit">Enregistrer les modifications</button>
                <a href="admin_dashboard.php" style="display:block; text-align:center; margin-top:10px;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>