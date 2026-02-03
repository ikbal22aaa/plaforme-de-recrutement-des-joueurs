<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier l'authentification et le type d'utilisateur
requireAuth('joueur');

$user_id = $_SESSION['user_id'];
$joueur = getJoueurProfile($pdo, $user_id);

// Récupérer les évaluations reçues
try {
    $stmt = $pdo->prepare("
        SELECT e.*, u.nom as evaluator_name, u.user_type as evaluator_type,
               cl.nom_club, cl.ville
        FROM evaluations e
        LEFT JOIN users u ON e.evaluator_id = u.id
        LEFT JOIN clubs cl ON u.id = cl.user_id
        WHERE e.evaluated_id = ?
        ORDER BY e.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $evaluations = $stmt->fetchAll();
    
    // Calculer les statistiques
    $stats = [
        'total' => count($evaluations),
        'moyenne' => 0,
        'precision' => 0,
        'vitesse' => 0,
        'force' => 0,
        'technique' => 0,
        'esprit_equipe' => 0,
        'experience' => 0
    ];
    
    if (!empty($evaluations)) {
        $stats['moyenne'] = round(array_sum(array_column($evaluations, 'note_generale')) / count($evaluations), 1);
        $stats['precision'] = round(array_sum(array_column($evaluations, 'precision')) / count($evaluations), 1);
        $stats['vitesse'] = round(array_sum(array_column($evaluations, 'vitesse')) / count($evaluations), 1);
        $stats['force'] = round(array_sum(array_column($evaluations, 'force')) / count($evaluations), 1);
        $stats['technique'] = round(array_sum(array_column($evaluations, 'technique')) / count($evaluations), 1);
        $stats['esprit_equipe'] = round(array_sum(array_column($evaluations, 'esprit_equipe')) / count($evaluations), 1);
        $stats['experience'] = round(array_sum(array_column($evaluations, 'experience')) / count($evaluations), 1);
    }
    
} catch (PDOException $e) {
    $evaluations = [];
    $stats = ['total' => 0, 'moyenne' => 0, 'precision' => 0, 'vitesse' => 0, 'force' => 0, 'technique' => 0, 'esprit_equipe' => 0, 'experience' => 0];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluations - KoraJob</title>
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
                                <a class="nav-link" href="candidatures.php">
                                    <i class="fas fa-paper-plane me-2"></i>Candidatures
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="messages.php">
                                    <i class="fas fa-envelope me-2"></i>Messages
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="evaluations.php">
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
                        <h2 class="fw-bold mb-1">Évaluations</h2>
                        <p class="text-muted mb-0">Consultez les évaluations de votre performance par les clubs et entraîneurs</p>
                    </div>
                    <div>
                        <a href="profile.php" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-2"></i>Améliorer mon profil
                        </a>
                    </div>
                </div>

                <!-- Note moyenne -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card bg-gradient-primary text-white">
                            <div class="card-body text-center">
                                <h1 class="display-4 mb-2"><?php echo $stats['moyenne']; ?>/10</h1>
                                <h5 class="mb-3">Note moyenne générale</h5>
                                <div class="d-flex justify-content-center">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= round($stats['moyenne'] / 2)): ?>
                                            <i class="fas fa-star fa-lg me-1"></i>
                                        <?php else: ?>
                                            <i class="far fa-star fa-lg me-1"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <small class="mt-2 d-block">Basé sur <?php echo $stats['total']; ?> évaluation<?php echo $stats['total'] > 1 ? 's' : ''; ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques détaillées -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-bullseye fa-2x text-primary mb-2"></i>
                                <h5>Précision</h5>
                                <h3 class="text-primary"><?php echo $stats['precision']; ?>/10</h3>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" style="width: <?php echo $stats['precision'] * 10; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-running fa-2x text-success mb-2"></i>
                                <h5>Vitesse</h5>
                                <h3 class="text-success"><?php echo $stats['vitesse']; ?>/10</h3>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: <?php echo $stats['vitesse'] * 10; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-dumbbell fa-2x text-warning mb-2"></i>
                                <h5>Force</h5>
                                <h3 class="text-warning"><?php echo $stats['force']; ?>/10</h3>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" style="width: <?php echo $stats['force'] * 10; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-futbol fa-2x text-info mb-2"></i>
                                <h5>Technique</h5>
                                <h3 class="text-info"><?php echo $stats['technique']; ?>/10</h3>
                                <div class="progress">
                                    <div class="progress-bar bg-info" style="width: <?php echo $stats['technique'] * 10; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x text-secondary mb-2"></i>
                                <h5>Esprit d'équipe</h5>
                                <h3 class="text-secondary"><?php echo $stats['esprit_equipe']; ?>/10</h3>
                                <div class="progress">
                                    <div class="progress-bar bg-secondary" style="width: <?php echo $stats['esprit_equipe'] * 10; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-trophy fa-2x text-dark mb-2"></i>
                                <h5>Expérience</h5>
                                <h3 class="text-dark"><?php echo $stats['experience']; ?>/10</h3>
                                <div class="progress">
                                    <div class="progress-bar bg-dark" style="width: <?php echo $stats['experience'] * 10; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des évaluations -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Historique des évaluations
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($evaluations)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucune évaluation reçue</h5>
                                <p class="text-muted">Les clubs et entraîneurs pourront vous évaluer après avoir travaillé avec vous</p>
                                <a href="profile.php" class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>Compléter mon profil
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($evaluations as $evaluation): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="me-3">
                                                        <?php if ($evaluation['evaluator_type'] === 'club'): ?>
                                                            <i class="fas fa-building text-primary fa-2x"></i>
                                                        <?php elseif ($evaluation['evaluator_type'] === 'entraineur'): ?>
                                                            <i class="fas fa-whistle text-success fa-2x"></i>
                                                        <?php else: ?>
                                                            <i class="fas fa-user text-info fa-2x"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <?php echo htmlspecialchars($evaluation['evaluator_name'] ?? 'Anonyme'); ?>
                                                            <?php if ($evaluation['nom_club']): ?>
                                                                <small class="text-muted">- <?php echo htmlspecialchars($evaluation['nom_club']); ?></small>
                                                            <?php endif; ?>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($evaluation['ville'] ?? ''); ?>
                                                            • <?php echo formatDate($evaluation['created_at']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($evaluation['commentaire'])): ?>
                                                    <div class="alert alert-light">
                                                        <i class="fas fa-quote-left me-2"></i>
                                                        <?php echo nl2br(htmlspecialchars($evaluation['commentaire'])); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center mb-3">
                                                    <h3 class="text-primary mb-1"><?php echo $evaluation['note_generale']; ?>/10</h3>
                                                    <div class="d-flex justify-content-center">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <?php if ($i <= round($evaluation['note_generale'] / 2)): ?>
                                                                <i class="fas fa-star text-warning me-1"></i>
                                                            <?php else: ?>
                                                                <i class="far fa-star text-warning me-1"></i>
                                                            <?php endif; ?>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="row text-center">
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted">Précision</small>
                                                        <div class="fw-bold"><?php echo $evaluation['precision']; ?></div>
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted">Vitesse</small>
                                                        <div class="fw-bold"><?php echo $evaluation['vitesse']; ?></div>
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted">Force</small>
                                                        <div class="fw-bold"><?php echo $evaluation['force']; ?></div>
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted">Technique</small>
                                                        <div class="fw-bold"><?php echo $evaluation['technique']; ?></div>
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted">Équipe</small>
                                                        <div class="fw-bold"><?php echo $evaluation['esprit_equipe']; ?></div>
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted">Expérience</small>
                                                        <div class="fw-bold"><?php echo $evaluation['experience']; ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Conseils d'amélioration -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>Conseils d'amélioration
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-target me-2 text-primary"></i>Précision</h6>
                                <p class="text-muted">Travaillez vos passes courtes et longues, vos tirs au but et votre placement.</p>
                                
                                <h6><i class="fas fa-running me-2 text-success"></i>Vitesse</h6>
                                <p class="text-muted">Améliorez votre vitesse de course et votre réactivité sur le terrain.</p>
                                
                                <h6><i class="fas fa-dumbbell me-2 text-warning"></i>Force</h6>
                                <p class="text-muted">Renforcez votre physique avec des exercices de musculation adaptés.</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-futbol me-2 text-info"></i>Technique</h6>
                                <p class="text-muted">Développez votre maîtrise du ballon et vos gestes techniques.</p>
                                
                                <h6><i class="fas fa-users me-2 text-secondary"></i>Esprit d'équipe</h6>
                                <p class="text-muted">Participez activement aux entraînements et soutenez vos coéquipiers.</p>
                                
                                <h6><i class="fas fa-trophy me-2 text-dark"></i>Expérience</h6>
                                <p class="text-muted">Accumulez de l'expérience en jouant régulièrement et en variant les situations.</p>
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

