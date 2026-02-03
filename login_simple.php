<?php
session_start();

// Simulation d'utilisateurs en dur pour tester (à supprimer en production)
$demo_users = [
    'admin@korajob.com' => [
        'password' => 'admin123',
        'nom' => 'Administrateur',
        'user_type' => 'admin'
    ],
    'joueur@test.com' => [
        'password' => 'joueur123',
        'nom' => 'Ahmed Benali',
        'user_type' => 'joueur'
    ],
    'entraineur@test.com' => [
        'password' => 'entraineur123',
        'nom' => 'Mohammed Coach',
        'user_type' => 'entraineur'
    ],
    'club@test.com' => [
        'password' => 'club123',
        'nom' => 'CR Belouizdad',
        'user_type' => 'club'
    ]
];

$error_message = '';
$success_message = '';

// Si déjà connecté, rediriger
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = 'Veuillez remplir tous les champs';
    } else {
        // Vérifier les identifiants demo
        if (isset($demo_users[$email]) && $demo_users[$email]['password'] === $password) {
            $user = $demo_users[$email];
            
            $_SESSION['user_id'] = 1; // ID fictif
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['user_name'] = $user['nom'];
            $_SESSION['user_email'] = $email;
            
            $success_message = 'Connexion réussie !';
            
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
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .card-body {
            padding: 3rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card login-card border-0">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <img src="assets/images/logo.svg" alt="KoraJob" width="80" class="mb-3">
                            <h2 class="fw-bold text-primary">Connexion</h2>
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
                                <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required autofocus>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Mot de passe
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-3">Pas encore de compte ?</p>
                            <a href="register_simple.php" class="btn btn-outline-primary btn-lg w-100">
                                <i class="fas fa-user-plus me-2"></i>Créer un compte
                            </a>
                        </div>

                        <!-- Comptes de test -->
                        <div class="alert alert-info mt-4">
                            <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Comptes de test</h6>
                            <div class="row">
                                <div class="col-6">
                                    <small><strong>Admin:</strong><br>admin@korajob.com<br>admin123</small>
                                </div>
                                <div class="col-6">
                                    <small><strong>Joueur:</strong><br>joueur@test.com<br>joueur123</small>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small><strong>Entraîneur:</strong><br>entraineur@test.com<br>entraineur123</small>
                                </div>
                                <div class="col-6">
                                    <small><strong>Club:</strong><br>club@test.com<br>club123</small>
                                </div>
                            </div>
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

        // Auto-fill demo credentials
        document.querySelectorAll('.alert-info small').forEach(element => {
            element.addEventListener('click', function() {
                const lines = this.textContent.split('\n');
                const email = lines[1];
                const password = lines[2];
                document.getElementById('email').value = email;
 Cursor error: input elements only contain values, not text content. Let me fix this:
document.getElementById('password').value = password;
            });
        });
    </script>
</body>
</html>
