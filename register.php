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

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = cleanInput($_POST['nom']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = cleanInput($_POST['user_type']);
    
    // Validation
    if (empty($nom) || empty($email) || empty($password) || empty($confirm_password) || empty($user_type)) {
        $error_message = 'Veuillez remplir tous les champs';
    } elseif (!validateEmail($email)) {
        $error_message = 'Adresse email invalide';
    } elseif (!validatePassword($password)) {
        $error_message = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Les mots de passe ne correspondent pas';
    } elseif (!in_array($user_type, ['joueur', 'entraineur', 'club'])) {
        $error_message = 'Type d\'utilisateur invalide';
    } else {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error_message = 'Cette adresse email est déjà utilisée';
            } else {
                // Créer le compte
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (nom, email, password, user_type, status) VALUES (?, ?, ?, ?, 'pending')");
                $stmt->execute([$nom, $email, $hashed_password, $user_type]);
                
                $user_id = $pdo->lastInsertId();
                
                // Créer le profil selon le type
                switch ($user_type) {
                    case 'joueur':
                        $stmt = $pdo->prepare("INSERT INTO joueurs (user_id) VALUES (?)");
                        $stmt->execute([$user_id]);
                        break;
                    case 'entraineur':
                        $stmt = $pdo->prepare("INSERT INTO entraineurs (user_id) VALUES (?)");
                        $stmt->execute([$user_id]);
                        break;
                    case 'club':
                        $stmt = $pdo->prepare("INSERT INTO clubs (user_id) VALUES (?)");
                        $stmt->execute([$user_id]);
                        break;
                }
                
                $success_message = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
                
                // Rediriger vers la page de connexion après 3 secondes
                header("refresh:3;url=login.php");
            }
        } catch (PDOException $e) {
            $error_message = 'Erreur lors de la création du compte';
        }
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
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-futbol me-2"></i>KoraJob
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home me-1"></i>Accueil
                </a>
                <a class="nav-link" href="login.php">
                    <i class="fas fa-sign-in-alt me-1"></i>Connexion
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold">Inscription</h2>
                            <p class="text-muted">Rejoignez la communauté KoraJob</p>
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
                                        <i class="fas fa-user me-2"></i>Nom complet
                                    </label>
                                    <input type="text" class="form-control" id="nom" name="nom" 
                                           value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" 
                                           required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Mot de passe
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Min. 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Confirmer le mot de passe
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="user_type" class="form-label">
                                    <i class="fas fa-users me-2"></i>Type de compte
                                </label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="">Sélectionnez votre profil</option>
                                    <option value="joueur" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'joueur') ? 'selected' : ''; ?>>
                                        <i class="fas fa-user"></i> Joueur
                                    </option>
                                    <option value="entraineur" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'entraineur') ? 'selected' : ''; ?>>
                                        <i class="fas fa-whistle"></i> Entraîneur
                                    </option>
                                    <option value="club" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'club') ? 'selected' : ''; ?>>
                                        <i class="fas fa-building"></i> Club
                                    </option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        J'accepte les <a href="#" class="text-decoration-none">conditions d'utilisation</a> 
                                        et la <a href="#" class="text-decoration-none">politique de confidentialité</a>
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Créer mon compte
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-2">Déjà un compte ?</p>
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Types de comptes -->
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-user fa-2x text-primary mb-3"></i>
                                <h6 class="fw-bold">Joueur</h6>
                                <small class="text-muted">Créez votre profil et trouvez des opportunités</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-whistle fa-2x text-success mb-3"></i>
                                <h6 class="fw-bold">Entraîneur</h6>
                                <small class="text-muted">Proposez vos services et évaluez les joueurs</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-building fa-2x text-info mb-3"></i>
                                <h6 class="fw-bold">Club</h6>
                                <small class="text-muted">Recrutez des talents et gérez votre équipe</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            // Remove existing strength classes
            this.classList.remove('border-danger', 'border-warning', 'border-success');
            
            if (password.length > 0) {
                if (strength < 3) {
                    this.classList.add('border-danger');
                } else if (strength < 5) {
                    this.classList.add('border-warning');
                } else {
                    this.classList.add('border-success');
                }
            }
        });

        function calculatePasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            return strength;
        }

        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    this.classList.remove('border-danger');
                    this.classList.add('border-success');
                } else {
                    this.classList.remove('border-success');
                    this.classList.add('border-danger');
                }
            } else {
                this.classList.remove('border-danger', 'border-success');
            }
        });
    </script>
</body>
</html>

