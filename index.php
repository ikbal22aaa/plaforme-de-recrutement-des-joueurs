<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    $user_type = $_SESSION['user_type'];
    switch ($user_type) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'joueur':
            header('Location: joueur/dashboard.php');
            break;
        case 'entraineur':
            header('Location: entraineur/dashboard.php');
            break;
        case 'club':
            header('Location: club/dashboard.php');
            break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KoraJob - Plateforme de Recrutement Footballistique</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
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
                        <a class="nav-link" href="#accueil">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
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
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">
                            <i class="fas fa-user-plus me-1"></i>Inscription
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section - Exact Kestra Style -->
    <section id="accueil" class="hero-section">
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">
                        Recrutement Puissant.
                        <span style="background: linear-gradient(135deg, #16a34a, #059669); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Workflows Simplifiés.</span>
                    </h1>
                        <p class="hero-subtitle">
                        Unifiez le recrutement pour tous les acteurs du football. Connectez talents, clubs et entraîneurs — Découvrez, évaluez et recrutez les meilleurs joueurs.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                        <a href="register.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket me-2"></i>Commencer Maintenant
                            </a>
                        <a href="#services" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-info-circle me-2"></i>En Savoir Plus
                            </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <img src="assets/images/football-hero.svg" alt="KoraJob Football Platform" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section - Exact Kestra Style -->
    <section id="services" class="py-5" style="background: var(--bg-secondary);">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 style="font-size: 2.5rem; font-weight: 700;">Plateforme de Recrutement Open Source</h2>
                    <p class="lead" style="font-size: 1.25rem;">
                        Une solution complète pour tous les acteurs du football
                    </p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="service-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5 class="card-title">Pour les Joueurs</h5>
                            <p class="card-text">
                                Créez votre profil complet, ajoutez vos vidéos de performance 
                                et trouvez les meilleures opportunités.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="service-icon">
                                <img src="assets/images/logo-entraineur.svg" alt="Logo Entraîneurs" style="width: 40px; height: 40px;">
                            </div>
                            <h5 class="card-title">Pour les Entraîneurs</h5>
                            <p class="card-text">
                                Proposez vos formations, évaluez les joueurs et 
                                publiez vos offres d'emploi.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="service-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <h5 class="card-title">Pour les Clubs</h5>
                            <p class="card-text">
                                Recherchez des talents avec des filtres avancés, 
                                organisez des essais et gérez votre équipe.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="service-icon">
                                <i class="fas fa-video"></i>
                            </div>
                            <h5 class="card-title">Analyse Vidéo</h5>
                            <p class="card-text">
                                Système de notation intégré et analyse 
                                objective des performances.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section - Exact Vibor Style -->
    <section class="py-5" style="background: var(--bg-primary);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="mb-4">Fonctionnalités Avancées</h2>
                    <div class="feature-list">
                        <div class="feature-item d-flex mb-3">
                            <div class="feature-icon me-3">
                                <i class="fas fa-search"></i>
                            </div>
                            <div>
                                <h5>Recherche Intelligente</h5>
                                <p class="mb-0">Filtres avancés par âge, position, région et niveau</p>
                            </div>
                        </div>
                        <div class="feature-item d-flex mb-3">
                            <div class="feature-icon me-3">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>
                                <h5>Système de Notation</h5>
                                <p class="mb-0">Évaluation objective des joueurs (★ à ★★★★★)</p>
                            </div>
                        </div>
                        <div class="feature-item d-flex mb-3">
                            <div class="feature-icon me-3">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div>
                                <h5>Messagerie Instantanée</h5>
                                <p class="mb-0">Communication directe entre tous les acteurs</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/features.svg" alt="Fonctionnalités KoraJob" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Technical Specifications Section - Exact Vibor Style -->
    <section class="py-5" style="background: var(--bg-secondary);">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="mb-3">Spécifications Techniques</h2>
                    <p class="lead">
                        Une plateforme robuste et sécurisée pour le recrutement professionnel
                    </p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="service-icon mb-3">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h5 class="card-title">Sécurité</h5>
                            <p class="card-text">
                                Chiffrement SSL/TLS, authentification sécurisée et protection des données personnelles conformément au RGPD.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="service-icon mb-3">
                                <i class="fas fa-cloud"></i>
                            </div>
                            <h5 class="card-title">Cloud</h5>
                            <p class="card-text">
                                Infrastructure cloud haute disponibilité avec sauvegarde automatique et récupération de données.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="service-icon mb-3">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h5 class="card-title">Mobile</h5>
                            <p class="card-text">
                                Interface responsive optimisée pour tous les appareils avec application mobile native.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="service-icon mb-3">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h5 class="card-title">API</h5>
                            <p class="card-text">
                                API REST complète pour intégration avec vos systèmes existants et automatisation des processus.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section - Exact Vibor Style -->
    <section class="py-5" style="background: var(--bg-primary);">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="stat-item">
                        <h3>500+</h3>
                        <p>Joueurs Inscrits</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-item">
                        <h3>150+</h3>
                        <p>Clubs Actifs</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-item">
                        <h3>80+</h3>
                        <p>Entraîneurs</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-item">
                        <h3>1000+</h3>
                        <p>Recrutements Réussis</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section - Exact Vibor Style -->
    <section class="py-5" style="background: var(--bg-secondary);">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="mb-4">Prêt à Révolutionner Votre Recrutement ?</h2>
                    <p class="lead mb-4">
                        Rejoignez KoraJob dès aujourd'hui et découvrez une nouvelle façon de recruter et d'être recruté.
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="register.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>S'inscrire Gratuitement
                        </a>
                        <a href="contact.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-envelope me-2"></i>Nous Contacter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-futbol me-2"></i>KoraJob
                    </h5>
                    <p class="text-muted">
                        La plateforme de référence pour le recrutement footballistique moderne. 
                        Connectons les talents du football.
                    </p>
                </div>
                <div class="col-lg-2 mb-4">
                    <h6 class="fw-bold mb-3">Liens Rapides</h6>
                    <ul class="list-unstyled">
                        <li><a href="joueurs.php" class="text-muted text-decoration-none">Joueurs</a></li>
                        <li><a href="entraineurs.php" class="text-muted text-decoration-none">Entraîneurs</a></li>
                        <li><a href="contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h6 class="fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Aide</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Conditions</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h6 class="fw-bold mb-3">Suivez-nous</h6>
                    <div class="social-links">
                        <a href="#" class="text-muted me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; 2024 KoraJob. Tous droits réservés.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted">Développé avec <i class="fas fa-heart text-danger"></i> pour le football</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

