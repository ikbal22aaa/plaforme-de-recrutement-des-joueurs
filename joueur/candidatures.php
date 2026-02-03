<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier l'authentification et le type d'utilisateur
requireAuth('joueur');

$user_id = $_SESSION['user_id'];
$joueur = getJoueurProfile($pdo, $user_id);

// Récupérer les candidatures du joueur
try {
    $stmt = $pdo->prepare("
        SELECT c.*, a.titre, a.description, a.position_recherchee, a.niveau_requis,
               cl.nom_club, cl.ville, cl.wilaya, u.nom as club_nom
        FROM candidatures c
        LEFT JOIN annonces a ON c.annonce_id = a.id
        LEFT JOIN clubs cl ON a.club_id = cl.id
        LEFT JOIN users u ON cl.user_id = u.id
        WHERE c.joueur_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$joueur['id']]);
    $candidatures = $stmt->fetchAll();
    
    // Statistiques
    $stats = [
        'total' => count($candidatures),
        'pending' => 0,
        'accepted' => 0,
        'rejected' => 0
    ];
    
    foreach ($candidatures as $candidature) {
        $stats[$candidature['status']]++;
    }
    
} catch (PDOException $e) {
    $candidatures = [];
    $stats = ['total' => 0, 'pending' => 0, 'accepted' => 0, 'rejected' => 0];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Candidatures - KoraJob</title>
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Mon Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="candidatures.php">Mes Candidatures</a>
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
                            <img src="../assets/images/avatar-joueur.svg" alt="Avatar" 
                                 class="rounded-circle mb-2" width="80" height="80">
                            <h6 class="fw-bold"><?php echo htmlspecialchars($joueur['nom']); ?></h6>
                            <small class="text-muted">Joueur</small>
                        </div>
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="profile.php">
                                    <i class="fas fa-user me-2"></i>Mon Profil
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="candidatures.php">
                                    <i class="fas fa-paper-plane me-2"></i>Candidatures
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="messages.php">
                                    <i class="fas fa-envelope me-2"></i>Messages
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
                        <h2 class="fw-bold mb-1">Mes Candidatures</h2>
                        <p class="text-muted mb-0">Suivez l'état de vos candidatures auprès des clubs</p>
                    </div>
                    <div>
                        <a href="../joueurs.php" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Rechercher des annonces
                        </a>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $stats['total']; ?></h3>
                                <small>Total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $stats['pending']; ?></h3>
                                <small>En attente</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $stats['accepted']; ?></h3>
                                <small>Acceptées</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $stats['rejected']; ?></h3>
                                <small>Refusées</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des candidatures -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Historique des candidatures
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($candidatures)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-paper-plane fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucune candidature envoyée</h5>
                                <p class="text-muted">Commencez par parcourir les annonces disponibles et postulez !</p>
                                <a href="../joueurs.php" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Rechercher des annonces
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Club</th>
                                            <th>Poste</th>
                                            <th>Statut</th>
                                            <th>Date de candidature</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($candidatures as $candidature): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <i class="fas fa-building fa-2x text-muted"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold"><?php echo htmlspecialchars($candidature['nom_club'] ?? 'N/A'); ?></div>
                                                            <small class="text-muted">
                                                                <?php echo htmlspecialchars($candidature['ville'] ?? ''); ?>
                                                                <?php if (!empty($candidature['wilaya'])): ?>
                                                                    , <?php echo htmlspecialchars($candidature['wilaya']); ?>
                                                                <?php endif; ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($candidature['titre'] ?? $candidature['position_recherchee'] ?? 'N/A'); ?></div>
                                                    <?php if (!empty($candidature['niveau_requis'])): ?>
                                                        <small class="text-muted">Niveau: <?php echo htmlspecialchars($candidature['niveau_requis']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_classes = [
                                                        'pending' => 'warning',
                                                        'accepted' => 'success',
                                                        'rejected' => 'danger'
                                                    ];
                                                    $status_texts = [
                                                        'pending' => 'En attente',
                                                        'accepted' => 'Acceptée',
                                                        'rejected' => 'Refusée'
                                                    ];
                                                    $status_class = $status_classes[$candidature['status']] ?? 'secondary';
                                                    $status_text = $status_texts[$candidature['status']] ?? ucfirst($candidature['status']);
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?> fs-6">
                                                        <?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div><?php echo formatDate($candidature['created_at'], 'd/m/Y'); ?></div>
                                                    <small class="text-muted"><?php echo formatDate($candidature['created_at'], 'H:i'); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#candidatureModal<?php echo $candidature['id']; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($candidature['status'] === 'accepted'): ?>
                                                            <a href="../contact.php?club_id=<?php echo $candidature['club_id'] ?? ''; ?>" 
                                                               class="btn btn-sm btn-success">
                                                                <i class="fas fa-envelope"></i>
                                                            </a>
                                                        <?php endif; ?>
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
        </div>
    </div>

    <!-- Modales pour voir les détails des candidatures -->
    <?php foreach ($candidatures as $candidature): ?>
        <div class="modal fade" id="candidatureModal<?php echo $candidature['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-paper-plane me-2"></i>Candidature - <?php echo htmlspecialchars($candidature['nom_club'] ?? 'N/A'); ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-building me-2"></i>Club</h6>
                                <p class="mb-3"><?php echo htmlspecialchars($candidature['nom_club'] ?? 'N/A'); ?></p>
                                
                                <h6><i class="fas fa-map-marker-alt me-2"></i>Localisation</h6>
                                <p class="mb-3">
                                    <?php echo htmlspecialchars($candidature['ville'] ?? ''); ?>
                                    <?php if (!empty($candidature['wilaya'])): ?>
                                        , <?php echo htmlspecialchars($candidature['wilaya']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-briefcase me-2"></i>Poste recherché</h6>
                                <p class="mb-3"><?php echo htmlspecialchars($candidature['titre'] ?? $candidature['position_recherchee'] ?? 'N/A'); ?></p>
                                
                                <h6><i class="fas fa-calendar me-2"></i>Date de candidature</h6>
                                <p class="mb-3"><?php echo formatDate($candidature['created_at']); ?></p>
                            </div>
                        </div>
                        
                        <?php if (!empty($candidature['message'])): ?>
                            <h6><i class="fas fa-comment me-2"></i>Message envoyé</h6>
                            <div class="alert alert-info">
                                <?php echo nl2br(htmlspecialchars($candidature['message'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($candidature['description'])): ?>
                            <h6><i class="fas fa-info-circle me-2"></i>Description de l'annonce</h6>
                            <p><?php echo nl2br(htmlspecialchars($candidature['description'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <?php if ($candidature['status'] === 'accepted'): ?>
                            <a href="../contact.php?club_id=<?php echo $candidature['club_id'] ?? ''; ?>" class="btn btn-success">
                                <i class="fas fa-envelope me-2"></i>Contacter le club
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>

