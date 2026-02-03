<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Récupérer l'ID du joueur
$player_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$player_id) {
    header('Location: joueurs.php');
    exit();
}

// Récupérer les informations du joueur
$player = null;
$player_data = null;

try {
    // Récupérer les informations de base
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'joueur' AND status = 'active'");
    $stmt->execute([$player_id]);
    $player = $stmt->fetch();
    
    if (!$player) {
        header('Location: joueurs.php');
        exit();
    }
    
    // Récupérer les données spécifiques du joueur
    $stmt = $pdo->prepare("SELECT * FROM joueurs WHERE user_id = ?");
    $stmt->execute([$player_id]);
    $player_data = $stmt->fetch();
    
    // Récupérer les statistiques et évaluations
    $stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as rating_count
        FROM evaluations 
        WHERE evaluated_id = ?
    ");
    $stmt->execute([$player_id]);
    $stats = $stmt->fetch();
    
    // Récupérer les évaluations récentes
    $stmt = $pdo->prepare("
        SELECT e.*, u.nom as evaluator_name 
        FROM evaluations e 
        LEFT JOIN users u ON e.evaluator_id = u.id 
        WHERE e.evaluated_id = ? 
        ORDER BY e.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$player_id]);
    $recent_evaluations = $stmt->fetchAll();
    
    // Récupérer les vidéos publiques du joueur
    $stmt = $pdo->prepare("
        SELECT * FROM joueur_videos 
        WHERE joueur_id = ? AND is_public = TRUE 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$player_data['id']]);
    $videos = $stmt->fetchAll();
    
} catch (PDOException $e) {
    header('Location: joueurs.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($player['nom']); ?> - Profil Joueur - KoraJob</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 60vh;
            display: flex;
            align-items: center;
        }
        .player-card {
            transition: transform 0.3s ease;
        }
        .player-card:hover {
            transform: translateY(-5px);
        }
        .skill-bar {
            height: 8px;
            border-radius: 4px;
            background: linear-gradient(90deg, #007bff, #0056b3);
        }
        .stat-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-futbol me-2"></i>KoraJob
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="joueurs.php">Joueurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="entraineurs.php">Entraîneurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo $_SESSION['user_type']; ?>/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo $_SESSION['user_type']; ?>/profile.php">Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 text-center">
                    <img src="assets/images/avatar-joueur.svg" alt="Photo du joueur" 
                         class="rounded-circle shadow-lg mb-4" width="200" height="200">
                    <div class="rating mb-3">
                        <?php 
                        $rating = round($stats['avg_rating'] ?? 0);
                        for ($i = 1; $i <= 5; $i++): 
                        ?>
                            <i class="fas fa-star fa-lg <?php echo $i <= $rating ? 'text-warning' : 'text-light'; ?>"></i>
                        <?php endfor; ?>
                        <span class="ms-2 fs-5"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?>/5</span>
                    </div>
                </div>
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($player['nom']); ?></h1>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($player_data['wilaya'] ?? 'Non spécifié'); ?></h5>
                            <h5><i class="fas fa-birthday-cake me-2"></i><?php echo $player_data['age'] ?? 'Non spécifié'; ?> ans</h5>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-futbol me-2"></i><?php echo htmlspecialchars($player_data['position'] ?? 'Non spécifié'); ?></h5>
                            <h5><i class="fas fa-star me-2"></i><?php echo htmlspecialchars($player_data['niveau'] ?? 'Non spécifié'); ?></h5>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="contact.php?player_id=<?php echo $player_id; ?>" class="btn btn-warning btn-lg">
                            <i class="fas fa-envelope me-2"></i>Contacter
                        </a>
                        <a href="joueurs.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Retour aux joueurs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenu principal -->
    <div class="container py-5">
        <div class="row">
            <!-- Informations détaillées -->
            <div class="col-lg-8">
                <!-- Statistiques du joueur -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Statistiques du Joueur</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="fas fa-running me-2"></i>Vitesse</span>
                                        <span><?php echo $player_data['vitesse'] ?? '0'; ?>/10</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: <?php echo ($player_data['vitesse'] ?? 0) * 10; ?>%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="fas fa-bullseye me-2"></i>Précision</span>
                                        <span><?php echo $player_data['`precision`'] ?? '0'; ?>/10</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: <?php echo ($player_data['`precision`'] ?? 0) * 10; ?>%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="fas fa-dumbbell me-2"></i>Force</span>
                                        <span><?php echo $player_data['`force`'] ?? '0'; ?>/10</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: <?php echo ($player_data['`force`'] ?? 0) * 10; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="fas fa-brain me-2"></i>Technique</span>
                                        <span><?php echo $player_data['technique'] ?? '0'; ?>/10</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" style="width: <?php echo ($player_data['technique'] ?? 0) * 10; ?>%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="fas fa-users me-2"></i>Esprit d'équipe</span>
                                        <span><?php echo $player_data['esprit_equipe'] ?? '0'; ?>/10</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" style="width: <?php echo ($player_data['esprit_equipe'] ?? 0) * 10; ?>%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="fas fa-trophy me-2"></i>Expérience</span>
                                        <span><?php echo $player_data['experience_joueur'] ?? '0'; ?>/10</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-dark" style="width: <?php echo ($player_data['experience_joueur'] ?? 0) * 10; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description et parcours -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Présentation</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($player_data['description'])): ?>
                            <p class="lead"><?php echo nl2br(htmlspecialchars($player_data['description'])); ?></p>
                        <?php else: ?>
                            <p class="text-muted">Aucune description disponible pour le moment.</p>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-info-circle me-2"></i>Informations complémentaires</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Pied fort:</strong> <?php echo htmlspecialchars($player_data['pied_fort'] ?? 'Non spécifié'); ?></li>
                                    <li><strong>Taille:</strong> <?php echo $player_data['taille'] ?? 'Non spécifié'; ?> cm</li>
                                    <li><strong>Poids:</strong> <?php echo $player_data['poids'] ?? 'Non spécifié'; ?> kg</li>
                                    <li><strong>Nationalité:</strong> <?php echo htmlspecialchars($player_data['nationalite'] ?? 'Non spécifié'); ?></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-trophy me-2"></i>Parcours</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Anciens clubs:</strong> <?php echo htmlspecialchars($player_data['anciens_clubs'] ?? 'Aucun'); ?></li>
                                    <li><strong>Palmarès:</strong> <?php echo htmlspecialchars($player_data['palmares'] ?? 'Aucun'); ?></li>
                                    <li><strong>Formation:</strong> <?php echo htmlspecialchars($player_data['formation'] ?? 'Non spécifiée'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vidéo de présentation -->
                <?php if (!empty($player_data['video_url'])): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-video me-2"></i>Vidéo de Présentation</h5>
                        </div>
                        <div class="card-body">
                            <div class="ratio ratio-16x9">
                                <video controls>
                                    <source src="<?php echo htmlspecialchars($player_data['video_url']); ?>" type="video/mp4">
                                    Votre navigateur ne supporte pas la lecture vidéo.
                                </video>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Vidéos du joueur -->
                <?php if (!empty($videos)): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-video me-2"></i>Vidéos de Présentation</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($videos as $video): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                                <?php if ($video['video_url']): ?>
                                                    <i class="fas fa-play-circle fa-3x text-primary"></i>
                                                <?php elseif ($video['video_file']): ?>
                                                    <i class="fas fa-file-video fa-3x text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-video fa-3x text-muted"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($video['title']); ?></h6>
                                                <?php if (!empty($video['description'])): ?>
                                                    <p class="card-text small text-muted">
                                                        <?php echo htmlspecialchars(substr($video['description'], 0, 100)); ?>
                                                        <?php echo strlen($video['description']) > 100 ? '...' : ''; ?>
                                                    </p>
                                                <?php endif; ?>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-tag me-1"></i>
                                                        <?php 
                                                        $video_types = [
                                                            'skills' => 'Compétences',
                                                            'match' => 'Match',
                                                            'training' => 'Entraînement',
                                                            'interview' => 'Interview',
                                                            'other' => 'Autre'
                                                        ];
                                                        echo $video_types[$video['video_type']] ?? ucfirst($video['video_type']);
                                                        ?>
                                                    </small>
                                                    <?php if ($video['video_url']): ?>
                                                        <a href="<?php echo htmlspecialchars($video['video_url']); ?>" 
                                                           target="_blank" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-external-link-alt me-1"></i>Ouvrir
                                                        </a>
                                                    <?php elseif ($video['video_file']): ?>
                                                        <a href="uploads/videos/<?php echo htmlspecialchars($video['video_file']); ?>" 
                                                           target="_blank" class="btn btn-success btn-sm">
                                                            <i class="fas fa-download me-1"></i>Télécharger
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Évaluations récentes -->
                <?php if (!empty($recent_evaluations)): ?>
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Évaluations Récentes</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($recent_evaluations as $evaluation): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?php echo htmlspecialchars($evaluation['evaluator_name'] ?? 'Anonyme'); ?></strong>
                                            <div class="rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $evaluation['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($evaluation['created_at'])); ?></small>
                                    </div>
                                    <?php if (!empty($evaluation['comment'])): ?>
                                        <p class="mt-2 mb-0"><?php echo htmlspecialchars($evaluation['comment']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-center">
                                <a href="profile.php?id=<?php echo $player_id; ?>" class="btn btn-outline-primary">
                                    Voir toutes les évaluations
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Statistiques rapides -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistiques Rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="stat-card p-3 mb-3">
                                    <h3 class="mb-1"><?php echo $stats['rating_count'] ?? 0; ?></h3>
                                    <small>Évaluations</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3 mb-3">
                                    <h3 class="mb-1"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h3>
                                    <small>Note moyenne</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <a href="contact.php?player_id=<?php echo $player_id; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-envelope me-2"></i>Contacter ce joueur
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Informations de contact -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-phone me-2"></i>Contact</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            Pour contacter ce joueur, utilisez le formulaire de contact. 
                            Vos coordonnées seront transmises directement.
                        </p>
                        <div class="d-grid gap-2">
                            <a href="contact.php?player_id=<?php echo $player_id; ?>" class="btn btn-success">
                                <i class="fas fa-envelope me-2"></i>Envoyer un message
                            </a>
                            <a href="tel:+213XXXXXXXX" class="btn btn-outline-primary">
                                <i class="fas fa-phone me-2"></i>Appeler
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Joueurs similaires -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Joueurs Similaires</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Découvrez d'autres joueurs avec un profil similaire.</p>
                        <div class="d-grid">
                            <a href="joueurs.php?position=<?php echo urlencode($player_data['position'] ?? ''); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-search me-2"></i>Voir les <?php echo htmlspecialchars($player_data['position'] ?? 'joueurs'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 KoraJob. Tous droits réservés.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Développé avec <i class="fas fa-heart text-danger"></i> pour le football</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
