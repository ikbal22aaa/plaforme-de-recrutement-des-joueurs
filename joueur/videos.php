<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/video_upload.php';

// Vérifier l'authentification et le type d'utilisateur
requireAuth('joueur');

$user_id = $_SESSION['user_id'];
$joueur = getJoueurProfile($pdo, $user_id);
$error_message = '';
$success_message = '';

// Traitement de l'upload de vidéo
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_video'])) {
        $title = cleanInput($_POST['title']);
        $description = cleanInput($_POST['description']);
        $video_type = cleanInput($_POST['video_type']);
        $is_public = isset($_POST['is_public']) ? 1 : 0;
        $video_url = cleanInput($_POST['video_url']);
        $video_file = '';
        
        // Gestion de l'upload de fichier
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_result = uploadVideoFile($_FILES['video_file'], '../uploads/videos/');
            if ($upload_result['success']) {
                $video_file = $upload_result['filename'];
            } else {
                $error_message = implode(', ', $upload_result['errors']);
            }
        }
        
        // Vérifier qu'on a soit une URL soit un fichier
        if (empty($video_url) && empty($video_file)) {
            $error_message = 'Veuillez fournir soit une URL vidéo soit uploader un fichier';
        }
        
        if (!empty($title) && empty($error_message)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO joueur_videos (joueur_id, title, description, video_url, video_file, video_type, is_public)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$joueur['id'], $title, $description, $video_url, $video_file, $video_type, $is_public]);
                $success_message = 'Vidéo ajoutée avec succès !';
            } catch (PDOException $e) {
                $error_message = 'Erreur lors de l\'ajout de la vidéo';
                // Supprimer le fichier uploadé en cas d'erreur
                if (!empty($video_file)) {
                    deleteVideoFile($video_file, '../uploads/videos/');
                }
            }
        } elseif (empty($title)) {
            $error_message = 'Le titre est obligatoire';
        }
    }
    
    // Supprimer une vidéo
    if (isset($_POST['delete_video'])) {
        $video_id = (int)$_POST['video_id'];
        try {
            // Récupérer le nom du fichier avant suppression
            $stmt = $pdo->prepare("SELECT video_file FROM joueur_videos WHERE id = ? AND joueur_id = ?");
            $stmt->execute([$video_id, $joueur['id']]);
            $video_data = $stmt->fetch();
            
            // Supprimer de la base de données
            $stmt = $pdo->prepare("DELETE FROM joueur_videos WHERE id = ? AND joueur_id = ?");
            $stmt->execute([$video_id, $joueur['id']]);
            
            // Supprimer le fichier s'il existe
            if ($video_data && !empty($video_data['video_file'])) {
                deleteVideoFile($video_data['video_file'], '../uploads/videos/');
            }
            
            $success_message = 'Vidéo supprimée avec succès !';
        } catch (PDOException $e) {
            $error_message = 'Erreur lors de la suppression de la vidéo';
        }
    }
}

// Récupérer les vidéos du joueur
try {
    $stmt = $pdo->prepare("
        SELECT * FROM joueur_videos 
        WHERE joueur_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$joueur['id']]);
    $videos = $stmt->fetchAll();
    
    // Statistiques
    $stats = [
        'total' => count($videos),
        'public' => 0,
        'private' => 0,
        'skills' => 0,
        'match' => 0,
        'training' => 0
    ];
    
    foreach ($videos as $video) {
        if ($video['is_public']) {
            $stats['public']++;
        } else {
            $stats['private']++;
        }
        $stats[$video['video_type']]++;
    }
    
} catch (PDOException $e) {
    $videos = [];
    $stats = ['total' => 0, 'public' => 0, 'private' => 0, 'skills' => 0, 'match' => 0, 'training' => 0];
}

