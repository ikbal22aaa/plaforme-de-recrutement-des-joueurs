<?php
session_start();

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard_simple.php');
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
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index_working.php">
                <img src="assets/images/logo.svg" alt="KoraJob" width="40" class="me-2">KoraJob
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard_simple.php">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout_simple.php">Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login_simple.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Connexion
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register_simple.php">
                                <i class="fas fa-user-plus me-1"></i>Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="accueil" class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">
                        <img src="assets/images/logo.svg" alt="KoraJob" width="80" class="me-3">KoraJob
                        <span class="text-warning">Révolutionne</span> le Recrutement Footballistique
                    </h1>
                    <p class="lead text-white mb-4">
                        Connectez talents, clubs et entraîneurs sur une plateforme moderne et intuitive. 
                        Découvrez, évaluez et recrutez les meilleurs joueurs grâce à nos outils avancés.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="register_simple.php" class="btn btn-warning btn-lg px-4">
                            <i class="fas fa-rocket me-2"></i>Commencer Gratuitement
                        </a>
                        <a href="#demo" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-play me-2"></i>Voir la Démo
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <img src="assets/images/football-hero.svg" alt="KoraJob Football Platform" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section id="demo" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">🎬 Démo Interactive</h2>
                    <p class="lead text-muted">
                        Testez KoraJob dès maintenant avec nos comptes de démonstration
                    </p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm demo-card">
                        <div class="card-body text-center p-4">
                            <img src="assets/images/avatar-joueur.svg" alt="Joueur" width="60" class="mb-3">
                            <h5 class="card-title">Joueur</h5>
                            <p class="card-text text-muted small mb-3">
                                Testez les fonctionnalités de recherche d'offres et de candidature
                            </p>
                            <button class="btn btn-primary btn-sm" onclick="demoLogin('joueur@test.com', 'joueur123')">
                                <i class="fas fa-play me-1"></i>Tester
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm demo-card">
                        <div class="card-body text-center p-4">
                            <img src="assets/images/avatar-entraineur.svg" alt="Entraîneur" width="60" class="mb-3">
                            <h5 class="card-title">Entraîneur</h5>
                            <p class="card-text text-muted small mb-3">
                                Découvrez les outils d'évaluation et de formation
                            </p>
                            <button class="btn btn-success btn-sm" onclick="demoLogin('entraineur@test.com', 'entraineur123')">
                                <i class="fas fa-play me-1"></i>Tester
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm demo-card">
                        <div class="card-body text-center p-4">
                            <img src="assets/images/avatar-club.svg" alt="Club" width="60" class="mb-3">
                            <h5 class="card-title">Club</h5>
                            <p class="card-text text-muted small mb-3">
                                Explorez les fonctionnalités de recrutement
                            </p>
                            <button class="btn btn-info btn-sm" onclick="demoLogin('club@test.com', 'club123')">
                                <i class="fas fa-play me-1"></i>Tester
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm demo-card">
                        <div class="card-body text-center p-4">
                            <img src="assets/images/logo.svg" alt="Admin" width="60" class="mb-3">
                            <h5 class="card-title">Administrateur</h5>
                            <p class="card-text text-muted small mb-3">
                                Accédez au panneau d'administration complet
                            </p>
                            <button class="btn btn-danger btn-sm" onclick="demoLogin('admin@korajob.com', 'admin123')">
                                <i class="fas fa-play me-1"></i>Tester
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- -->
    <section id="services" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">Nos Services</h2>
                    <p class="lead text-muted">
                        Une plateforme complète pour tous les acteurs du football
                    </p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="service-icon mb-3">
                                <i class="fas fa-users fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Pour les Joueurs</h5>
                            <p class="card-text text-muted">
                                Créez votre profil complet, ajoutez vos vidéos de performance 
                                et trouvez les meilleures opportunités.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="service-icon mb-3">
                                <i class="fas fa-whistle fa-3x text-success"></i>
                            </div>
                            <h5 class="card-title">Pour les Entraîneurs</h5>
                            <p class="card-text text-muted">
                                Proposez vos formations, évaluez les joueurs et 
                                publiez vos offres d'emploi.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="service-icon mb-3">
                                <i class="fas fa-building fa-3x text-info"></i>
                            </div>
                            <h5 class="card-title">Pour les Clubs</h5>
                            <p class="card-text text-muted">
                                Recherchez des talents avec des filtres avancés, 
                                organisez des essais et gérez votre équipe.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="service-icon mb-3">
                                <i class="fas fa-video fa-3x text-warning"></i>
                            </div>
                            <h5 class="card-title">Analyse Vidéo</h5>
                            <p class="card-text text-muted">
                                Système de notation intégié et analyse 
                                objective des performances.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
