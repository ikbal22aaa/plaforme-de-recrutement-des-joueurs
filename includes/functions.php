<?php
// Fonctions utilitaires pour KoraJob

// Fonction pour nettoyer les données d'entrée
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction pour valider l'email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fonction pour vérifier la force du mot de passe
function validatePassword($password) {
    // Au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
}

// Fonction pour générer un token aléatoire
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Fonction pour uploader un fichier
function uploadFile($file, $upload_dir = 'uploads/', $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Paramètres invalides'];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'message' => 'Aucun fichier envoyé'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'message' => 'Fichier trop volumineux'];
        default:
            return ['success' => false, 'message' => 'Erreur inconnue'];
    }

    if ($file['size'] > 10000000) { // 10MB max
        return ['success' => false, 'message' => 'Fichier trop volumineux (max 10MB)'];
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'Type de fichier non autorisé'];
    }

    $filename = uniqid() . '.' . $file_extension;
    $filepath = $upload_dir . $filename;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Erreur lors de l\'upload'];
    }

    return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
}

// Fonction pour obtenir les informations d'un utilisateur
function getUserInfo($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Fonction pour obtenir le profil complet d'un joueur
function getJoueurProfile($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT u.*, j.* 
        FROM users u 
        LEFT JOIN joueurs j ON u.id = j.user_id 
        WHERE u.id = ? AND u.user_type = 'joueur'
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Fonction pour obtenir le profil complet d'un entraîneur
function getEntraineurProfile($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT u.*, e.* 
        FROM users u 
        LEFT JOIN entraineurs e ON u.id = e.user_id 
        WHERE u.id = ? AND u.user_type = 'entraineur'
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Fonction pour obtenir le profil complet d'un club
function getClubProfile($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT u.*, c.* 
        FROM users u 
        LEFT JOIN clubs c ON u.id = c.user_id 
        WHERE u.id = ? AND u.user_type = 'club'
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Fonction pour envoyer une notification
function sendNotification($pdo, $user_id, $title, $message, $type = 'info') {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$user_id, $title, $message, $type]);
}

// Fonction pour obtenir les notifications non lues
function getUnreadNotifications($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = FALSE ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Fonction pour marquer une notification comme lue
function markNotificationAsRead($pdo, $notification_id) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ?");
    return $stmt->execute([$notification_id]);
}

// Fonction pour calculer la note moyenne d'un utilisateur
function calculateAverageRating($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM evaluations WHERE evaluated_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return round($result['avg_rating'], 2);
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fonction pour vérifier le type d'utilisateur
function checkUserType($required_type) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_array($required_type)) {
        return in_array($_SESSION['user_type'], $required_type);
    }
    
    return $_SESSION['user_type'] === $required_type;
}

// Fonction pour rediriger si non autorisé
function requireAuth($required_type = null) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    
    if ($required_type && !checkUserType($required_type)) {
        header('Location: unauthorized.php');
        exit();
    }
}

// Fonction pour formater la date
function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

// Fonction pour obtenir les wilayas d'Algérie
function getWilayas() {
    return [
        'Adrar', 'Chlef', 'Laghouat', 'Oum El Bouaghi', 'Batna', 'Béjaïa', 'Biskra', 'Béchar',
        'Blida', 'Bouira', 'Tamanrasset', 'Tébessa', 'Tlemcen', 'Tiaret', 'Tizi Ouzou', 'Alger',
        'Djelfa', 'Jijel', 'Sétif', 'Saïda', 'Skikda', 'Sidi Bel Abbès', 'Annaba', 'Guelma',
        'Constantine', 'Médéa', 'Mostaganem', 'M\'Sila', 'Mascara', 'Ouargla', 'Oran', 'El Bayadh',
        'Illizi', 'Bordj Bou Arreridj', 'Boumerdès', 'El Tarf', 'Tindouf', 'Tissemsilt', 'El Oued',
        'Khenchela', 'Souk Ahras', 'Tipaza', 'Mila', 'Aïn Defla', 'Naâma', 'Aïn Témouchent',
        'Ghardaïa', 'Relizane', 'Timimoun', 'Bordj Badji Mokhtar', 'Ouled Djellal', 'Beni Abbès',
        'In Salah', 'In Guezzam', 'Touggourt', 'Djanet', 'El M\'Ghair', 'El Meniaa'
    ];
}

// Fonction pour obtenir les positions de football
function getPositions() {
    return [
        'Gardien de but', 'Défenseur central', 'Défenseur latéral', 'Milieu défensif',
        'Milieu central', 'Milieu offensif', 'Ailier', 'Attaquant', 'Libéro'
    ];
}

// Fonction pour obtenir les niveaux de football
function getNiveaux() {
    return [
        'Débutant', 'Amateur', 'Semi-professionnel', 'Professionnel', 'Élite'
    ];
}

// Fonction pour paginer les résultats
function paginate($pdo, $sql, $params = [], $page = 1, $per_page = 10) {
    $offset = ($page - 1) * $per_page;
    
    // Compter le total - méthode simplifiée
    $count_sql = str_replace('SELECT u.*, j.*', 'SELECT COUNT(*) as total', $sql);
    $count_sql = str_replace('ORDER BY avg_rating DESC, u.created_at DESC', '', $count_sql);
    
    try {
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total = $count_stmt->fetch()['total'];
    } catch (PDOException $e) {
        // Si la requête de comptage échoue, utiliser une méthode alternative
        $simple_count = "SELECT COUNT(*) as total FROM users u LEFT JOIN joueurs j ON u.id = j.user_id WHERE u.user_type = 'joueur' AND u.status = 'active'";
        $count_stmt = $pdo->prepare($simple_count);
        $count_stmt->execute();
        $total = $count_stmt->fetch()['total'];
    }
    
    // Récupérer les données
    $data_sql = $sql . " LIMIT $per_page OFFSET $offset";
    $data_stmt = $pdo->prepare($data_sql);
    $data_stmt->execute($params);
    $data = $data_stmt->fetchAll();
    
    return [
        'data' => $data,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total / $per_page)
    ];
}

// Fonction pour générer les liens de pagination
function generatePaginationLinks($current_page, $total_pages, $base_url) {
    $links = [];
    
    // Page précédente
    if ($current_page > 1) {
        $links[] = '<a href="' . $base_url . '?page=' . ($current_page - 1) . '" class="btn btn-outline-primary">Précédent</a>';
    }
    
    // Pages numérotées
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = $i == $current_page ? 'btn-primary' : 'btn-outline-primary';
        $links[] = '<a href="' . $base_url . '?page=' . $i . '" class="btn ' . $active . '">' . $i . '</a>';
    }
    
    // Page suivante
    if ($current_page < $total_pages) {
        $links[] = '<a href="' . $base_url . '?page=' . ($current_page + 1) . '" class="btn btn-outline-primary">Suivant</a>';
    }
    
    return implode(' ', $links);
}
?>

