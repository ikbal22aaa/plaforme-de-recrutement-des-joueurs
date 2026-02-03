<?php
session_start();

// Simulation de stockage des utilisateurs (en session pour cette demo)
if (!isset($_SESSION['registered_users'])) {
    $_SESSION['registered_users'] = [];
}

$error_message = '';
$success_message = '';

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    header('Location: login_simple.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['password_confirmation'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    
    // Validation
    if (empty($nom) || empty($email) || empty($password) || empty($confirm_password) || empty($user_type)) {
        $error_message = 'Veuillez remplir tous les champs obligatoires';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Adresse email invalide';
    } elseif (strlen($password) < 6) {
        $error_message = 'Le mot de passe doit contenir au moins 6 caractères';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Les mots de passe ne correspondent pas';
    } elseif (!in_array($user_type, ['joueur', 'entraineur', 'club'])) {
        $error_message = 'Type d\'utilisateur invalide';
    } elseif (isset($_SESSION['registered_users'][$email])) {
        $error_message = 'Cette adresse email est déjà utilisée';
    } else {
        // Enregistrer le nouvel utilisateur
        $_SESSION['registered_users'][$email] = [
            'nom' => $nom,
            'password' => $password, // En production, hasher le mot de passe
            'user_type' => $user_type,
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ];
        
        // Connecter automatiquement l'utilisateur
        $_SESSION['user_id'] = count($_SESSION['registered_users']);
        $_SESSION['user_type'] = $user_type;
        $_SESSION['user_name'] = $nom;
        $_SESSION['user_email'] = $email;
        
        $success_message = 'Compte créé avec succès ! Vous êtes maintenant connecté.';
        
        // Redirection vers le dashboard approprié
        sleep(2); // Attendre 2 secondes pour que l'utilisateur voit le message
        switch ($user_type) {
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
}

// Obtenir la liste des utilisateurs enregistrés pour l'affichage
$total_users = count($_SESSION['registered_users']);
$type_counts = ['joueur' => 0, 'entraineur' => 0, 'club' => 0];
foreach ($_SESSION['registered_users'] as $user) {
    if (isset($type_counts[$user['user_type']])) {
        $type_counts[$user['user_type']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - KoraJob</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .register-card {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .card-body {
            padding: 2rem;
        }
        .stats-card {
            background: rgba(255,255,255,0.9);
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Header -->
        <div class="row justify-content-center mb-4">
            <div class="col-12 text-center text-white">
                <img src="assets/images/logo.svg" alt="KoraJob" width="100" class="mb-3">
                <h1 class="display-6 fw-bold">Rejoignez KoraJob</h1>
                <p class="lead">Connectez-vous à la communauté footballistique algérienne</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <!-- Statistiques -->
            <div class="col-lg-4 mb-4">
                <div class="stats-card">
                    <h5 class="text-primary mb-3"><i class="fas fa-chart-bar me-2"></i>Communauté</h5>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-primary mb-1"><?php echo $total_users; ?></h4>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-success mb-1"><?php echo $type_counts['joueur']; ?></h4>
                                <small class="text-muted">Joueurs</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="text-info mb-1"><?php echo $type_counts['entraineur'] + $type_counts['club']; ?></h4>
                            <small class="text-muted">Professionnels</small>
                        </div>
                    </div>
                </div>

                <!-- Types de comptes -->
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="fas fa-user fa-2x text-primary mb-3"></i>
                                <h6 class="fw-bold">Joueur</h6>
                                <small class="text-muted">Créez votre profil et trouvez des opportunités</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="fas fa-whistle fa-2x text-success mb-3"></i>
                                <h6 class="fw-bold">Entraîneur</h6>
                                <small class="text-muted">Proposez vos services et évaluez les joueurs</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-building fa-2x text-info mb-3"></i>
                                <h6 class="fw-bold">Club</h6>
                                <small class="text-muted">Recrutez des talents et gérez votre équipe</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire d'inscription -->
            <div class="col-lg-8">
                <div class="card register-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="mb-0 fw-bold text-primary">
                                <i class="fas fa-user-plus me-2"></i>Créer mon compte
                            </h3>
                            <small class="text-muted">C'est gratuit et rapide</small>
                        </div>

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
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">
                                        <i class="fas fa-user me-2"></i>Nom complet *
                                    </label>
                                    <input type="text" class="form-control" id="nom" name="nom" 
                                           value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" 
                                           required autofocus>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email *
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Mot de passe *
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <small class="text-muted">Minimum 6 caractères</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Confirmer le mot de passe *
                                    </label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="user_type" class="form-label">
                                    <i class="fas fa-users me-2"></i>Type de compte *
                                </label>
                                <select class="form-select form-select-lg" id="user_type" name="user_type" required>
                                    <option value="">Sélectionnez votre profil</option>
                                    <option value="joueur" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'joueur') ? 'selected' : ''; ?>>
                                        🏃‍♂️ Joueur - Trouver des clubs et opportunités
                                    </option>
                                    <option value="entraineur" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'entraineur') ? 'selected' : ''; ?>>
                                        🎯 Entraîneur - Proposer vos services
                                    </option>
                                    <option value="club" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'club') ? 'selected' : ''; ?>>
                                        ⚽ Club - Recruter des talents
                                    </option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        J'accepte les <a href="#" class="text-decoration-none">conditions d'utilisation</a> 
                                        et la <a href="#" class="text-decoration-none">politique de confidentialité</a> *
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Créer mon compte
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-3">Déjà un compte ?</p>
                            <a href="login_simple.php" class="btn btn-outline-primary btn-lg w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                            </a>
                        </div>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <a href="index.php" class="text-decoration-none">
                                    <i class="fas fa-home me-1"></i>Retour à l'accueil
                                </a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation en temps réel du mot de passe
        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });

        // Validation de la force du mot de passe
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = getPasswordStrength(password);
            
            this.classList.remove('border-danger', 'border-warning', 'border-success');
            
            if (password.length > 0) {
                if (strength < 2) {
                    this.classList.add('border-danger');
                } else if (strength < 4) {
                    this.classList.add('border-warning');
                } else {
                    this.classList.add('border-success');
                }
            }
        });

        function getPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            return strength;
        }
    </script>
</body>
</html>
