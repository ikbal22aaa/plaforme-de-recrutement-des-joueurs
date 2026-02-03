<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login_simple.php');
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'Utilisateur';
$user_type = $_SESSION['user_type'] ?? 'user';
$user_email = $_SESSION['user_email'] ?? '';

// Couleurs selon le type d'utilisateur
$type_colors = [
    'admin' => 'danger',
    'joueur' => 'primary', 
    'entraineur' => 'success',
    'club' => 'info'
];

$type_labels = [
    'admin' => 'Administrateur',
    'joueur' => 'Joueur',
    'entraineur' => 'Entraîneur', 
    'club' => 'Club'
];

$color = $type_colors[$user_type] ?? 'secondary';
$label = $type_labels[$user_type] ?? 'Utilisateur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - KoraJob</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
        }
        .stat-card {
            border-radius: 15px;
            border-left: 4px solid;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card.primary { border-left-color: #1976D2; }
        .stat-card.success { border-left-color: #4CAF50; }
        .stat-card.warning { border-left-color: #FF9800; }
        .stat-card.info { border-left-color: #03DAC6; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="assets/images/logo.svg" alt="KoraJob" width="40" class="me-2">KoraJob
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard_simple.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="joueurs.php">Joueurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="entraineurs.php">Entraîneurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user_name); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="showProfile()">Profil</a></li>
                            <li><a class="dropdown-item" href="#" onclick="showSettings()">Paramètres</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout_simple.php">Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-2">
                        Bienvenue, <?php echo htmlspecialchars($user_name); ?> !
                    </h1>
                    <p class="lead mb-0">Votre tableau de bord KoraJob</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <span class="badge bg-light text-dark fs-6 p-3">
                        <i class="fas fa-user-tag me-2"></i><?php echo $label; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <!-- Statistiques -->
        <div class="row mb-5">
            <div class="col-md-3 mb-4">
                <div class="card stat-card primary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-chart-line fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-1"><?php echo rand(10, 100); ?></h3>
                                <small class="text-muted">Activités cette semaine</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stat-card success h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-star fa-2x text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-1">4.<?php echo rand(5, 9); ?></h3>
                                <small class="text-muted">Note moyenne</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stat-card warning h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-envelope fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-1"><?php echo rand(1, 15); ?></h3>
                                <small class="text-muted">Messages</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stat-card info h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-eye fa-2x text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-1"><?php echo rand(50, 500); ?></h3>
                                <small class="text-muted">Visiteurs du profil</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Actions rapides -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Actions Rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php if ($user_type === 'joueur'): ?>
                                <div class="col-md-4">
                                    <a href="joueurs.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center py-4">
                                        <i class="fas fa-search fa-2x mb-2"></i>
                                        <span>Rechercher des Offres</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center py-4">
                                        <i class="fas fa-video fa-2x mb-2"></i>
                                        <span>Ajouter une Vidéo</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center py-4">
                                        <i class="fas fa-edit fa-2x mb-2"></i>
                                        <span>Compléter mon Profil</span>
                                    </a>
                                </div>
                            <?php elseif ($user_type === 'entraineur'): ?>
                                <div class="col-md-4">
                                    <a href="joueurs.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center py-4">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <span>Évaluer des Joueurs</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center py-4">
                                        <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                                        <span>Proposer des Séances</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center py-4">
                                        <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                        <span>Mes Évaluations</span>
                                    </a>
                                </div>
                            <?php elseif ($user_type === 'club'): ?>
                                <div class="col-md-4">
                                    <a href="joueurs.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center py-4">
                                        <i class="fas fa-search fa-2x mb-2"></i>
                                        <span>Recruter des Talents</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center py-4">
                                        <i class="fas fa-bullhorn fa-2x mb-2"></i>
                                        <span>Publier une Annonce</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center py-4">
                                        <i class="fas fa-calendar fa-2x mb-2"></i>
                                        <span>Organiser un Essai</span>
                                    </a>
                                </div>
                            <?php elseif ($user_type === 'admin'): ?>
                                <div class="col-md-4">
                                    <a href="admin/users.php" class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center py-4">
                                        <i class="fas fa-users-cog fa-2x mb-2"></i>
                                        <span>Gérer les Utilisateurs</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center py-4">
                                        <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                        <span>Statistiques</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center py-4">
                                        <i class="fas fa-bell fa-2x mb-2"></i>
                                        <span>Notifications</span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations utilisateur -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>Profils Utilisateur
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <img src="assets/images/avatar-<?php echo $user_type; ?>.svg" alt="Avatar" width="80" class="mb-3">
                            <h6 class="fw-bold"><?php echo htmlspecialchars($user_name); ?></h6>
                            <span class="badge bg-<?php echo $color; ?>"><?php echo $label; ?></span>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-2">
                            <strong>Email:</strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($user_email); ?></small>
                        </div>
                        
                        <div class="mb-2">
                            <strong>Membre depuis:</strong><br>
                            <small class="text-muted"><?php echo date('d/m/Y'); ?></small>
                        </div>
                        
                        <div class="mb-2">
                            <strong>Statut:</strong><br>
                            <span class="badge bg-success">Actif</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="showProfile()">
                                <i class="fas fa-edit me-1"></i>Modifier le profil
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="logout()">
                                <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Test de l'auth -->
                <div class="alert alert-info mt-3">
                    <h6><i class="fas fa-info-circle me-2"></i>Mode Test</h6>
                    <small>Vous êtes connecté en mode simulation.<br>
                    Les données sont stockées en session temporaire.</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showProfile() {
            alert('Fonctionnalité de modification du profil - À implémenter avec la vraie base de données');
        }

        function showSettings() {
            alert('Paramètres du compte - À implémenter');
        }

        function logout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = 'logout_simple.php';
            }
        }

        // Animation des statistiques
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.stat-card h3');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                let current = 0;
                const increment = target / 50;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }, 30);
            });
        });
    </script>
</body>
</html>
