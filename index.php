<?php
session_start();
require 'db.php';

// Initialisation des variables de recherche et de filtrage
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$cat = isset($_GET['cat']) ? htmlspecialchars($_GET['cat']) : '';

// Construction de la requête SQL dynamique
$sql = "SELECT * FROM products WHERE nom LIKE :search";
$params = ['search' => "%$search%"];

if ($cat != '') {
    $sql .= " AND categorie = :cat";
    $params['cat'] = $cat;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeublesDesign | Boutique Officielle</title>
    <link rel="stylesheet" href="/Meubless/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Style pour le message de bienvenue */
        .welcome-message {
            background: linear-gradient(135deg, #5d4037 0%, #8d6e63 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            margin: 20px 0;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(93, 64, 55, 0.3);
            font-weight: 500;
            animation: slideDown 0.5s ease;
        }
        
        .welcome-message i {
            margin-right: 10px;
            color: #ffd700;
        }
        
        .welcome-message span {
            font-weight: 700;
            text-transform: capitalize;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* NAVBAR : LOGO TOUT À GAUCHE - NAVBAR TOUT À DROITE */
        .navbar {
            background: var(--white);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 15px 0;
        }
        
        .nav-container {
            max-width: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        
        .navbar h1 {
            font-size: 28px;
            color: var(--primary-color);
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-left: 0;
            padding-left: 20px;
            white-space: nowrap;
        }
        
        .navbar h1 i {
            margin-right: 12px;
            color: var(--secondary-color);
            font-size: 32px;
        }
        
        .navbar nav {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-right: 0;
            padding-right: 20px;
        }
        
        .navbar nav a {
            padding: 10px 18px;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.3s ease;
            border-radius: 30px;
            white-space: nowrap;
        }
        
        .navbar nav a:hover {
            color: var(--primary-color);
            background-color: rgba(93, 64, 55, 0.08);
        }
        
        .navbar nav a i {
            margin-right: 8px;
        }
        
        .admin-link {
            color: #d35400 !important;
            background: rgba(211, 84, 0, 0.05);
        }
        
        .admin-link:hover {
            background: rgba(211, 84, 0, 0.15) !important;
        }
        
        .logout-link {
            font-size: 18px !important;
            padding: 10px 15px !important;
        }
        
        /* Style pour le nom d'utilisateur dans la navbar */
        .user-name {
            background: var(--primary-color);
            color: white !important;
            border-radius: 30px;
            padding: 8px 18px !important;
            margin-left: 10px;
        }
        
        .user-name i {
            color: #ffd700;
        }
        
        .user-name:hover {
            background: var(--secondary-color) !important;
            color: white !important;
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                align-items: center;
            }
            
            .navbar h1 {
                padding-left: 0;
                margin-bottom: 10px;
            }
            
            .navbar nav {
                padding-right: 0;
                flex-wrap: wrap;
                justify-content: center;
            }
        }
        
        :root {
            --primary-color: #5d4037;
            --secondary-color: #8d6e63;
            --bg-color: #f4f1ea;
            --text-color: #333;
            --white: #ffffff;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
        }
        
        main {
            max-width: 1400px;
            margin: 150px auto 40px;
            padding: 0 30px;
            min-height: calc(100vh - 220px);
        }
        
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            flex-wrap: wrap;
            gap: 25px;
            background: var(--white);
            padding: 25px 35px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        
        .search-form {
            display: flex;
            flex: 1;
            max-width: 450px;
        }
        
        .search-form input {
            flex: 1;
            padding: 14px 18px;
            border: 1px solid #e0e0e0;
            border-radius: 50px 0 0 50px;
            font-size: 14px;
            outline: none;
            background: #fafafa;
        }
        
        .search-form input:focus {
            border-color: var(--secondary-color);
            background: white;
        }
        
        .search-form button {
            width: 55px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0 50px 50px 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .search-form button:hover {
            background: var(--secondary-color);
        }
        
        .categories {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .categories a {
            padding: 10px 22px;
            background: #f0f0f0;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .categories a:hover {
            background: var(--secondary-color);
            color: var(--white);
        }
        
        .categories a.active {
            background: var(--primary-color);
            color: var(--white);
        }
        
        @media (max-width: 768px) {
            main {
                margin-top: 220px;
                min-height: calc(100vh - 260px);
            }
        }
        
        .section-title {
            color: var(--primary-color);
            margin-bottom: 35px;
            font-size: 32px;
            font-weight: 600;
            position: relative;
            padding-bottom: 12px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 70px;
            height: 4px;
            background: var(--secondary-color);
            border-radius: 4px;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 35px;
            margin-bottom: 60px;
        }
        
        .product-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        
        .product-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 35px rgba(93, 64, 55, 0.15);
        }
        
        .product-image {
            position: relative;
            overflow: hidden;
            aspect-ratio: 1 / 1;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.08);
        }
        
        .stock-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(220, 53, 69, 0.95);
            color: white;
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            z-index: 10;
        }
        
        .product-info {
            padding: 22px;
        }
        
        .badge {
            display: inline-block;
            background: var(--bg-color);
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 12px;
        }
        
        .product-info h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 18px;
            color: var(--text-color);
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .price {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .add-btn {
            background: var(--primary-color);
            color: white;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(93, 64, 55, 0.3);
        }
        
        .add-btn:hover {
            background: var(--secondary-color);
            transform: scale(1.1) rotate(5deg);
        }
        
        .support-section {
            background: var(--white);
            padding: 70px;
            margin-top: 60px;
            text-align: center;
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        }
        
        .support-section h2 {
            color: var(--primary-color);
            font-size: 32px;
            margin-bottom: 15px;
        }
        
        .btn-valider {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 16px 45px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(93, 64, 55, 0.25);
        }
        
        .btn-valider:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
        }
        
        .floating-contact {
            position: fixed;
            bottom: 35px;
            right: 35px;
            background-color: var(--primary-color);
            color: white;
            width: 65px;
            height: 65px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 26px;
            box-shadow: 0 6px 20px rgba(93, 64, 55, 0.4);
            z-index: 1000;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .floating-contact:hover {
            transform: scale(1.1) rotate(5deg);
            background-color: var(--secondary-color);
        }
        
        footer {
            text-align: center;
            padding: 35px;
            background: var(--white);
            border-top: 1px solid #eee;
            color: #aaa;
            margin-top: 60px;
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="nav-container">
            <h1><i class="fas fa-couch"></i> MeublesDesign</h1>
            <nav>
                <a href="index.php">Accueil</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="mes_commandes.php"><i class="fas fa-box"></i> Commandes</a>
                    <a href="panier.php"><i class="fas fa-shopping-basket"></i> Panier</a>
                    <?php if($_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin_dashboard.php" class="admin-link"><i class="fas fa-tools"></i> Admin</a>
                    <?php endif; ?>
                    <!-- Affichage du nom de l'utilisateur connecté -->
                    <a href="profil.php" class="user-name">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </a>
                    <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="connexion.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                    <a href="signup.php"><i class="fas fa-user-plus"></i> S'inscrire</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        

        <div class="filter-bar">
            <form method="GET" action="index.php" class="search-form">
                <input type="text" name="search" placeholder="Quel meuble cherchez-vous ?" value="<?php echo $search; ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            
            <div class="categories">
                <a href="index.php" class="<?php echo $cat == '' ? 'active' : ''; ?>">Tous</a>
                <?php
                $stmt_count = $pdo->query("SELECT categorie, COUNT(*) as nb FROM products GROUP BY categorie");
                while ($row = $stmt_count->fetch()) {
                    $active = ($cat == $row['categorie']) ? 'active' : '';
                    echo "<a href='index.php?cat=".$row['categorie']."' class='$active'>".$row['categorie']." (".$row['nb'].")</a>";
                }
                ?>
            </div>
        </div>
<!-- MESSAGE DE BIENVENUE POUR L'UTILISATEUR CONNECTÉ -->
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="welcome-message">
                <i class="fas fa-hand-peace"></i> Bonjour, <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span> ! Ravi de vous revoir sur MeublesDesign
            </div>
        <?php endif; ?>
        <h2 class="section-title">
            <?php echo $cat ? "Collection " . $cat : "Toutes nos créations"; ?>
        </h2>

        <div class="product-grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if($p['stock'] <= 0): ?>
                                <div class="stock-badge">ÉPUISÉ</div>
                            <?php endif; ?>
                            
                            <a href="produit.php?id=<?php echo $p['id']; ?>">
                                <img src="<?php echo $p['image']; ?>" alt="<?php echo htmlspecialchars($p['nom']); ?>" class="<?php echo $p['stock'] <= 0 ? 'out-of-stock' : ''; ?>">
                            </a>
                        </div>
                        <div class="product-info">
                            <span class="badge"><?php echo htmlspecialchars($p['categorie']); ?></span>
                            <h3><?php echo htmlspecialchars($p['nom']); ?></h3>
                            
                            <div class="price-row">
                               <span class="price"><?php echo number_format($p['prix'] * 655.96, 0); ?> FCFA</span>
                                
                                <?php if($p['stock'] > 0): ?>
                                    <a href="panier.php?add=<?php echo $p['id']; ?>" class="add-btn">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="unavailable">Indisponible</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search fa-3x"></i>
                    <p>Désolé, nous n'avons trouvé aucun meuble correspondant à votre demande.</p>
                </div>
            <?php endif; ?>
        </div>

        <section class="support-section">
            <h2>Besoin d'aide pour aménager votre intérieur ?</h2>
            <p>Nos conseillers sont disponibles pour vous répondre.</p>
            <a href="contact.php" class="btn-valider">
                <i class="fas fa-paper-plane"></i> Contactez le support
            </a>
        </section>

    </main>

    <a href="contact.php" class="floating-contact" title="Nous écrire">
        <i class="fas fa-comment-dots"></i>
    </a>

    <footer>
        <p>&copy; 2026 MeublesDesign. Fait avec passion pour le design.</p>
    </footer>

</body>
</html>