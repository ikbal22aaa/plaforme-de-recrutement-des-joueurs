<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Récupérer l'ID de l'entraîneur
$coach_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$coach_id) {
    header('Location: entraineurs.php');
    exit();
}

// Récupérer les informations de l'entraîneur
$coach = null;
$coach_data = null;

try {
    // Récupérer les informations de base
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'entraineur' AND status = 'active'");
    $stmt->execute([$coach_id]);
    $coach = $stmt->fetch();
    
    if (!$coach) {
        header('Location: entraineurs.php');
        exit();
    }
    
    // Récupérer les données spécifiques de l'entraîneur
    $stmt = $pdo->prepare("SELECT * FROM entraineurs WHERE user_id = ?");
    $stmt->execute([$coach_id]);
    $coach_data = $stmt->fetch();
    
    // Récupérer les statistiques et évaluations
    $stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as rating_count
        FROM evaluations 
        WHERE evaluated_id = ?
    ");
    $stmt->execute([$coach_id]);
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
    $stmt->execute([$coach_id]);
    $recent_evaluations = $stmt->fetchAll();
    
} catch (PDOException $e) {
    header('Location: entraineurs.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($coach['nom']); ?> - Profil Entraîneur - KoraJob</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 60vh;
            display: flex;
            align-items: center;
        }
        .coach-card {
            transition: transform 0.3s ease;
        }
        .coach-card:hover {
            transform: translateY(-5px);
        }
        .skill-bar {
            height: 8px;
            border-radius: 4px;
            background: linear-gradient(90deg, #28a745, #20c997);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .specialty-badge {
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            color: white;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 0.9rem;
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
                        <a class="nav-link" href="joueurs.php">Joueurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="entraineurs.php">Entraîneurs</a>
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
                    <img src="assets/images/avatar-entraineur.svg" alt="Photo de l'entraîneur" 
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
                    <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($coach['nom']); ?></h1>
                    <div class="mb-3">
                        <span class="specialty-badge me-3">
                            <i class="fas fa-whistle me-2"></i><?php echo htmlspecialchars($coach_data['specialite'] ?? 'Entraîneur'); ?>
                        </span>
                        <span class="badge bg-light text-dark fs-6">
                            <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($coach_data['wilaya'] ?? 'Non spécifié'); ?>
                        </span>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><i class="fas fa-flag me-2"></i><?php echo htmlspecialchars($coach_data['nationalite'] ?? 'Non spécifié'); ?></h5>
                            <h5><i class="fas fa-language me-2"></i><?php echo htmlspecialchars($coach_data['langues'] ?? 'Non spécifié'); ?></h5>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-calendar-alt me-2"></i><?php echo $coach_data['annees_experience'] ?? '0'; ?> ans d'expérience</h5>
                            <h5><i class="fas fa-certificate me-2"></i><?php echo htmlspecialchars($coach_data['diplomes'] ?? 'Non spécifié'); ?></h5>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="contact.php?entraineur_id=<?php echo $coach_id; ?>" class="btn btn-warning btn-lg">
                            <i class="fas fa-envelope me-2"></i>Contacter
                        </a>
                        <a href="entraineurs.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Retour aux entraîneurs
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
                <!-- Compétences de l'entraîneur -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Compétences de l'Entraîneur</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="fas fa-cogs me-2"></i>Technique</span>
                                        <span><?php echo $coach_data['competence_technique'] ?? '0'; ?>/10</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: <?php echo ($coach_data['competence_technique'] ?? 0) * 10; ?>%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="fas fa-chess me-2"></i>Tactique</span>
                                        <span><?php echo $coach_data['competence_tactique'] ?? '0'; ?>/10</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: <?php echo ($coach_data['competence_tactique'] ?? 0) * 10; ?>%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="fas fa-dumbbell me-2"></i>Physique</span>
                                        <span><?php echo $coach_data['competence_physique'] ?? '0'; ?>/10</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: <?php echo ($coach_data['competence_physique'] ?? 0) * 10; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="fas fa-brain me-2"></i>Mental</span>
                                        <span><?php echo $coach_data['competence_mental'] ?? '0'; ?>/10</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" style="width: <?php echo ($coach_data['competence_mental'] ?? 0) * 10; ?>%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="fas fa-users me-2"></i>Management</span>
                                        <span><?php echo $coach_data['competence_management'] ?? '0'; ?>/10</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" style="width: <?php echo ($coach_data['competence_management'] ?? 0) * 10; ?>%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="fas fa-graduation-cap me-2"></i>Formation</span>
                                        <span><?php echo $coach_data['competence_formation'] ?? '0'; ?>/10</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-dark" style="width: <?php echo ($coach_data['competence_formation'] ?? 0) * 10; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expérience et parcours -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Expérience et Parcours</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($coach_data['experience'])): ?>
                            <div class="mb-4">
                                <h6><i class="fas fa-history me-2"></i>Expérience détaillée</h6>
                                <p><?php echo nl2br(htmlspecialchars($coach_data['experience'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-building me-2"></i>Anciens clubs</h6>
                                <p><?php echo htmlspecialchars($coach_data['anciens_clubs'] ?? 'Aucun club précédent'); ?></p>
                                
                                <h6><i class="fas fa-trophy me-2"></i>Palmarès</h6>
                                <p><?php echo htmlspecialchars($coach_data['palmares'] ?? 'Aucun palmarès'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-certificate me-2"></i>Diplômes et certifications</h6>
                                <p><?php echo htmlspecialchars($coach_data['diplomes'] ?? 'Non spécifié'); ?></p>
                                
                                <h6><i class="fas fa-language me-2"></i>Langues parlées</h6>
                                <p><?php echo htmlspecialchars($coach_data['langues'] ?? 'Non spécifié'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Méthodologie et approche -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Méthodologie et Approche</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($coach_data['methodologie'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($coach_data['methodologie'])); ?></p>
                        <?php else: ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-target me-2"></i>Objectifs</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>Développement technique</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Amélioration tactique</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Préparation physique</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Mental et motivation</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-tools me-2"></i>Méthodes</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>Entraînements personnalisés</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Analyse vidéo</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Suivi individuel</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Évaluation continue</li>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Services proposés -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Services Proposés</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-user-check text-primary me-3 fa-2x"></i>
                                    <div>
                                        <h6 class="mb-1">Entraînement individuel</h6>
                                        <small class="text-muted">Séances personnalisées selon vos besoins</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-users text-success me-3 fa-2x"></i>
                                    <div>
                                        <h6 class="mb-1">Entraînement en groupe</h6>
                                        <small class="text-muted">Séances collectives pour améliorer la cohésion</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-video text-info me-3 fa-2x"></i>
                                    <div>
                                        <h6 class="mb-1">Analyse vidéo</h6>
                                        <small class="text-muted">Analyse détaillée de vos performances</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-brain text-warning me-3 fa-2x"></i>
                                    <div>
                                        <h6 class="mb-1">Préparation mentale</h6>
                                        <small class="text-muted">Techniques de concentration et motivation</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                                <a href="profile.php?id=<?php echo $coach_id; ?>" class="btn btn-outline-primary">
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
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistiques</h5>
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
                            <a href="contact.php?entraineur_id=<?php echo $coach_id; ?>" class="btn btn-success w-100">
                                <i class="fas fa-envelope me-2"></i>Contacter cet entraîneur
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
                            Pour contacter cet entraîneur, utilisez le formulaire de contact. 
                            Vos coordonnées seront transmises directement.
                        </p>
                        <div class="d-grid gap-2">
                            <a href="contact.php?entraineur_id=<?php echo $coach_id; ?>" class="btn btn-success">
                                <i class="fas fa-envelope me-2"></i>Envoyer un message
                            </a>
                            <a href="tel:+213XXXXXXXX" class="btn btn-outline-primary">
                                <i class="fas fa-phone me-2"></i>Appeler
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Entraîneurs similaires -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Entraîneurs Similaires</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Découvrez d'autres entraîneurs avec une spécialité similaire.</p>
                        <div class="d-grid">
                            <a href="entraineurs.php?specialite=<?php echo urlencode($coach_data['specialite'] ?? ''); ?>" class="btn btn-outline-success">
                                <i class="fas fa-search me-2"></i>Voir les <?php echo htmlspecialchars($coach_data['specialite'] ?? 'entraîneurs'); ?>
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

