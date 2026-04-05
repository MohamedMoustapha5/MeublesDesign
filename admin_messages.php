<?php
session_start();
require 'db.php';

// 1. SÉCURITÉ : Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: connexion.php");
    exit();
}

// 2. SUPPRESSION D'UN MESSAGE
if (isset($_GET['delete_id'])) {
    $id_del = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$id_del]);
    header("Location: admin_messages.php?msg=deleted");
    exit();
}

// 3. RÉCUPÉRATION DES MESSAGES (les plus récents en premier)
$stmt = $pdo->query("SELECT * FROM messages ORDER BY id DESC");
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages Clients - Administration</title>
    <link rel="stylesheet" href="/Meubless/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-messages-grid {
            display: grid;
            gap: 20px;
            margin-top: 30px;
        }
        .message-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-left: 5px solid #3498db;
            position: relative;
        }
        .message-card.unread { border-left-color: #e74c3c; }
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .client-info h4 { margin: 0; color: #333; }
        .client-info span { font-size: 0.9em; color: #7f8c8d; }
        .message-body {
            font-style: italic;
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .message-footer {
            display: flex;
            gap: 15px;
        }
        .btn-action {
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: 0.3s;
        }
        .btn-reply { background: #3498db; color: white; }
        .btn-delete { background: #fee; color: #e74c3c; }
        .btn-reply:hover { background: #2980b9; }
        .btn-delete:hover { background: #e74c3c; color: white; }
        .date-badge {
            font-size: 0.8em;
            color: #bdc3c7;
        }
    </style>
</head>
<body>

    <header class="navbar">
        <h1><i class="fas fa-tools"></i> Admin Panel</h1>
        <nav>
            <a href="admin_dashboard.php">Boutique</a>
            <a href="admin_messages.php" class="active">Messages</a>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>

    
    <main class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 40px;">
            <h2><i class="fas fa-envelope-open-text"></i> Boîte de réception</h2>
            <span class="badge"><?php echo count($messages); ?> message(s)</span>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <p style="color: #e74c3c; background: #fee; padding: 10px; border-radius: 5px; text-align: center;">Message supprimé avec succès.</p>
        <?php endif; ?>

        <div class="admin-messages-grid">
            <?php if (count($messages) > 0): ?>
                <?php foreach ($messages as $m): ?>
                    <div class="message-card">
                        <div class="message-header">
                            <div class="client-info">
                                <h4><?php echo htmlspecialchars($m['nom']); ?></h4>
                                <span><i class="fas fa-at"></i> <?php echo htmlspecialchars($m['email']); ?></span>
                            </div>
                            <div class="date-badge">
                                <i class="far fa-clock"></i> 
                                <?php echo isset($m['created_at']) ? date('d/m/Y H:i', strtotime($m['created_at'])) : 'Date inconnue'; ?>
                            </div>
                        </div>

                        <div class="message-body">
                            <strong>Sujet : <?php echo htmlspecialchars($m['sujet']); ?></strong><br><br>
<?php echo isset($m['message']) ? nl2br(htmlspecialchars($m['message'])) : 'Contenu indisponible'; ?>                        </div>

                        <div class="message-footer">
                            <a href="admin_reponse.php?id=<?php echo $m['id']; ?>" class="btn-action btn-reply">
                                <i class="fas fa-reply"></i> Répondre par Email
                            </a>
                            
                            <a href="admin_messages.php?delete_id=<?php echo $m['id']; ?>" 
                               class="btn-action btn-delete" 
                               onclick="return confirm('Supprimer définitivement ce message ?')">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 100px; color: #bdc3c7;">
                    <i class="fas fa-inbox fa-4x"></i>
                    <p style="margin-top: 20px;">Aucun message reçu pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>