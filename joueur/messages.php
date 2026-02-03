<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier l'authentification et le type d'utilisateur
requireAuth('joueur');

$user_id = $_SESSION['user_id'];
$joueur = getJoueurProfile($pdo, $user_id);

// Traitement de l'envoi de message
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $subject = cleanInput($_POST['subject']);
    $message = cleanInput($_POST['message']);
    
    if (!empty($subject) && !empty($message)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $receiver_id, $subject, $message]);
            $success_message = 'Message envoyé avec succès !';
        } catch (PDOException $e) {
            $error_message = 'Erreur lors de l\'envoi du message';
        }
    }
}

// Marquer un message comme lu
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $message_id = (int)$_GET['mark_read'];
    try {
        $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE id = ? AND receiver_id = ?");
        $stmt->execute([$message_id, $user_id]);
    } catch (PDOException $e) {
        // Erreur silencieuse
    }
}

// Récupérer les messages reçus
try {
    $stmt = $pdo->prepare("
        SELECT m.*, u.nom as sender_name, u.user_type as sender_type
        FROM messages m
        LEFT JOIN users u ON m.sender_id = u.id
        WHERE m.receiver_id = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $messages_recus = $stmt->fetchAll();
    
    // Récupérer les messages envoyés
    $stmt = $pdo->prepare("
        SELECT m.*, u.nom as receiver_name, u.user_type as receiver_type
        FROM messages m
        LEFT JOIN users u ON m.receiver_id = u.id
        WHERE m.sender_id = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $messages_envoyes = $stmt->fetchAll();
    
    // Statistiques
    $stats = [
        'recus' => count($messages_recus),
        'envoyes' => count($messages_envoyes),
        'non_lus' => 0
    ];
    
    foreach ($messages_recus as $message) {
        if (!$message['is_read']) {
            $stats['non_lus']++;
        }
    }
    
} catch (PDOException $e) {
    $messages_recus = [];
    $messages_envoyes = [];
    $stats = ['recus' => 0, 'envoyes' => 0, 'non_lus' => 0];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - KoraJob</title>
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
                        <a class="nav-link active" href="messages.php">Messages</a>
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
                                <a class="nav-link active" href="messages.php">
                                    <i class="fas fa-envelope me-2"></i>Messages
                                    <?php if ($stats['non_lus'] > 0): ?>
                                        <span class="badge bg-danger ms-2"><?php echo $stats['non_lus']; ?></span>
                                    <?php endif; ?>
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
                        <h2 class="fw-bold mb-1">Messages</h2>
                        <p class="text-muted mb-0">Gérez vos communications avec les clubs et entraîneurs</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                            <i class="fas fa-plus me-2"></i>Nouveau message
                        </button>
                    </div>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $stats['recus']; ?></h3>
                                <small>Messages reçus</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $stats['envoyes']; ?></h3>
                                <small>Messages envoyés</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $stats['non_lus']; ?></h3>
                                <small>Non lus</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglets -->
                <ul class="nav nav-tabs mb-4" id="messagesTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="recus-tab" data-bs-toggle="tab" data-bs-target="#recus" type="button" role="tab">
                            <i class="fas fa-inbox me-2"></i>Messages reçus
                            <?php if ($stats['non_lus'] > 0): ?>
                                <span class="badge bg-danger ms-2"><?php echo $stats['non_lus']; ?></span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="envoyes-tab" data-bs-toggle="tab" data-bs-target="#envoyes" type="button" role="tab">
                            <i class="fas fa-paper-plane me-2"></i>Messages envoyés
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="messagesTabContent">
                    <!-- Messages reçus -->
                    <div class="tab-pane fade show active" id="recus" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <?php if (empty($messages_recus)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Aucun message reçu</h5>
                                        <p class="text-muted">Vous recevrez ici les messages des clubs et entraîneurs</p>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group">
                                        <?php foreach ($messages_recus as $message): ?>
                                            <div class="list-group-item <?php echo !$message['is_read'] ? 'list-group-item-light' : ''; ?>">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <div class="me-3">
                                                                <?php if ($message['sender_type'] === 'club'): ?>
                                                                    <i class="fas fa-building text-primary fa-lg"></i>
                                                                <?php elseif ($message['sender_type'] === 'entraineur'): ?>
                                                                    <i class="fas fa-whistle text-success fa-lg"></i>
                                                                <?php else: ?>
                                                                    <i class="fas fa-user text-info fa-lg"></i>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1">
                                                                    <?php echo htmlspecialchars($message['sender_name'] ?? 'Anonyme'); ?>
                                                                    <?php if (!$message['is_read']): ?>
                                                                        <span class="badge bg-primary ms-2">Nouveau</span>
                                                                    <?php endif; ?>
                                                                </h6>
                                                                <p class="mb-1 fw-bold"><?php echo htmlspecialchars($message['subject']); ?></p>
                                                                <p class="mb-1"><?php echo htmlspecialchars(substr($message['message'], 0, 150)); ?><?php echo strlen($message['message']) > 150 ? '...' : ''; ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="text-muted"><?php echo formatDate($message['created_at']); ?></small>
                                                        <div class="mt-2">
                                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#messageModal<?php echo $message['id']; ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <?php if (!$message['is_read']): ?>
                                                                <a href="?mark_read=<?php echo $message['id']; ?>" class="btn btn-sm btn-outline-success">
                                                                    <i class="fas fa-check"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Messages envoyés -->
                    <div class="tab-pane fade" id="envoyes" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <?php if (empty($messages_envoyes)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-paper-plane fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Aucun message envoyé</h5>
                                        <p class="text-muted">Commencez une conversation avec un club ou un entraîneur</p>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                                            <i class="fas fa-plus me-2"></i>Envoyer un message
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group">
                                        <?php foreach ($messages_envoyes as $message): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <div class="me-3">
                                                                <i class="fas fa-paper-plane text-primary fa-lg"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1">À: <?php echo htmlspecialchars($message['receiver_name'] ?? 'Anonyme'); ?></h6>
                                                                <p class="mb-1 fw-bold"><?php echo htmlspecialchars($message['subject']); ?></p>
                                                                <p class="mb-1"><?php echo htmlspecialchars(substr($message['message'], 0, 150)); ?><?php echo strlen($message['message']) > 150 ? '...' : ''; ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="text-muted"><?php echo formatDate($message['created_at']); ?></small>
                                                        <div class="mt-2">
                                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#messageModal<?php echo $message['id']; ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal nouveau message -->
    <div class="modal fade" id="newMessageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope me-2"></i>Nouveau message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="send_message" value="1">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="receiver_id" class="form-label">Destinataire</label>
                            <select class="form-select" id="receiver_id" name="receiver_id" required>
                                <option value="">Sélectionnez un destinataire</option>
                                <optgroup label="Clubs">
                                    <option value="61">CR Belouizdad</option>
                                    <option value="62">MC Alger</option>
                                    <option value="63">JS Kabylie</option>
                                    <option value="64">ES Sétif</option>
                                    <option value="65">USM Alger</option>
                                </optgroup>
                                <optgroup label="Entraîneurs">
                                    <option value="56">Djamel Belmadi</option>
                                    <option value="57">Rabah Madjer</option>
                                    <option value="58">Lakhdar Belloumi</option>
                                    <option value="59">Ali Fergani</option>
                                    <option value="60">Abdelhafid Tasfaout</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Sujet</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modales pour voir les détails des messages -->
    <?php foreach (array_merge($messages_recus, $messages_envoyes) as $message): ?>
        <div class="modal fade" id="messageModal<?php echo $message['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($message['subject']); ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>De:</strong> <?php echo htmlspecialchars($message['sender_name'] ?? 'Anonyme'); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>À:</strong> <?php echo htmlspecialchars($message['receiver_name'] ?? 'Anonyme'); ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>Date:</strong> <?php echo formatDate($message['created_at']); ?>
                        </div>
                        <hr>
                        <div>
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <?php if (isset($message['sender_id']) && $message['sender_id'] != $user_id): ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#replyModal<?php echo $message['id']; ?>">
                                <i class="fas fa-reply me-2"></i>Répondre
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>

