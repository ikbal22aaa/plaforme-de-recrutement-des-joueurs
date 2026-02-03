<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error_message = '';
$success_message = '';

// Récupérer les paramètres de contact
$player_id = isset($_GET['player_id']) ? (int)$_GET['player_id'] : null;
$entraineur_id = isset($_GET['entraineur_id']) ? (int)$_GET['entraineur_id'] : null;
$club_id = isset($_GET['club_id']) ? (int)$_GET['club_id'] : null;

$contact_user = null;
$contact_type = '';

if ($player_id) {
    $contact_user = getJoueurProfile($pdo, $player_id);
    $contact_type = 'joueur';
} elseif ($entraineur_id) {
    $contact_user = getEntraineurProfile($pdo, $entraineur_id);
    $contact_type = 'entraineur';
} elseif ($club_id) {
    $contact_user = getClubProfile($pdo, $club_id);
    $contact_type = 'club';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $subject = cleanInput($_POST['subject']);
    $message = cleanInput($_POST['message']);
    $contact_user_id = (int)$_POST['contact_user_id'];
    $contact_type = cleanInput($_POST['contact_type']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Veuillez remplir tous les champs obligatoires';
    } elseif (!validateEmail($email)) {
        $error_message = 'Adresse email invalide';
    } else {
        try {
            // Insérer le message dans la base de données
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message) VALUES (?, ?, ?, ?)");
            
            // Si l'utilisateur est connecté, utiliser son ID, sinon 0 pour les visiteurs
            $sender_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            
            $stmt->execute([$sender_id, $contact_user_id, $subject, $message]);
            
            // Envoyer une notification à l'utilisateur contacté
            if ($contact_user_id) {
                sendNotification($pdo, $contact_user_id, 'Nouveau message', "Vous avez reçu un nouveau message de $name", 'info');
            }
            
            $success_message = 'Votre message a été envoyé avec succès !';
            
            // Réinitialiser le formulaire
            $_POST = [];
            
        } catch (PDOException $e) {
            $error_message = 'Erreur lors de l\'envoi du message';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - KoraJob</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
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
                        <a class="nav-link" href="entraineurs.php">Entraîneurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact</a>
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

    <!-- Header -->
    <div class="bg-info text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-3">
                        <i class="fas fa-envelope me-3"></i>Contact
                    </h1>
                    <p class="lead">
                        Contactez les joueurs, entraîneurs et clubs de la plateforme KoraJob. 
                        Mettez-vous en relation pour créer des opportunités.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row">
            <!-- Formulaire de contact -->
            <div class="col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-paper-plane me-2"></i>
                            <?php if ($contact_user): ?>
                                Contacter <?php echo htmlspecialchars($contact_user['nom']); ?>
                            <?php else: ?>
                                Envoyer un message
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <?php if ($contact_user): ?>
                                <input type="hidden" name="contact_user_id" value="<?php echo $contact_user['user_id']; ?>">
                                <input type="hidden" name="contact_type" value="<?php echo $contact_type; ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user me-2"></i>Nom complet *
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : (isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''); ?>" 
                                           required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email *
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : (isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''); ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label">
                                    <i class="fas fa-tag me-2"></i>Sujet *
                                </label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" 
                                       placeholder="Objet de votre message..." required>
                            </div>

                            <div class="mb-4">
                                <label for="message" class="form-label">
                                    <i class="fas fa-comment me-2"></i>Message *
                                </label>
                                <textarea class="form-control" id="message" name="message" rows="6" 
                                          placeholder="Décrivez votre demande, proposition ou question..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Envoyer le message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Informations de contact -->
            <div class="col-lg-4">
                <?php if ($contact_user): ?>
                    <!-- Profil de la personne contactée -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Profil contacté
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="assets/images/default-avatar.jpg" alt="Avatar" 
                                     class="rounded-circle me-3" width="50" height="50">
                                <div>
                                    <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($contact_user['nom']); ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-<?php echo $contact_type === 'joueur' ? 'user' : ($contact_type === 'entraineur' ? 'whistle' : 'building'); ?> me-1"></i>
                                        <?php echo ucfirst($contact_type); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <?php if ($contact_type === 'joueur'): ?>
                                <div class="row small">
                                    <div class="col-6">
                                        <strong>Position:</strong><br>
                                        <?php echo htmlspecialchars($contact_user['position'] ?? 'Non spécifié'); ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Âge:</strong><br>
                                        <?php echo $contact_user['age'] ?? 'Non spécifié'; ?> ans
                                    </div>
                                </div>
                            <?php elseif ($contact_type === 'entraineur'): ?>
                                <div class="row small">
                                    <div class="col-6">
                                        <strong>Spécialité:</strong><br>
                                        <?php echo htmlspecialchars($contact_user['specialite'] ?? 'Non spécifié'); ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Nationalité:</strong><br>
                                        <?php echo htmlspecialchars($contact_user['nationalite'] ?? 'Non spécifié'); ?>
                                    </div>
                                </div>
                            <?php elseif ($contact_type === 'club'): ?>
                                <div class="row small">
                                    <div class="col-6">
                                        <strong>Club:</strong><br>
                                        <?php echo htmlspecialchars($contact_user['nom_club'] ?? 'Non spécifié'); ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Ville:</strong><br>
                                        <?php echo htmlspecialchars($contact_user['ville'] ?? 'Non spécifié'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Informations générales -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Informations
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="fw-bold">
                                <i class="fas fa-envelope me-2 text-primary"></i>Email général
                            </h6>
                            <p class="mb-0">contact@korajob.com</p>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold">
                                <i class="fas fa-phone me-2 text-success"></i>Téléphone
                            </h6>
                            <p class="mb-0">+213 XXX XXX XXX</p>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold">
                                <i class="fas fa-map-marker-alt me-2 text-danger"></i>Adresse
                            </h6>
                            <p class="mb-0">Alger, Algérie</p>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold">
                                <i class="fas fa-clock me-2 text-warning"></i>Horaires
                            </h6>
                            <p class="mb-0">Lun - Ven: 9h00 - 18h00</p>
                        </div>

                        <hr>

                        <div class="text-center">
                            <h6 class="fw-bold mb-3">Suivez-nous</h6>
                            <div class="social-links">
                                <a href="#" class="text-primary me-3">
                                    <i class="fab fa-facebook fa-lg"></i>
                                </a>
                                <a href="#" class="text-info me-3">
                                    <i class="fab fa-twitter fa-lg"></i>
                                </a>
                                <a href="#" class="text-danger me-3">
                                    <i class="fab fa-instagram fa-lg"></i>
                                </a>
                                <a href="#" class="text-primary">
                                    <i class="fab fa-linkedin fa-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conseils -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>Conseils
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Soyez précis dans votre demande
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Mentionnez votre expérience
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Proposez un rendez-vous
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Restez professionnel
                            </li>
                        </ul>
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
    <script>
        // Auto-resize du textarea
        document.getElementById('message').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });

        // Validation en temps réel
        document.querySelectorAll('input[required], textarea[required]').forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        });

        // Validation de l'email
        document.getElementById('email').addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    </script>
</body>
</html>