// Types de vidéos disponibles
$video_types = [
    'skills' => 'Compétences techniques',
    'match' => 'Match/Performance',
    'training' => 'Entraînement',
    'interview' => 'Interview',
    'other' => 'Autre'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Vidéos - KoraJob</title>
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
                        <a class="nav-link" href="profile.php">Mon Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="candidatures.php">Mes Candidatures</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">Messages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="evaluations.php">Évaluations</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                            <li><a class="dropdown-item" href="videos.php">Mes Vidéos</a></li>
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
                                <a class="nav-link" href="profile.php">
                                    <i class="fas fa-user me-2"></i>Mon Profil
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
                            <li class="nav-item">
                                <a class="nav-link active" href="videos.php">
                                    <i class="fas fa-video me-2"></i>Mes Vidéos
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
                        <h2 class="fw-bold mb-1">Mes Vidéos</h2>
                        <p class="text-muted mb-0">Gérez vos vidéos de présentation pour attirer l'attention des entraîneurs</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVideoModal">
                            <i class="fas fa-plus me-2"></i>Ajouter une vidéo
                        </button>
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

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $stats['total']; ?></h3>
                                <small>Total vidéos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $stats['public']; ?></h3>
                                <small>Publiques</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $stats['private']; ?></h3>
                                <small>Privées</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $stats['skills']; ?></h3>
                                <small>Compétences</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des vidéos -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-video me-2"></i>Vos vidéos
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($videos)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-video fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucune vidéo ajoutée</h5>
                                <p class="text-muted">Ajoutez vos premières vidéos pour montrer vos compétences aux entraîneurs</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVideoModal">
                                    <i class="fas fa-plus me-2"></i>Ajouter ma première vidéo
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($videos as $video): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100">
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <?php if ($video['video_url']): ?>
                                                    <i class="fas fa-play-circle fa-3x text-primary"></i>
                                                <?php elseif ($video['video_file']): ?>
                                                    <i class="fas fa-file-video fa-3x text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-video fa-3x text-muted"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0"><?php echo htmlspecialchars($video['title']); ?></h6>
                                                    <span class="badge bg-<?php echo $video['is_public'] ? 'success' : 'warning'; ?>">
                                                        <?php echo $video['is_public'] ? 'Public' : 'Privé'; ?>
                                                    </span>
                                                </div>
                                                <p class="card-text text-muted small flex-grow-1">
                                                    <?php echo htmlspecialchars(substr($video['description'], 0, 100)); ?>
                                                    <?php echo strlen($video['description']) > 100 ? '...' : ''; ?>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-tag me-1"></i>
                                                        <?php echo $video_types[$video['video_type']]; ?>
                                                        <?php if ($video['video_file']): ?>
                                                            <br><i class="fas fa-hdd me-1"></i>
                                                            <?php 
                                                            $filepath = '../uploads/videos/' . $video['video_file'];
                                                            if (file_exists($filepath)) {
                                                                echo formatFileSize(filesize($filepath));
                                                            }
                                                            ?>
                                                        <?php endif; ?>
                                                    </small>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($video['video_url']): ?>
                                                            <a href="<?php echo htmlspecialchars($video['video_url']); ?>" 
                                                               target="_blank" class="btn btn-outline-primary" title="Ouvrir l'URL">
                                                                <i class="fas fa-external-link-alt"></i>
                                                            </a>
                                                        <?php elseif ($video['video_file']): ?>
                                                            <a href="../uploads/videos/<?php echo htmlspecialchars($video['video_file']); ?>" 
                                                               target="_blank" class="btn btn-outline-success" title="Télécharger la vidéo">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="deleteVideo(<?php echo $video['id']; ?>, '<?php echo htmlspecialchars($video['title']); ?>')" title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Conseils -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>Conseils pour vos vidéos
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-futbol me-2 text-primary"></i>Vidéos de compétences</h6>
                                <p class="text-muted">Montrez vos meilleures techniques : dribbles, passes, tirs, contrôles de balle.</p>
                                
                                <h6><i class="fas fa-trophy me-2 text-success"></i>Vidéos de match</h6>
                                <p class="text-muted">Sélectionnez vos meilleures performances en match, vos buts, vos actions décisives.</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-running me-2 text-warning"></i>Vidéos d'entraînement</h6>
                                <p class="text-muted">Montrez votre sérieux et votre progression lors des séances d'entraînement.</p>
                                
                                <h6><i class="fas fa-microphone me-2 text-info"></i>Interviews</h6>
                                <p class="text-muted">Partagez votre personnalité et vos objectifs à travers des interviews.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ajouter vidéo -->
    <div class="modal fade" id="addVideoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-video me-2"></i>Ajouter une vidéo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="add_video" value="1">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label">Titre de la vidéo *</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                       placeholder="Ex: Mes meilleures compétences techniques">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="video_type" class="form-label">Type de vidéo *</label>
                                <select class="form-select" id="video_type" name="video_type" required>
                                    <?php foreach ($video_types as $key => $label): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Décrivez le contenu de votre vidéo..."></textarea>
                        </div>
                        
                        <!-- Onglets pour URL ou Upload -->
                        <ul class="nav nav-tabs mb-3" id="videoTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="url-tab" data-bs-toggle="tab" data-bs-target="#url" type="button" role="tab">
                                    <i class="fas fa-link me-2"></i>URL Vidéo
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">
                                    <i class="fas fa-upload me-2"></i>Upload Fichier
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="videoTabContent">
                            <!-- Onglet URL -->
                            <div class="tab-pane fade show active" id="url" role="tabpanel">
                                <div class="mb-3">
                                    <label for="video_url" class="form-label">URL de la vidéo</label>
                                    <input type="url" class="form-control" id="video_url" name="video_url"
                                           placeholder="https://www.youtube.com/watch?v=... ou https://vimeo.com/...">
                                    <div class="form-text">Collez le lien de votre vidéo (YouTube, Vimeo, etc.)</div>
                                </div>
                            </div>
                            
                            <!-- Onglet Upload -->
                            <div class="tab-pane fade" id="upload" role="tabpanel">
                                <div class="mb-3">
                                    <label for="video_file" class="form-label">Fichier vidéo</label>
                                    <input type="file" class="form-control" id="video_file" name="video_file" 
                                           accept="video/*">
                                    <div class="form-text">
                                        Formats acceptés: MP4, AVI, MOV, WMV, FLV, WebM, MKV (max 100MB)
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" checked>
                                <label class="form-check-label" for="is_public">
                                    Rendre cette vidéo publique
                                </label>
                                <div class="form-text">Les vidéos publiques sont visibles par tous les entraîneurs</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Ajouter la vidéo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer la vidéo <strong id="videoTitle"></strong> ?</p>
                    <p class="text-muted">Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="delete_video" value="1">
                        <input type="hidden" name="video_id" id="videoId">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function deleteVideo(videoId, title) {
            document.getElementById('videoId').value = videoId;
            document.getElementById('videoTitle').textContent = title;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Gestion de l'upload de fichier
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('video_file');
            const urlInput = document.getElementById('video_url');
            
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        // Vérifier la taille (100MB)
                        const maxSize = 100 * 1024 * 1024;
                        if (file.size > maxSize) {
                            alert('Le fichier est trop volumineux. Taille maximale: 100MB');
                            this.value = '';
                            return;
                        }
                        
                        // Vérifier le type de fichier
                        const allowedTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-ms-wmv', 'video/x-flv', 'video/webm', 'video/x-matroska'];
                        if (!allowedTypes.includes(file.type)) {
                            alert('Type de fichier non autorisé. Formats acceptés: MP4, AVI, MOV, WMV, FLV, WebM, MKV');
                            this.value = '';
                            return;
                        }
                        
                        // Vider l'URL si un fichier est sélectionné
                        if (urlInput) {
                            urlInput.value = '';
                        }
                        
                        // Afficher les informations du fichier
                        const fileInfo = document.createElement('div');
                        fileInfo.className = 'alert alert-info mt-2';
                        fileInfo.innerHTML = `
                            <i class="fas fa-file-video me-2"></i>
                            <strong>${file.name}</strong> (${formatFileSize(file.size)})
                        `;
                        
                        // Supprimer l'ancienne info si elle existe
                        const oldInfo = this.parentNode.querySelector('.alert');
                        if (oldInfo) {
                            oldInfo.remove();
                        }
                        
                        this.parentNode.appendChild(fileInfo);
                    }
                });
            }
            
            if (urlInput) {
                urlInput.addEventListener('input', function() {
                    // Vider le fichier si une URL est saisie
                    if (fileInput && this.value.trim() !== '') {
                        fileInput.value = '';
                        const fileInfo = fileInput.parentNode.querySelector('.alert');
                        if (fileInfo) {
                            fileInfo.remove();
                        }
                    }
                });
            }
        });

        function formatFileSize(bytes) {
            const units = ['B', 'KB', 'MB', 'GB'];
            let size = bytes;
            let unitIndex = 0;
            
            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }
            
            return Math.round(size * 100) / 100 + ' ' + units[unitIndex];
        }
    </script>
</body>
</html>
