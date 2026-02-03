<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier l'authentification et le type d'utilisateur
requireAuth('joueur');

$user_id = $_SESSION['user_id'];
$joueur = getJoueurProfile($pdo, $user_id);

// Statistiques du joueur
$stats = [
    'annonces_vues' => 0,
    'candidatures_envoyees' => 0,
    'messages_recus' => 0,
    'evaluations_recues' => 0
];

try {
    // Compter les candidatures envoyées
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidatures WHERE joueur_id = ?");
    $stmt->execute([$joueur['id']]);
    $stats['candidatures_envoyees'] = $stmt->fetchColumn();

    // Compter les messages reçus
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ?");
    $stmt->execute([$user_id]);
    $stats['messages_recus'] = $stmt->fetchColumn();

    // Compter les évaluations reçues
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM evaluations WHERE evaluated_id = ?");
    $stmt->execute([$user_id]);
    $stats['evaluations_recues'] = $stmt->fetchColumn();

    // Récupérer les dernières candidatures
    $stmt = $pdo->prepare("
        SELECT c.*, a.titre, a.description, cl.nom_club, cl.ville
        FROM candidatures c
        LEFT JOIN annonces a ON c.annonce_id = a.id
        LEFT JOIN clubs cl ON a.club_id = cl.id
        WHERE c.joueur_id = ?
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$joueur['id']]);
    $candidatures = $stmt->fetchAll();

    // Récupérer les dernières notifications
    $notifications = getUnreadNotifications($pdo, $user_id);

} catch (PDOException $e) {
    $error_message = 'Erreur lors du chargement des données';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Joueur - KoraJob</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../index.php">
                <i class="fas fa-futbol me-2"></i>KoraJob
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
                        <a class="nav-link" href="profile.php">Mon Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="candidatures.php">Mes Candidatures</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">Messages</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
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
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <img src="../assets/images/default-avatar.jpg" alt="Avatar" 
                                 class="rounded-circle mb-2" width="80" height="80">
                            <h6 class="fw-bold"><?php echo htmlspecialchars($joueur['nom']); ?></h6>
                            <small class="text-muted">Joueur</small>
                        </div>
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item">
                                <a class="nav-link active" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="profile.php">
                                    <i class="fas fa-user me-2"></i>Mon Profil
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="videos.php">
                                    <i class="fas fa-video me-2"></i>Mes Vidéos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="candidatures.php">
                                    <i class="fas fa-paper-plane me-2"></i>Candidatures
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="messages.php">
                                    <i class="fas fa-envelope me-2"></i>Messages
                                    <?php if ($stats['messages_recus'] > 0): ?>
                                        <span class="badge bg-danger ms-2"><?php echo $stats['messages_recus']; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="evaluations.php">
                                    <i class="fas fa-star me-2"></i>Évaluations
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="col-md-9 col-lg-10">
                <!-- En-tête -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Dashboard</h2>
                        <p class="text-muted mb-0">Bienvenue, <?php echo htmlspecialchars($joueur['nom']); ?> !</p>
                    </div>
                    <div>
                        <a href="profile.php" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Compléter mon profil
                        </a>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="dashboard-card success">
                            <div class="d-flex align-items-center">
                                <div class="dashboard-icon success me-3">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1"><?php echo $stats['candidatures_envoyees']; ?></h4>
                                    <small class="text-muted">Candidatures envoyées</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="dashboard-card info">
                            <div class="d-flex align-items-center">
                                <div class="dashboard-icon info me-3">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1"><?php echo $stats['messages_recus']; ?></h4>
                                    <small class="text-muted">Messages reçus</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="dashboard-card warning">
                            <div class="d-flex align-items-center">
                                <div class="dashboard-icon warning me-3">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1"><?php echo $stats['evaluations_recues']; ?></h4>
                                    <small class="text-muted">Évaluations reçues</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="dashboard-card primary">
                            <div class="d-flex align-items-center">
                                <div class="dashboard-icon primary me-3">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1"><?php echo round($joueur['rating'] ?? 0, 1); ?></h4>
                                    <small class="text-muted">Note moyenne</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Dernières candidatures -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-paper-plane me-2"></i>Dernières candidatures
                                </h5>
                                <a href="candidatures.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($candidatures)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-paper-plane fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted">Aucune candidature envoyée</h6>
                                        <p class="text-muted">Commencez par parcourir les annonces disponibles</p>
                                        <a href="../joueurs.php" class="btn btn-primary">Voir les annonces</a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Club</th>
                                                    <th>Poste</th>
                                                    <th>Statut</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($candidatures as $candidature): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="fw-bold"><?php echo htmlspecialchars($candidature['nom_club'] ?? 'N/A'); ?></div>
                                                            <small class="text-muted"><?php echo htmlspecialchars($candidature['ville'] ?? ''); ?></small>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($candidature['titre'] ?? 'N/A'); ?></td>
                                                        <td>
                                                            <?php
                                                            $status_class = '';
                                                            switch ($candidature['status']) {
                                                                case 'accepted':
                                                                    $status_class = 'success';
                                                                    break;
                                                                case 'rejected':
                                                                    $status_class = 'danger';
                                                                    break;
                                                                default:
                                                                    $status_class = 'warning';
                                                            }
                                                            ?>
                                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                                <?php echo ucfirst($candidature['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo formatDate($candidature['created_at']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-bell me-2"></i>Notifications
                                </h5>
                                <?php if (count($notifications) > 0): ?>
                                    <span class="badge bg-danger"><?php echo count($notifications); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if (empty($notifications)): ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Aucune notification</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-info-circle text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                <p class="small text-muted mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                <small class="text-muted"><?php echo formatDate($notification['created_at']); ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Actions rapides -->
                        <div class="card mt-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-bolt me-2"></i>Actions rapides
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="profile.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-edit me-2"></i>Compléter mon profil
                                    </a>
                                    <a href="videos.php" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-video me-2"></i>Ajouter des vidéos
                                    </a>
                                    <a href="../joueurs.php" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-search me-2"></i>Rechercher des annonces
                                    </a>
                                    <a href="messages.php" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-envelope me-2"></i>Voir mes messages
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>

