<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Paramètres de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;

// Filtres de recherche
$filters = [
    'position' => isset($_GET['position']) ? cleanInput($_GET['position']) : '',
    'wilaya' => isset($_GET['wilaya']) ? cleanInput($_GET['wilaya']) : '',
    'age_min' => isset($_GET['age_min']) ? (int)$_GET['age_min'] : '',
    'age_max' => isset($_GET['age_max']) ? (int)$_GET['age_max'] : '',
    'niveau' => isset($_GET['niveau']) ? cleanInput($_GET['niveau']) : '',
    'search' => isset($_GET['search']) ? cleanInput($_GET['search']) : ''
];

// Construction de la requête SQL simplifiée
$where_conditions = ["u.user_type = 'joueur'", "u.status = 'active'"];
$params = [];

if (!empty($filters['position'])) {
    $where_conditions[] = "j.position = ?";
    $params[] = $filters['position'];
}

if (!empty($filters['wilaya'])) {
    $where_conditions[] = "j.wilaya = ?";
    $params[] = $filters['wilaya'];
}

if (!empty($filters['age_min'])) {
    $where_conditions[] = "j.age >= ?";
    $params[] = $filters['age_min'];
}

if (!empty($filters['age_max'])) {
    $where_conditions[] = "j.age <= ?";
    $params[] = $filters['age_max'];
}

if (!empty($filters['niveau'])) {
    $where_conditions[] = "j.niveau = ?";
    $params[] = $filters['niveau'];
}

if (!empty($filters['search'])) {
    $where_conditions[] = "(u.nom LIKE ? OR j.description LIKE ?)";
    $search_term = '%' . $filters['search'] . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = implode(' AND ', $where_conditions);

// Requête simplifiée pour récupérer les joueurs
$sql = "
    SELECT u.id as user_id, u.nom, u.email, u.created_at,
           j.age, j.position, j.wilaya, j.niveau, j.pied_fort, j.description, j.video_url
    FROM users u 
    LEFT JOIN joueurs j ON u.id = j.user_id 
    WHERE $where_clause
    ORDER BY u.created_at DESC
";

// Récupérer les données directement sans pagination complexe
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $joueurs = $stmt->fetchAll();
    
    // Pagination manuelle
    $total = count($joueurs);
    $total_pages = ceil($total / $per_page);
    $offset = ($page - 1) * $per_page;
    $joueurs = array_slice($joueurs, $offset, $per_page);
    
} catch (PDOException $e) {
    $joueurs = [];
    $total_pages = 0;
    echo "<!-- Erreur SQL: " . $e->getMessage() . " -->";
}

