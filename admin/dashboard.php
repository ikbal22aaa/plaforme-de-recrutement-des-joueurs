<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier l'authentification et le type d'utilisateur
requireAuth('admin');

$user_id = $_SESSION['user_id'];

// Statistiques générales
$stats = [
    'total_users' => 0,
    'total_joueurs' => 0,
    'total_entraineurs' => 0,
    'total_clubs' => 0,
    'total_annonces' => 0,
    'total_candidatures' => 0,
    'total_messages' => 0,
    'pending_users' => 0
];

try {
    // Compter les utilisateurs par type
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'joueur'");
    $stats['total_joueurs'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'entraineur'");
    $stats['total_entraineurs'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'club'");
    $stats['total_clubs'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending'");
    $stats['pending_users'] = $stmt->fetchColumn();

    // Compter les annonces et candidatures
    $stmt = $pdo->query("SELECT COUNT(*) FROM annonces");
    $stats['total_annonces'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM candidatures");
    $stats['total_candidatures'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM messages");
    $stats['total_messages'] = $stmt->fetchColumn();

    // Récupérer les derniers utilisateurs inscrits
    $stmt = $pdo->query("
        SELECT u.*, 
               CASE 
                   WHEN u.user_type = 'joueur' THEN j.position
                   WHEN u.user_type = 'entraineur' THEN e.specialite
                   WHEN u.user_type = 'club' THEN c.nom_club
                   ELSE ''
               END as detail
        FROM users u
        LEFT JOIN joueurs j ON u.id = j.user_id
        LEFT JOIN entraineurs e ON u.id = e.user_id
        LEFT JOIN clubs c ON u.id = c.user_id
        ORDER BY u.created_at DESC
        LIMIT 10
    ");
    $recent_users = $stmt->fetchAll();

    // Récupérer les utilisateurs en attente de validation
    $stmt = $pdo->query("
        SELECT u.*, 
               CASE 
                   WHEN u.user_type = 'joueur' THEN j.position
                   WHEN u.user_type = 'entraineur' THEN e.specialite
                   WHEN u.user_type = 'club' THEN c.nom_club
                   ELSE ''
               END as detail
        FROM users u
        LEFT JOIN joueurs j ON u.id = j.user_id
        LEFT JOIN entraineurs e ON u.id = e.user_id
        LEFT JOIN clubs c ON u.id = c.user_id
        WHERE u.status = 'pending'
        ORDER BY u.created_at DESC
        LIMIT 5
    ");
    $pending_users = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_message = 'Erreur lors du chargement des données';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - KoraJob</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../index.php">
                <i class="fas fa-futbol me-2"></i>KoraJob Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Utilisateurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="joueurs.php">Joueurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="entraineurs.php">Entraîneurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clubs.php">Clubs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="annonces.php">Annonces</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">Messages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notifications.php">Notifications</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="settings.php">Paramètres</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Dashboard Administrateur</h2>
                <p class="text-muted mb-0">Vue d'ensemble de la plateforme KoraJob</p>
            </div>
            <div>
                <a href="notifications.php" class="btn btn-primary">
                    <i class="fas fa-bell me-2"></i>Envoyer notification
                </a>
            </div>
        </div>

        <!-- Statistiques principales -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card primary">
                    <div class="d-flex align-items-center">
                        <div class="dashboard-icon primary me-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?php echo $stats['total_users']; ?></h4>
                            <small class="text-muted">Utilisateurs total</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card success">
                    <div class="d-flex align-items-center">
                        <div class="dashboard-icon success me-3">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?php echo $stats['total_joueurs']; ?></h4>
                            <small class="text-muted">Joueurs</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card info">
                    <div class="d-flex align-items-center">
                        <div class="dashboard-icon info me-3">
                            <i class="fas fa-whistle"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?php echo $stats['total_entraineurs']; ?></h4>
                            <small class="text-muted">Entraîneurs</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card warning">
                    <div class="d-flex align-items-center">
                        <div class="dashboard-icon warning me-3">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?php echo $stats['total_clubs']; ?></h4>
                            <small class="text-muted">Clubs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques secondaires -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card success">
                    <div class="d-flex align-items-center">
                        <div class="dashboard-icon success me-3">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?php echo $stats['total_annonces']; ?></h4>
                            <small class="text-muted">Annonces</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card info">
                    <div class="d-flex align-items-center">
                        <div class="dashboard-icon info me-3">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?php echo $stats['total_candidatures']; ?></h4>
                            <small class="text-muted">Candidatures</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card warning">
                    <div class="d-flex align-items-center">
                        <div class="dashboard-icon warning me-3">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?php echo $stats['total_messages']; ?></h4>
                            <small class="text-muted">Messages</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card danger">
                    <div class="d-flex align-items-center">
                        <div class="dashboard-icon danger me-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?php echo $stats['pending_users']; ?></h4>
                            <small class="text-muted">En attente</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Utilisateurs en attente -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2 text-warning"></i>Utilisateurs en attente
                        </h5>
                        <a href="users.php?status=pending" class="btn btn-sm btn-outline-warning">Voir tout</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pending_users)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h6 class="text-muted">Aucun utilisateur en attente</h6>
                                <p class="text-muted">Tous les comptes sont validés</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Type</th>
                                            <th>Détail</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_users as $user): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($user['nom']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['user_type'] === 'joueur' ? 'primary' : ($user['user_type'] === 'entraineur' ? 'success' : 'info'); ?>">
                                                        <?php echo ucfirst($user['user_type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['detail'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-success" onclick="validateUser(<?php echo $user['id']; ?>)">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-danger" onclick="rejectUser(<?php echo $user['id']; ?>)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Derniers utilisateurs inscrits -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-plus me-2 text-primary"></i>Derniers inscrits
                        </h5>
                        <a href="users.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($user['nom']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['user_type'] === 'joueur' ? 'primary' : ($user['user_type'] === 'entraineur' ? 'success' : 'info'); ?>">
                                                    <?php echo ucfirst($user['user_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = $user['status'] === 'active' ? 'success' : ($user['status'] === 'pending' ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge bg-<?php echo $status_class; ?>">
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($user['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Actions rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="users.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-users me-2"></i>Gérer les utilisateurs
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="notifications.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-bell me-2"></i>Envoyer notification
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="annonces.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-bullhorn me-2"></i>Modérer annonces
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="messages.php" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-envelope me-2"></i>Voir messages
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function validateUser(userId) {
            if (confirm('Valider cet utilisateur ?')) {
                fetch('api/validate_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId, action: 'validate' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                });
            }
        }

        function rejectUser(userId) {
            if (confirm('Rejeter cet utilisateur ?')) {
                fetch('api/validate_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId, action: 'reject' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>

