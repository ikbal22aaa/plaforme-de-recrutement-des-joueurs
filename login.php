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

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = 'Veuillez remplir tous les champs';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['user_name'] = $user['nom'];
                $_SESSION['user_email'] = $user['email'];
                
                // Redirection selon le type d'utilisateur
                switch ($user['user_type']) {
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
            } else {
                $error_message = 'Email ou mot de passe incorrect';
            }
        } catch (PDOException $e) {
            $error_message = 'Erreur de connexion à la base de données';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - KoraJob</title>
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
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-sign-in-alt fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold">Connexion</h2>
                            <p class="text-muted">Accédez à votre compte KoraJob</p>
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
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Mot de passe
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-2">Pas encore de compte ?</p>
                            <a href="register.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i>Créer un compte
                            </a>
                        </div>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <a href="#" class="text-decoration-none">Mot de passe oublié ?</a>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Informations de connexion pour les tests -->
                <div class="card mt-4 border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Comptes de test</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Admin:</strong> admin@korajob.com / admin123<br>
                            <strong>Joueur:</strong> ahmed.benali@test.com / password123<br>
                            <strong>Entraîneur:</strong> djamel.belmadi@test.com / password123<br>
                            <strong>Club:</strong> crb@test.com / password123
                        </small>
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
    </script>
</body>
</html>