// Obtenir les listes pour les filtres
$wilayas = getWilayas();
$positions = getPositions();
$niveaux = getNiveaux();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joueurs - KoraJob</title>
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
                        <a class="nav-link active" href="joueurs.php">Joueurs</a>
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

    <!-- Header -->
    <div class="bg-primary text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-3">
                        <i class="fas fa-users me-3"></i>Joueurs
                    </h1>
                    <p class="lead">
                        Découvrez les talents du football algérien. Filtrez par position, région, âge et niveau pour trouver le joueur parfait.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <!-- Filtres -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Filtres de recherche
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($filters['search']); ?>" 
                                       placeholder="Nom du joueur...">
                            </div>
                            <div class="col-md-2">
                                <label for="position" class="form-label">Position</label>
                                <select class="form-select" id="position" name="position">
                                    <option value="">Toutes</option>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?php echo $position; ?>" 
                                                <?php echo $filters['position'] === $position ? 'selected' : ''; ?>>
                                            <?php echo $position; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="wilaya" class="form-label">Wilaya</label>
                                <select class="form-select" id="wilaya" name="wilaya">
                                    <option value="">Toutes</option>
                                    <?php foreach ($wilayas as $wilaya): ?>
                                        <option value="<?php echo $wilaya; ?>" 
                                                <?php echo $filters['wilaya'] === $wilaya ? 'selected' : ''; ?>>
                                            <?php echo $wilaya; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="niveau" class="form-label">Niveau</label>
                                <select class="form-select" id="niveau" name="niveau">
                                    <option value="">Tous</option>
                                    <?php foreach ($niveaux as $niveau): ?>
                                        <option value="<?php echo $niveau; ?>" 
                                                <?php echo $filters['niveau'] === $niveau ? 'selected' : ''; ?>>
                                            <?php echo $niveau; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label for="age_min" class="form-label">Âge min</label>
                                <input type="number" class="form-control" id="age_min" name="age_min" 
                                       value="<?php echo $filters['age_min']; ?>" min="16" max="40">
                            </div>
                            <div class="col-md-1">
                                <label for="age_max" class="form-label">Âge max</label>
                                <input type="number" class="form-control" id="age_max" name="age_max" 
                                       value="<?php echo $filters['age_max']; ?>" min="16" max="40">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résultats -->
        <div class="row">
            <div class="col-12 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <?php echo count($joueurs); ?> joueur<?php echo count($joueurs) > 1 ? 's' : ''; ?> trouvé<?php echo count($joueurs) > 1 ? 's' : ''; ?>
                    </h4>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary active" data-view="grid">
                            <i class="fas fa-th"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grille des joueurs -->
        <div class="row" id="joueurs-grid">
            <?php if (empty($joueurs)): ?>
                <div class="col-12">
                    <div class="card text-center py-5">
                        <div class="card-body">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun joueur trouvé</h5>
                            <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($joueurs as $joueur): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm joueur-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar me-3">
                                        <img src="assets/images/avatar-joueur.svg" alt="Avatar" 
                                             class="rounded-circle" width="60" height="60">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($joueur['nom']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($joueur['wilaya'] ?? 'Non spécifié'); ?>
                                        </small>
                                    </div>
                                    <div class="rating">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-muted"></i>
                                        <small class="text-muted ms-1">(4.0)</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Position</small>
                                        <div class="fw-bold"><?php echo htmlspecialchars($joueur['position'] ?? 'Non spécifié'); ?></div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Âge</small>
                                        <div class="fw-bold"><?php echo $joueur['age'] ?? 'Non spécifié'; ?> ans</div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Niveau</small>
                                        <div class="fw-bold"><?php echo htmlspecialchars($joueur['niveau'] ?? 'Non spécifié'); ?></div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Pied fort</small>
                                        <div class="fw-bold"><?php echo htmlspecialchars($joueur['pied_fort'] ?? 'Non spécifié'); ?></div>
                                    </div>
                                </div>

                                <?php if (!empty($joueur['description'])): ?>
                                    <p class="card-text text-muted small mb-3">
                                        <?php echo htmlspecialchars(substr($joueur['description'], 0, 100)); ?>
                                        <?php if (strlen($joueur['description']) > 100): ?>...<?php endif; ?>
                                    </p>
                                <?php endif; ?>

                                <div class="d-flex gap-2">
                                    <a href="joueur-profile.php?id=<?php echo $joueur['user_id']; ?>" class="btn btn-outline-primary btn-sm flex-grow-1">
                                        <i class="fas fa-eye me-1"></i>Voir profil
                                    </a>
                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] !== 'joueur'): ?>
                                        <a href="contact.php?player_id=<?php echo $joueur['user_id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-envelope me-1"></i>Contacter
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="row">
                <div class="col-12">
                    <nav aria-label="Pagination des joueurs">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                        Précédent
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                        Suivant
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
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
        // Gestion du changement de vue
        document.querySelectorAll('[data-view]').forEach(btn => {
            btn.addEventListener('click', function() {
                const view = this.dataset.view;
                
                // Mettre à jour les boutons actifs
                document.querySelectorAll('[data-view]').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Changer la vue
                const grid = document.getElementById('joueurs-grid');
                if (view === 'list') {
                    grid.className = 'row';
                    document.querySelectorAll('.joueur-card').forEach(card => {
                        card.classList.add('col-12');
                    });
                } else {
                    grid.className = 'row';
                    document.querySelectorAll('.joueur-card').forEach(card => {
                        card.classList.remove('col-12');
                    });
                }
            });
        });
    </script>
</body>
</html>