</div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-6 fw-bold mb-4">Fonctionnalités Avancées</h2>
                    <div class="feature-list">
                        <div class="feature-item d-flex mb-3">
                            <div class="feature-icon me-3">
                                <i class="fas fa-search fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5>Recherche Intelligente</h5>
                                <p class="text-muted mb-0">Filtres avancés par âge, position, région et niveau</p>
                            </div>
                        </div>
                        <div class="feature-item d-flex mb-3">
                            <div class="feature-icon me-3">
                                <i class="fas fa-star fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h5>Système de Notation</h5>
                                <p class="text-muted mb-0">Évaluation objective des joueurs (★ à ★★★★★)</p>
                            </div>
                        </div>
                        <div class="feature-item d-flex mb-3">
                            <div class="feature-icon me-3">
                                <i class="fas fa-comments fa-2x text-success"></i>
                            </div>
                            <div>
                                <h5>Messagerie Instantanée</h5>
                                <p class="text-muted mb-0">Communication directe entre tous les acteurs</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/features.svg" alt="Fonctionnalités KoraJob" class="img-fluid rounded-3 shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="stat-item">
                        <h3 class="display-4 fw-bold text-warning">500+</h3>
                        <p class="mb-0">Joueurs Inscrits</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-item">
                        <h3 class="display-4 fw-bold text-warning">150+</h3>
                        <p class="mb-0">Clubs Actifs</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-item">
                        <h3 class="display-4 fw-bold text-warning">80+</h3>
                        <p class="mb-0">Entraîneurs</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-item">
                        <h3 class="display-4 fw-bold text-warning">1000+</h3>
                        <p class="mb-0">Recrutements Réussis</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-6 fw-bold mb-4">Prêt à Révolutionner Votre Recrutement ?</h2>
                    <p class="lead text-muted mb-4">
                        Rejoignez KoraJob dès aujourd'hui et découvrez une nouvelle façon de recruter et d'être recruté.
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="register_simple.php" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-user-plus me-2"></i>S'inscrire Gratuitement
                        </a>
                        <a href="login_simple.php" class="btn btn-outline-primary btn-lg px-5">
                            <i class="fas fa-sign-in-alt me-2"></i>Se Connecter
                        </a>
                        <a href="contact.php" class="btn btn-outline-info btn-lg px-5">
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
                        <img src="assets/images/logo.svg" alt="KoraJob" width="40" class="me-2">KoraJob
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
                    <h6 class="fw-bold mb-3">Comptes de Test</h6>
                    <ul class="list-unstyled small">
                        <li><strong>Admin:</strong> admin@korajob.com</li>
                        <li><strong>Joueur:</strong> joueur@test.com</li>
                        <li><strong>Entraîneur:</strong> entraineur@test.com</li>
                    </ul>
                    <p class="small text-muted">Mot de passe: admin123, joueur123, etc.</p>
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
    <script>
        function demoLogin(email, password) {
            const loginForm = document.createElement('form');
            loginForm.method = 'POST';
            loginForm.action = 'login_simple.php';
            loginForm.style.display = 'none';
            
            loginForm.innerHTML = `
                <input type="email" name="email" value="${email}">
                <input type="password" name="password" value="${password}">
            `;
            
            document.body.appendChild(loginForm);
            loginForm.submit();
        }

        // Animation des statistiques
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.stat-item h3');
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = entry.target;
                        const text = target.textContent;
                        const number = parseInt(text.replace(/[^\d]/g, ''));
                        
                        let current = 0;
                        const increment = number / 50;
                        
                        const timer = setInterval(() => {
                            current += increment;
                            if (current >= number) {
                                target.textContent = text;
                                clearInterval(timer);
                            } else {
                                target.textContent = Math.floor(current) + text.replace(/[\d]/g, '').replace(/\d+/, '');
                            }
                        }, 30);
                        
                        observer.unobserve(target);
                    }
                });
            });
            
            counters.forEach(counter => observer.observe(counter));
        });
    </script>
</body>
</html>
