<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Récupérer l'ID du profil à afficher
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$profile_id) {
    header('Location: index.php');
    exit();
}

// Récupérer les informations du profil
$user = null;
$profile_data = null;
$user_type = '';

try {
    // Récupérer les informations de base de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
    $stmt->execute([$profile_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: index.php');
        exit();
    }
    
    $user_type = $user['user_type'];
    
    // Récupérer les données spécifiques selon le type d'utilisateur
    switch ($user_type) {
        case 'joueur':
            $stmt = $pdo->prepare("SELECT * FROM joueurs WHERE user_id = ?");
            $stmt->execute([$profile_id]);
            $profile_data = $stmt->fetch();
            break;
        case 'entraineur':
            $stmt = $pdo->prepare("SELECT * FROM entraineurs WHERE user_id = ?");
            $stmt->execute([$profile_id]);
            $profile_data = $stmt->fetch();
            break;
        case 'club':
            $stmt = $pdo->prepare("SELECT * FROM clubs WHERE user_id = ?");
            $stmt->execute([$profile_id]);
            $profile_data = $stmt->fetch();
            break;
    }
    
    // Récupérer les évaluations
    $stmt = $pdo->prepare("
        SELECT e.*, u.nom as evaluator_name 
        FROM evaluations e 
        LEFT JOIN users u ON e.evaluator_id = u.id 
        WHERE e.evaluated_id = ? 
        ORDER BY e.created_at DESC
    ");
    $stmt->execute([$profile_id]);
    $evaluations = $stmt->fetchAll();
    
    // Calculer la note moyenne
    $avg_rating = 0;
    if (!empty($evaluations)) {
        $total_rating = array_sum(array_column($evaluations, 'rating'));
        $avg_rating = round($total_rating / count($evaluations), 1);
    }
    
} catch (PDOException $e) {
    header('Location: index.php');
    exit();
}

// Gestion du contact
$contact_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
    $name = cleanInput($_POST['contact_name']);
    $email = cleanInput($_POST['contact_email']);
    $subject = cleanInput($_POST['contact_subject']);
    $message = cleanInput($_POST['contact_message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $contact_message = '<div class="alert alert-danger">Veuillez remplir tous les champs</div>';
    } elseif (!validateEmail($email)) {
        $contact_message = '<div class="alert alert-danger">Adresse email invalide</div>';
    } else {
        try {
            $sender_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$sender_id, $profile_id, $subject, $message]);
            
            if ($profile_id) {
                sendNotification($pdo, $profile_id, 'Nouveau message', "Vous avez reçu un nouveau message de $name", 'info');
            }
            
            $contact_message = '<div class="alert alert-success">Votre message a été envoyé avec succès !</div>';
        } catch (PDOException $e) {
            $contact_message = '<div class="alert alert-danger">Erreur lors de l\'envoi du message</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['nom']); ?> - KoraJob</title>
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

    <!-- Header du profil -->
    <div class="bg-gradient py-5 mt-5" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    <img src="assets/images/default-avatar.jpg" alt="Photo de profil" 
                         class="rounded-circle shadow-lg mb-3" width="150" height="150">
                </div>
                <div class="col-md-8">
                    <h1 class="text-white fw-bold mb-2"><?php echo htmlspecialchars($user['nom']); ?></h1>
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-warning text-dark me-3 fs-6">
                            <i class="fas fa-<?php echo $user_type === 'joueur' ? 'user' : ($user_type === 'entraineur' ? 'whistle' : 'building'); ?> me-1"></i>
                            <?php echo ucfirst($user_type); ?>
                        </span>
                        <div class="rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $avg_rating ? 'text-warning' : 'text-light'; ?>"></i>
                            <?php endfor; ?>
                            <span class="text-white ms-2"><?php echo $avg_rating; ?>/5 (<?php echo count($evaluations); ?> avis)</span>
                        </div>
                    </div>
                    <?php if ($user_type === 'joueur' && isset($profile_data['position'])): ?>
                        <p class="text-white-50 mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo htmlspecialchars($profile_data['wilaya'] ?? 'Non spécifié'); ?> • 
                            <?php echo htmlspecialchars($profile_data['position']); ?>
                        </p>
                    <?php elseif ($user_type === 'entraineur' && isset($profile_data['specialite'])): ?>
                        <p class="text-white-50 mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo htmlspecialchars($profile_data['wilaya'] ?? 'Non spécifié'); ?> • 
                            <?php echo htmlspecialchars($profile_data['specialite']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row">
            <!-- Informations principales -->
            <div class="col-lg-8">
                <?php if ($user_type === 'joueur'): ?>
                    <!-- Profil Joueur -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations du Joueur</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <strong><i class="fas fa-birthday-cake me-2"></i>Âge:</strong>
                                        <span class="ms-2"><?php echo $profile_data['age'] ?? 'Non spécifié'; ?> ans</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="fas fa-map-marker-alt me-2"></i>Position:</strong>
                                        <span class="ms-2"><?php echo htmlspecialchars($profile_data['position'] ?? 'Non spécifié'); ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="fas fa-star me-2"></i>Niveau:</strong>
                                        <span class="ms-2"><?php echo htmlspecialchars($profile_data['niveau'] ?? 'Non spécifié'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <strong><i class="fas fa-map-marker-alt me-2"></i>Wilaya:</strong>
                                        <span class="ms-2"><?php echo htmlspecialchars($profile_data['wilaya'] ?? 'Non spécifié'); ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="fas fa-shoe-prints me-2"></i>Pied fort:</strong>
                                        <span class="ms-2"><?php echo htmlspecialchars($profile_data['pied_fort'] ?? 'Non spécifié'); ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="fas fa-weight me-2"></i>Taille:</strong>
                                        <span class="ms-2"><?php echo $profile_data['taille'] ?? 'Non spécifié'; ?> cm</span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($profile_data['description'])): ?>
                                <hr>
                                <div>
                                    <strong><i class="fas fa-align-left me-2"></i>Description:</strong>
                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($profile_data['description'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($profile_data['video_url'])): ?>
                                <hr>
                                <div>
                                    <strong><i class="fas fa-video me-2"></i>Vidéo de présentation:</strong>
                                    <div class="mt-2">
                                        <video controls class="w-100" style="max-height: 400px;">
                                            <source src="<?php echo htmlspecialchars($profile_data['video_url']); ?>" type="video/mp4">
                                            Votre navigateur ne supporte pas la lecture vidéo.
                                        </video>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php elseif ($user_type === 'entraineur'): ?>
                    <!-- Profil Entraîneur -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-whistle me-2"></i>Informations de l'Entraîneur</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <strong><i class="fas fa-flag me-2"></i>Nationalité:</strong>
                                        <span class="ms-2"><?php echo htmlspecialchars($profile_data['nationalite'] ?? 'Non spécifié'); ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="fas fa-map-marker-alt me-2"></i>Wilaya:</strong>
                                        <span class="ms-2"><?php echo htmlspecialchars($profile_data['wilaya'] ?? 'Non spécifié'); ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="fas fa-graduation-cap me-2"></i>Spécialité:</strong>
                                        <span class="ms-2"><?php echo htmlspecialchars($profile_data['specialite'] ?? 'Non spécifié'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <strong><i class="fas fa-language me-2"></i>Langues:</strong>
                                        <span class="ms-2"><?php echo htmlspecialchars($profile_data['langues'] ?? 'Non spécifié'); ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="fas fa-calendar-alt me-2"></i>Expérience:</strong>
                                        <span class="ms-2"><?php echo $profile_data['annees_experience'] ?? 'Non spécifié'; ?> ans</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="fas fa-certificate me-2"></i>Diplômes:</strong>
                                        <span class="ms-2"><?php echo htmlspecialchars($profile_data['diplomes'] ?? 'Non spécifié'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($profile_data['experience'])): ?>
                                <hr>
                                <div>
                                    <strong><i class="fas fa-briefcase me-2"></i>Expérience détaillée:</strong>
                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($profile_data['experience'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($profile_data['anciens_clubs'])): ?>
                                <hr>
                                <div>
                                    <strong><i class="fas fa-building me-2"></i>Anciens clubs:</strong>
                                    <p class="mt-2"><?php echo htmlspecialchars($profile_data['anciens_clubs']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Évaluations -->
                <?php if (!empty($evaluations)): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Évaluations (<?php echo count($evaluations); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($evaluations as $evaluation): ?>
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
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Formulaire de contact -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Contacter</h5>
                    </div>
                    <div class="card-body">
                        <?php echo $contact_message; ?>
                        
                        <?php if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $profile_id): ?>
                            <form method="POST">
                                <input type="hidden" name="contact_form" value="1">
                                
                                <div class="mb-3">
                                    <label for="contact_name" class="form-label">Votre nom</label>
                                    <input type="text" class="form-control" id="contact_name" name="contact_name" 
                                           value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">Votre email</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                           value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_subject" class="form-label">Sujet</label>
                                    <input type="text" class="form-control" id="contact_subject" name="contact_subject" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_message" class="form-label">Message</label>
                                    <textarea class="form-control" id="contact_message" name="contact_message" rows="4" required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Envoyer
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="text-muted">Vous ne pouvez pas vous contacter vous-même.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistiques</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="text-primary mb-1"><?php echo count($evaluations); ?></h4>
                                    <small class="text-muted">Évaluations</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="text-warning mb-1"><?php echo $avg_rating; ?>/5</h4>
                                <small class="text-muted">Note moyenne</small>
                            </div>
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

