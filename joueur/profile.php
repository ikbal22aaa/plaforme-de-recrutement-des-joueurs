<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier l'authentification et le type d'utilisateur
requireAuth('joueur');

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Récupérer les données du joueur
$joueur = getJoueurProfile($pdo, $user_id);

// Traitement du formulaire de mise à jour
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = cleanInput($_POST['nom']);
    $age = (int)$_POST['age'];
    $position = cleanInput($_POST['position']);
    $wilaya = cleanInput($_POST['wilaya']);
    $niveau = cleanInput($_POST['niveau']);
    $taille = (float)$_POST['taille'];
    $poids = (float)$_POST['poids'];
    $pied_fort = cleanInput($_POST['pied_fort']);
    $description = cleanInput($_POST['description']);
    $video_url = cleanInput($_POST['video_url']);
    
    try {
        // Mettre à jour les informations utilisateur
        $stmt = $pdo->prepare("UPDATE users SET nom = ? WHERE id = ?");
        $stmt->execute([$nom, $user_id]);
        
        // Mettre à jour le profil joueur
        $stmt = $pdo->prepare("
            UPDATE joueurs SET 
                age = ?, position = ?, wilaya = ?, niveau = ?, 
                taille = ?, poids = ?, pied_fort = ?, 
                description = ?, video_url = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $age, $position, $wilaya, $niveau, 
            $taille, $poids, $pied_fort, 
            $description, $video_url, $user_id
        ]);
        
        $success_message = 'Profil mis à jour avec succès !';
        
        // Recharger les données
        $joueur = getJoueurProfile($pdo, $user_id);
        
    } catch (PDOException $e) {
        $error_message = 'Erreur lors de la mise à jour du profil';
    }
}

// Récupérer les listes pour les formulaires
$wilayas = getWilayas();
$positions = getPositions();
$niveaux = getNiveaux();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - KoraJob</title>
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
                        <a class="nav-link active" href="profile.php">Mon Profil</a>
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
                                <a class="nav-link active" href="profile.php">
                                    <i class="fas fa-user me-2"></i>Mon Profil
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="videos.php">
                                    <i class="fas fa-video me-2"></i>Mes Vidéos
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
                                <a class="nav-link" href="evaluations.php">
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
                        <h2 class="fw-bold mb-1">Mon Profil</h2>
                        <p class="text-muted mb-0">Gérez vos informations personnelles et votre profil football</p>
                    </div>
                    <div>
                        <a href="videos.php" class="btn btn-outline-success me-2">
                            <i class="fas fa-video me-2"></i>Gérer mes vidéos
                        </a>
                        <a href="../joueur-profile.php?id=<?php echo $user_id; ?>" class="btn btn-outline-primary" target="_blank">
                            <i class="fas fa-eye me-2"></i>Voir mon profil public
                        </a>
                    </div>
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

                <!-- Formulaire de profil -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Informations personnelles
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" 
                                           value="<?php echo htmlspecialchars($joueur['nom'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="age" class="form-label">Âge *</label>
                                    <input type="number" class="form-control" id="age" name="age" 
                                           value="<?php echo $joueur['age'] ?? ''; ?>" min="16" max="40" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="position" class="form-label">Position *</label>
                                    <select class="form-select" id="position" name="position" required>
                                        <option value="">Sélectionnez votre position</option>
                                        <?php foreach ($positions as $position): ?>
                                            <option value="<?php echo $position; ?>" 
                                                    <?php echo ($joueur['position'] ?? '') === $position ? 'selected' : ''; ?>>
                                                <?php echo $position; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="wilaya" class="form-label">Wilaya *</label>
                                    <select class="form-select" id="wilaya" name="wilaya" required>
                                        <option value="">Sélectionnez votre wilaya</option>
                                        <?php foreach ($wilayas as $wilaya): ?>
                                            <option value="<?php echo $wilaya; ?>" 
                                                    <?php echo ($joueur['wilaya'] ?? '') === $wilaya ? 'selected' : ''; ?>>
                                                <?php echo $wilaya; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="niveau" class="form-label">Niveau *</label>
                                    <select class="form-select" id="niveau" name="niveau" required>
                                        <option value="">Sélectionnez votre niveau</option>
                                        <?php foreach ($niveaux as $niveau): ?>
                                            <option value="<?php echo $niveau; ?>" 
                                                    <?php echo ($joueur['niveau'] ?? '') === $niveau ? 'selected' : ''; ?>>
                                                <?php echo $niveau; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="pied_fort" class="form-label">Pied fort</label>
                                    <select class="form-select" id="pied_fort" name="pied_fort">
                                        <option value="">Sélectionnez</option>
                                        <option value="droit" <?php echo ($joueur['pied_fort'] ?? '') === 'droit' ? 'selected' : ''; ?>>Droit</option>
                                        <option value="gauche" <?php echo ($joueur['pied_fort'] ?? '') === 'gauche' ? 'selected' : ''; ?>>Gauche</option>
                                        <option value="ambidextre" <?php echo ($joueur['pied_fort'] ?? '') === 'ambidextre' ? 'selected' : ''; ?>>Ambidextre</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="taille" class="form-label">Taille (cm)</label>
                                    <input type="number" class="form-control" id="taille" name="taille" 
                                           value="<?php echo $joueur['taille'] ?? ''; ?>" min="150" max="220" step="0.1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="poids" class="form-label">Poids (kg)</label>
                                    <input type="number" class="form-control" id="poids" name="poids" 
                                           value="<?php echo $joueur['poids'] ?? ''; ?>" min="40" max="120" step="0.1">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description / Présentation</label>
                                <textarea class="form-control" id="description" name="description" rows="4" 
                                          placeholder="Décrivez votre parcours, vos objectifs, vos qualités..."><?php echo htmlspecialchars($joueur['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="video_url" class="form-label">URL de votre vidéo de présentation</label>
                                <input type="url" class="form-control" id="video_url" name="video_url" 
                                       value="<?php echo htmlspecialchars($joueur['video_url'] ?? ''); ?>" 
                                       placeholder="https://example.com/video.mp4">
                                <div class="form-text">Ajoutez un lien vers votre vidéo de présentation (optionnel)</div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Retour au Dashboard
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Statistiques du profil -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-eye fa-2x text-primary mb-2"></i>
                                <h5>Visibilité</h5>
                                <p class="text-muted">Votre profil est visible par les clubs et entraîneurs</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-star fa-2x text-warning mb-2"></i>
                                <h5>Évaluations</h5>
                                <p class="text-muted">Recevez des évaluations de vos performances</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-bullhorn fa-2x text-success mb-2"></i>
                                <h5>Opportunités</h5>
                                <p class="text-muted">Soyez contacté par des clubs intéressés</p>
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
