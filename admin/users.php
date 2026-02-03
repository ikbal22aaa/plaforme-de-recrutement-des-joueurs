<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier l'authentification et le type d'utilisateur
requireAuth('admin');

// Paramètres de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;

// Filtres
$status_filter = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$type_filter = isset($_GET['type']) ? cleanInput($_GET['type']) : '';
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

// Construction de la requête SQL
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "u.status = ?";
    $params[] = $status_filter;
}

if (!empty($type_filter)) {
    $where_conditions[] = "u.user_type = ?";
    $params[] = $type_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(u.nom LIKE ? OR u.email LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Requête pour récupérer les utilisateurs
$sql = "
    SELECT u.*, 
           CASE 
               WHEN u.user_type = 'joueur' THEN j.position
               WHEN u.user_type = 'entraineur' THEN e.specialite
               WHEN u.user_type = 'club' THEN c.nom_club
               ELSE ''
           END as detail
    FROM users u
    LEFT JOIN joueurs j ON u.id = j.user_id
    LEFT JOIN entraineurs e ON u.id = e.user_id
    LEFT JOIN clubs c ON u.id = c.user_id
    $where_clause
    ORDER BY u.created_at DESC
";

$result = paginate($pdo, $sql, $params, $page, $per_page);
$users = $result['data'];
$total_pages = $result['total_pages'];

// Actions sur les utilisateurs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $user_id = (int)$_POST['user_id'];
    
    try {
        switch ($action) {
            case 'activate':
                $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                $stmt->execute([$user_id]);
                $success_message = 'Utilisateur activé avec succès';
                break;
                
            case 'deactivate':
                $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
                $stmt->execute([$user_id]);
                $success_message = 'Utilisateur désactivé avec succès';
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $success_message = 'Utilisateur supprimé avec succès';
                break;
        }
        
        // Recharger la page pour voir les changements
        header("Location: users.php?" . http_build_query($_GET));
        exit();
        
    } catch (PDOException $e) {
        $error_message = 'Erreur lors de l\'opération';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - KoraJob Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../index.php">
                <i class="fas fa-futbol me-2"></i>KoraJob Admin
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
                        <a class="nav-link active" href="users.php">Utilisateurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="joueurs.php">Joueurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="entraineurs.php">Entraîneurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clubs.php">Clubs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="annonces.php">Annonces</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">Messages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notifications.php">Notifications</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
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
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gestion des Utilisateurs</h2>
                <p class="text-muted mb-0">Gérez tous les utilisateurs de la plateforme</p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-2"></i>Ajouter un utilisateur
                </button>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nom ou email...">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous</option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Actif</option>
                            <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">Tous</option>
                            <option value="joueur" <?php echo $type_filter === 'joueur' ? 'selected' : ''; ?>>Joueur</option>
                            <option value="entraineur" <?php echo $type_filter === 'entraineur' ? 'selected' : ''; ?>>Entraîneur</option>
                            <option value="club" <?php echo $type_filter === 'club' ? 'selected' : ''; ?>>Club</option>
                            <option value="admin" <?php echo $type_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="users.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des utilisateurs -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>Liste des utilisateurs
                </h5>
                <span class="badge bg-primary"><?php echo count($users); ?> utilisateur(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Détail</th>
                                <th>Statut</th>
                                <th>Inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted">Aucun utilisateur trouvé</h6>
                                        <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($user['nom']); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php
                                            $type_colors = [
                                                'joueur' => 'primary',
                                                'entraineur' => 'success',
                                                'club' => 'info',
                                                'admin' => 'danger'
                                            ];
                                            $color = $type_colors[$user['user_type']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo ucfirst($user['user_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['detail'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            $status_colors = [
                                                'active' => 'success',
                                                'inactive' => 'danger',
                                                'pending' => 'warning'
                                            ];
                                            $status_color = $status_colors[$user['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $status_color; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="viewUser(<?php echo $user['id']; ?>)" 
                                                        title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-warning" 
                                                        onclick="editUser(<?php echo $user['id']; ?>)" 
                                                        title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($user['status'] === 'active'): ?>
                                                    <button class="btn btn-outline-secondary" 
                                                            onclick="toggleStatus(<?php echo $user['id']; ?>, 'inactive')" 
                                                            title="Désactiver">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                <?php elseif ($user['status'] === 'inactive'): ?>
                                                    <button class="btn btn-outline-success" 
                                                            onclick="toggleStatus(<?php echo $user['id']; ?>, 'active')" 
                                                            title="Activer">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                <?php elseif ($user['status'] === 'pending'): ?>
                                                    <button class="btn btn-outline-success" 
                                                            onclick="toggleStatus(<?php echo $user['id']; ?>, 'active')" 
                                                            title="Valider">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($user['user_type'] !== 'admin'): ?>
                                                    <button class="btn btn-outline-danger" 
                                                            onclick="deleteUser(<?php echo $user['id']; ?>)" 
                                                            title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <nav aria-label="Pagination des utilisateurs">
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

    <!-- Modales -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="add_user.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom complet</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_type" class="form-label">Type d'utilisateur</label>
                            <select class="form-select" id="user_type" name="user_type" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="joueur">Joueur</option>
                                <option value="entraineur">Entraîneur</option>
                                <option value="club">Club</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                                <option value="pending">En attente</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function viewUser(userId) {
            window.location.href = `view_user.php?id=${userId}`;
        }

        function editUser(userId) {
            window.location.href = `edit_user.php?id=${userId}`;
        }

        function toggleStatus(userId, newStatus) {
            const action = newStatus === 'active' ? 'activer' : 'désactiver';
            if (confirm(`Êtes-vous sûr de vouloir ${action} cet utilisateur ?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="${newStatus === 'active' ? 'activate' : 'deactivate'}">
                    <input type="hidden" name="user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteUser(userId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>

